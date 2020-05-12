<?php

namespace App\Jobs;

use App\DomainMapping;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class CheckDomainMappingStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $domainMapping;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(DomainMapping $domainMapping)
    {
        $this->domainMapping = $domainMapping;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->domainMapping->checkStatus();
    }
}
