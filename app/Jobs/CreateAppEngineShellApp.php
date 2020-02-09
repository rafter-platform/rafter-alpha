<?php

namespace App\Jobs;

use App\GoogleProject;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateAppEngineShellApp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $googleProject;

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
            if (! $this->googleProject->client()->hasAppEngineApp()) {
                $this->googleProject->client()->createAppEngineApp();
            }
        } catch (Exception $e) {
            $this->fail($e);
        }
    }
}
