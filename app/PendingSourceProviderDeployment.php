<?php

namespace App;

class PendingSourceProviderDeployment
{
    protected $environment;
    protected $hash;
    protected $userId;
    protected $payload = [];

    public static function make()
    {
        return new static;
    }

    public function forEnvironment(Environment $environment)
    {
        $this->environment = $environment;

        return $this;
    }

    public function forHash(string $hash)
    {
        $this->hash = $hash;

        return $this;
    }

    public function withPayload($payload)
    {
        $this->payload = array_merge($this->payload, $payload);

        return $this;
    }

    public function byUserId(?int $userId)
    {
        $this->userId = $userId;

        return $this;
    }

    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getRepository(): string
    {
        return $this->environment->repository();
    }
}
