<?php

namespace App\Jobs;

use App\DatabaseUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class MonitorDatabaseUserCreation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $databaseUser;
    public $operationName;

    // 1 minute
    public $tries = 4;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(DatabaseUser $databaseUser, $operationName)
    {
        $this->databaseUser = $databaseUser;
        $this->operationName = $operationName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $operation = $this->databaseUser->client()->getDatabaseOperation(
            $this->databaseUser->databaseInstance->projectId(),
            $this->operationName
        );

        if ($operation->inProgress()) {
            $this->release(15);
            return;
        }

        $this->databaseUser->setActive();
    }

    public function failed(Throwable $exception)
    {
        logger()->error($exception->getMessage());
        $this->databaseUser->setFailed();
    }
}
