<?php

namespace App\GoogleCloud;

use Exception;

class CloudRunService
{
    protected $service;

    public function __construct($service) {
        $this->service = $service;
    }

    /**
     * Get the status array
     *
     * @return array
     */
    public function status()
    {
        return $this->service['status'];
    }

    /**
     * Whether the revision is ready
     *
     * @return boolean
     */
    public function isReady()
    {
        return $this->getStatus('Ready') === 'True';
    }

    /**
     * Get a status condition
     *
     * @param string $type
     * @return array
     */
    public function getCondition($type)
    {
        $condition = collect($this->status()['conditions'])
            ->firstWhere('type', $type);

        if (!$condition) {
            throw new InvalidConditionException("{$type} is not a valid condition");
        }

        return $condition;
    }

    /**
     * Get the value of a given status by key
     *
     * @param string $type
     * @return string
     */
    public function getStatus($type)
    {
        return $this->getCondition($type)['status'];
    }

    /**
     * Get a message for a given condition type
     *
     * @param string $type
     * @return string|null
     */
    public function getMessage($type)
    {
        return $this->getCondition($type)['message'] ?? null;
    }

    /**
     * Get the error, if it exists
     *
     * @return string|null
     */
    public function getError()
    {
        return $this->getMessage('Ready');
    }

    /**
     * Whether the service has errors.
     *
     * @return boolean
     */
    public function hasErrors()
    {
        return $this->getStatus('Ready') === 'False';
    }

    /**
     * Get the URL of the service.
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->status()['url'] ?? null;
    }

    /**
     * Get the metadata
     *
     * @return array
     */
    public function metadata()
    {
        return $this->services['metadata'];
    }

    /**
     * Dump out the service to JSON for testing purposes.
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->service);
    }
}


class InvalidConditionException extends Exception {}
