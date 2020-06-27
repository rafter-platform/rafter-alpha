<?php

namespace App\Http\Requests;

use App\Environment;
use Illuminate\Foundation\Http\FormRequest;

class GitHubHookDeploymentRequest extends FormRequest
{
    public function rules()
    {
        return [
            //
        ];
    }

    public function id()
    {
        return $this->deployment['id'];
    }

    public function installationid()
    {
        return $this->installation['id'];
    }

    public function payload()
    {
        return $this->deployment['payload'] ?? [];
    }

    public function environmentId()
    {
        return $this->payload()['environment_id'] ?? null;
    }

    public function manual()
    {
        return $this->payload()['manual'] ?? false;
    }

    public function initiatorId()
    {
        return $this->payload()['initiator_id'];
    }

    public function hash()
    {
        return $this->deployment['sha'];
    }

    public function getEnvironment()
    {
        return Environment::find($this->environmentId());
    }
}
