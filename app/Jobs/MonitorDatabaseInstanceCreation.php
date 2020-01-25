<?php

namespace App\Jobs;

use App\DatabaseInstance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class MonitorDatabaseInstanceCreation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $databaseInstance;

    // 10 minutes
    public $tries = 40;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(DatabaseInstance $databaseInstance)
    {
        $this->databaseInstance = $databaseInstance;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $operation = $this->databaseInstance->client()->getDatabaseOperation(
            $this->databaseInstance->projectId(),
            $this->databaseInstance->operation_name
        );

        if ($operation->inProgress()) {
            $this->release(15);
            return;
        }

        $this->databaseInstance->setActive();
    }

    public function failed(Throwable $exception)
    {
        Log::error($exception->getMessage());
        $this->databaseInstance->setFailed();
    }
}
