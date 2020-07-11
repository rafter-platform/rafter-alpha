<?php

namespace App\GoogleCloud;

use App\Contracts\GoogleConfig;
use App\Database;

class DatabaseConfig implements GoogleConfig
{
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function projectId()
    {
        return $this->database->databaseInstance->projectId();
    }

    public function instanceName()
    {
        return $this->database->databaseInstance->name;
    }

    public function charset()
    {
        return 'utf8mb4';
    }

    public function name()
    {
        return $this->database->name;
    }

    public function config(): array
    {
        return [
            'kind' => 'sql#database',
            'charset' => $this->charset(),
            'name' => $this->name(),
        ];
    }
}
