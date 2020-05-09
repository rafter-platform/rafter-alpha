<?php

namespace Tests\Support;

class FakeGoogleSecretManagerClient
{
    public function __construct(array $options = [])
    {
        # code...
    }

    public function projectName()
    {
        return '';
    }

    public function secretName()
    {
        return '';
    }

    public function getSecret()
    {
        return true;
    }

    public function addSecretVersion()
    {
        # code...
    }
}
