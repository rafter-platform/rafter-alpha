<?php

namespace App\Rafter;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Queue\Job as JobContract;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Arr;

class RafterJob extends Job implements JobContract
{
    /**
     * The Google Tasks client.
     *
     * @var \Google\Cloud\Tasks\V2beta3\CloudTasksClient
     */
    protected $tasks;

    /**
     * The RafterQueue
     *
     * @var \Rafter\RafterQueue
     */
    protected $queue;

    /**
     * The job payload.
     *
     * (
     * 'displayName' => 'App\\Jobs\\TestJob',
     * 'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
     * 'maxTries' => NULL,
     * 'delay' => NULL,
     * 'timeout' => NULL,
     * 'timeoutAt' => NULL,
     * 'data' =>
     * array (
         * 'commandName' => 'App\\Jobs\\TestJob',
         * 'command' => 'O:16:"App\\Jobs\\TestJob":9:{s:7:"monkeys";i:12;s:6:"' . "\0" . '*' . "\0" . 'job";N;s:10:"connection";N;s:5:"queue";N;s:15:"chainConnection";N;s:10:"chainQueue";N;s:5:"delay";N;s:10:"middleware";a:0:{}s:7:"chained";a:0:{}}',
     * ),
     * 'attempts' => 0,
     * )
     */
    protected $job;

    public function __construct(Application $container, RafterQueue $queue, array $job, array $headers, string $queueName)
    {
        $this->queue = $queue;
        $this->tasks = $queue->getTasks();
        $this->job = $job;
        $this->headers = $headers;
        $this->queueName = $queueName;
        $this->container = $container;
    }

    /**
     * Release the job back into the queue.
     *
     * @param  int  $delay
     * @return void
     */
    public function release($delay = 0)
    {
        $options = [];

		if ($delay > 0) {
			$options['scheduleTime'] = $delay;
        }

        $this->job['attempts'] += 1;

		$this->queue->pushRaw(base64_encode(json_encode($this->job)), $this->queueName, $options);
		$this->delete();

		parent::release($delay);
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        return (int) $this->job['attempts'];
    }

    /**
     * Get the job identifier.
     *
     * @return string
     */
    public function getJobId()
    {
        return Arr::last(explode('/', $this->headers['x-cloudtasks-taskname'][0]));
    }

    /**
     * By this step, the payload is already an array and doesn't need to be decoded.
     */
    public function payload()
    {
        return $this->getRawBody();
    }

    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    public function getRawBody()
    {
        return $this->job;
    }
}
