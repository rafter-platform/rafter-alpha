<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WaitForDatabaseToProvision implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Trackable;

    // 30 seconds
    public $tries = 6;

    public function handle()
    {
        if (
            $this->model->environment->database->isActive()
            && $this->model->environment->databaseUser->isActive()
        ) {
            return true;
        }

        $this->release(5);
    }
}
