<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WaitForGoogleProjectToBeProvisioned implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Trackable;

    // 1 minute
    public $tries = 6;

    public function handle()
    {
        if (!$this->model->environment->project->googleProject->isReady()) {
            $this->release(10);
            return false;
        }

        return true;
    }
}
