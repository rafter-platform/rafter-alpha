<?php

namespace Tests\Support;

class FakeGoogleApiClient
{
    public function setAuthConfig($config)
    {

    }

    public function addScope($scope)
    {

    }

    public function fetchAccessTokenWithAssertion()
    {
        return [
            'access_token' => 'notatoken',
        ];
    }
}
