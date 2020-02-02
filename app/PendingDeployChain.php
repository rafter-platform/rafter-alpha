<?php

namespace App;

use Illuminate\Foundation\Bus\PendingChain;
use Illuminate\Foundation\Bus\PendingDispatch;

class PendingDeployChain extends PendingChain
{
    /**
     * The first job associated with this chain.
     *
     * @var \App\Jobs\DeploymentStepJob
     */
    public $job;

    public function __construct($job, $chain) {
        $this->job = $job;
        $this->chain = $chain;
    }

    /**
     * Dispatch the jobs, starting with the first, and including the chain.
     * Since the withDeploymentChain method is an instance method, a deployment and step are
     * already associated with this job.
     *
     * @return PendingDispatch
     */
    public function dispatch()
    {
        return (new PendingDispatch(
            $this->job
        ))->chain($this->chain);
    }
}
