<?php

namespace App\Jobs;

use App\DomainMapping;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReverifyDomainMapping implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $mappingId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mappingId)
    {
        $this->mappingId = $mappingId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $mapping = DomainMapping::find($this->mappingId);

        if (!$mapping) return;

        $mapping->resubmitAfterVerification();
    }
}
