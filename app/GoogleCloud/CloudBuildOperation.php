<?php

namespace App\GoogleCloud;

class CloudBuildOperation
{
    protected $operation;

    public function __construct($operation) {
        $this->operation = $operation;
    }

    /**
     * Whether the build is finished.
     *
     * @return boolean
     */
    public function isDone()
    {
        return $this->operation['done'] ?? false;
    }

    /**
     * The build error message
     *
     * @return string|null
     */
    public function errorMessage()
    {
        return $this->operation['error']['message'] ?? null;
    }

    /**
     * Whether the build has errors.
     *
     * @return boolean
     */
    public function hasError()
    {
        return ! empty($this->errorMessage());
    }

    /**
     * Get the string of the built image
     *
     * @return string
     */
    public function builtImage()
    {
        return $this->operation['metadata']['build']['id'] ?? null;
    }
}
