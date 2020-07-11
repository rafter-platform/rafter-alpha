<?php

namespace App\GoogleCloud;

use App\Contracts\GoogleConfig;
use App\DatabaseUser;

class DatabaseUserConfig implements GoogleConfig
{
    protected $databaseUser;

    public function __construct(DatabaseUser $databaseUser)
    {
        $this->databaseUser = $databaseUser;
    }

    public function instanceName(): string
    {
        return $this->databaseUser->databaseInstance->name;
    }

    public function projectId(): string
    {
        return $this->databaseUser->databaseInstance->projectId();
    }

    public function config(): array
    {
        return [
            'kind' => 'sql#user',
            'password' => $this->databaseUser->password,
            'name' => $this->databaseUser->name,
            'host' => '%',
            'instance' => $this->instanceName(),
            'project' => $this->projectId(),
        ];
    }
}
