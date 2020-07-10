<?php

namespace App\Jobs;

use App\DatabaseInstance;
use App\TrackedJob;
use Error;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WaitForDatabaseInstanceToProvision implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Trackable;

    public $databaseInstanceId;

    // 5 minutes
    public $tries = 10;

    public function __construct($model, $databaseInstanceId)
    {
        $this->model = $model;

        $this->trackedJob = TrackedJob::create([
            'trackable_id' => $this->model->id,
            'trackable_type' => get_class($this->model),
            'name' => class_basename(static::class),
        ]);

        $this->databaseInstanceId = $databaseInstanceId;
    }

    public function handle()
    {
        $databaseInstance = DatabaseInstance::find($this->databaseInstanceId);

        if (!$databaseInstance) {
            throw new Error('Database Instance could not be found');
        }

        if ($databaseInstance->isActive()) {
            return true;
        }

        $this->release(30);
    }
}
