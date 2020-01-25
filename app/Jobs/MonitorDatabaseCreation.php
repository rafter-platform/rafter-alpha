<?php

namespace App\Jobs;

use App\Database;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class MonitorDatabaseCreation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $database;

    // 10 minutes
    public $tries = 40;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $operation = $this->database->client()->getDatabaseOperation(
            $this->database->databaseInstance->projectId(),
            $this->database->operation_name
        );

        if ($operation->inProgress()) {
            $this->release(15);
            return;
        }

        $this->database->setActive();
    }

    public function failed(Throwable $exception)
    {
        Log::error($exception->getMessage());
        $this->database->setFailed();
    }
}
