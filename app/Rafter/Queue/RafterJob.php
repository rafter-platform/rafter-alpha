<?php

namespace App\Rafter\Queue;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Queue\Job as JobContract;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\HeaderBag;

class RafterJob extends Job implements JobContract
{
    /**
     * The Google Tasks client.
     *
     * @var \Google\Cloud\Tasks\V2beta3\CloudTasksClient
     */
    protected $tasks;

    /**
     * The name of the queue
     *
     * @var string
     */
    protected $queue;

    /**
     * The raw, string version of the JSON.
     *
     * @var string
     */
    protected $rawJob;

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

    protected $rafterQueue;

    public function __construct(Application $container, $rafterQueue, string $job, HeaderBag $headers, $queue)
    {
        $this->rafterQueue = $rafterQueue;
        $this->queue = $queue;
        $this->rawJob = $job;
        $this->job = $this->payload();
        $this->headers = $headers;
        $this->container = $container;
        $this->connectionName = 'rafter';
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

		$this->rafterQueue->pushRaw(json_encode($this->job), $this->queue, $options);
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
        return Arr::last(explode('/', $this->headers['X-CloudTasks-TaskName']));
    }

    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    public function getRawBody()
    {
        return $this->rawJob;
    }
}
