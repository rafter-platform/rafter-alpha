<?php

namespace App\Jobs;

use App\GoogleProject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class DetermineProjectNumber implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $googleProject;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(GoogleProject $googleProject)
    {
        $this->googleProject = $googleProject;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->googleProject->determineProjectNumber();
        } catch (Throwable $e) {
            $this->fail($e);
        }
    }
}
