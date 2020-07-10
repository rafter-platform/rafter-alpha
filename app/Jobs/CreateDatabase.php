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

class CreateDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Trackable;

    public $databaseInstanceId;

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

        $this->model->environment->createDatabase($databaseInstance);

        return true;
    }
}
