<?php

namespace App\GoogleCloud;

class EnableApisOperation
{
    protected $operation;

    public function __construct($operation) {
        $this->operation = $operation;
    }

    /**
     * Whether the operation is still in progress.
     *
     * @return boolean
     */
    public function isInProgress()
    {
        return ! ($this->operation['done'] ?? false);
    }
}
