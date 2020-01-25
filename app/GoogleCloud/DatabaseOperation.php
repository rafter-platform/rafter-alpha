<?php

namespace App\GoogleCloud;

class DatabaseOperation
{
    const STATUS_DONE = 'DONE';

    protected $operation;

    public function __construct($operation) {
        $this->operation = $operation;
    }

    /**
     * Whether the operation is still in progress.
     */
    public function inProgress()
    {
        return $this->operation['status'] !== static::STATUS_DONE;
    }
}
