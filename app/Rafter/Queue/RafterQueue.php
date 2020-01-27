<?php

namespace App\Rafter\Queue;

use App\Rafter\Rafter;
use Google\Cloud\Tasks\V2beta3\CloudTasksClient;
use Google\Cloud\Tasks\V2beta3\HttpMethod;
use Google\Cloud\Tasks\V2beta3\Task;
use Google\Cloud\Tasks\V2beta3\HttpRequest;
use Google\Protobuf\Timestamp;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Queue\Queue;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class RafterQueue extends Queue implements QueueContract
{
    /**
     * The Google Cloud Tasks instance.
     *
     * @var \Google\Cloud\Tasks\V2\CloudTasksClient
     */
    protected $tasks;

    /**
     * The name of the default queue.
     *
     * @var string
     */
    protected $default;

    /**
     * The GCP Project ID
     *
     * @var string
     */
    protected $projectId;

    /**
     * The GCP region/location.
     *
     * @var string
     */
    protected $region;

    /**
	 * @var array
	 */
	protected $options = [
		'scheduleTime',
	];

    public function __construct(CloudTasksClient $tasks, $default, $projectId, $region)
    {
        $this->tasks = $tasks;
        $this->projectId = $projectId;
        $this->region = $region;
        $this->default = $default;
    }

    /**
     * Get the size of the queue.
     *
     * @param  string|null  $queue
     * @return int
     */
    public function size($queue = null)
    {
        $iterator = $this->tasks->listTasks($this->getQueue($queue))->iterateAllElements();

        $count = 0;

		while ($iterator->valid()) {
			$count++;
			$iterator->next();
		}

		return $count;
    }

    /**
     * Get the queue or return the default.
     *
     * @param  string|null  $queue
     * @return string
     */
    public function getQueue($queue)
    {
        $queue = $queue ?: $this->default;

        return $this->tasks::queueName($this->projectId, $this->region, $queue);
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  string  $job
     * @param  mixed  $data
     * @param  string|null  $queue
     * @return mixed
     */
    public function push($job, $data = '', $queue = null)
    {
        return $this->pushRaw($this->createPayload($job, $queue ?: $this->default, $data), $queue);
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param  string  $payload
     * @param  string|null  $queue
     * @param  array  $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $queueName = $this->getQueue($queue);

        $task = new Task();

        // Set options on the task
		foreach ($this->options as $option) {
			if (array_key_exists($option, $options)) {
				$this->{'set' . ucfirst($option)}($task, $options[$option]);
			}
		}

        $url = Rafter::queueWorkerUrl();
        $httpRequest = new HttpRequest();
        $httpRequest->setUrl($url);
        $httpRequest->setHttpMethod(HttpMethod::POST);
        $httpRequest->setBody($payload);

        $task->setHttpRequest($httpRequest);

        $response = $this->tasks->createTask($queueName, $task);

        // Return the task name
        return Arr::last(explode('/', $response->getName()));
    }

    /**
	 * Push a new job onto the queue after a delay.
	 *
	 * @param \DateTimeInterface|\DateInterval|int $delay
	 * @param string|object                        $job
	 * @param mixed                                $data
	 * @param string|null                          $queue
	 *
	 * @return mixed
	 * @throws \Google\ApiCore\ApiException
	 */
	public function later($delay, $job, $data = '', $queue = null)
	{
		return $this->pushRaw(
			$this->createPayload($job, $queue, $data),
			$queue,
			['scheduleTime' => $delay]
		);
    }

    /**
	 * Pop the next job off of the queue.
     * Note: This is unused, as Cloud Tasks automatically pops and sends
     * the payload for the next job.
	 *
	 * @param string $queue
	 *
	 * @return \Illuminate\Contracts\Queue\Job|null
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 */
	public function pop($queue = null)
	{
        return null;
    }

    /**
     * Get the Tasks client instance, for use by the rafter:work command.
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * Create a payload string from the given job and data.
     *
     * @param  string  $job
     * @param  string  $queue
     * @param  mixed  $data
     * @return array
     */
    protected function createPayloadArray($job, $queue, $data = '')
    {
        return array_merge(parent::createPayloadArray($job, $queue, $data), [
            'attempts' => 0,
        ]);
    }

    /**
     * Google Cloud Tasks requires that payload be send in base64 encoded strings.
     */
    protected function createPayload($job, $queue, $data = '')
	{
		return base64_encode(parent::createPayload($job, $queue, $data));
    }

    /**
	 * @param \Google\Cloud\Tasks\V2\Task $task
	 * @param                             $time
	 */
	protected function setScheduleTime(&$task, $time)
	{
		$time = Carbon::createFromTimestamp($this->availableAt($time));
		$timestamp = new Timestamp();
		$timestamp->fromDateTime($time);
		$task->setScheduleTime($timestamp);
	}
}
