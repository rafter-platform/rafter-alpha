<?php

namespace App\Rafter;

use Illuminate\Queue\Worker;
use Illuminate\Queue\WorkerOptions;

class RafterWorker extends Worker
{
    /**
     * Process the given job.
     *
     * @param  \Illuminate\Contracts\Queue\Job  $job
     * @param  string  $connectionName
     * @param  \Illuminate\Queue\WorkerOptions  $options
     * @return void
     */
    public function runRafterJob($job, $connectionName, WorkerOptions $options)
    {
        return $this->runJob($job, $connectionName, $options);
    }
}
