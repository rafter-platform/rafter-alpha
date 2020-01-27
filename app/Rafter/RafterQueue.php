<?php

namespace App\Rafter;

use Google\Cloud\Tasks\V2\CloudTasksClient;
use Google\Cloud\Tasks\V2\HttpRequest;
use Google\Cloud\Tasks\V2\Task;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Queue\Queue;

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
        ddd($payload);
        $queueName = $this->getQueue($queue);
        $taskId = uniqid();

        $task = new Task();
        $task->setName("{$queueName}/tasks/{$taskId}");

        $url = Rafter::queueWorkerUrl();
        $httpRequest = new HttpRequest();
        $httpRequest->setUrl($url);
        $httpRequest->setBody($payload);

        $task->setHttpRequest($httpRequest);

        $this->tasks->createTask($queueName, $task);

        return $taskId;
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
	 *
	 * @param string $queue
	 *
	 * @return \Illuminate\Contracts\Queue\Job|null
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 */
	public function pop($queue = null)
	{
        // lol idk what does this do for us???

		// $payload = $this->container->make($this->currentJobContainerKey);

		// // Return the previously bound in job content, provided by GCP Tasks
		// return new RafterJob(
		// 	$this->container,
		// 	$this,
		// 	$payload,
		// 	$this->connectionName,
		// 	$this->queue
		// );
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
}
