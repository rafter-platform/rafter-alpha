<?php

namespace App\GoogleCloud;

use App\DatabaseInstance;

class DatabaseInstanceConfig
{
    public $databaseInstance;

    public function __construct(DatabaseInstance $databaseInstance)
    {
        $this->databaseInstance = $databaseInstance;
    }

    /**
     * Get the project ID.
     */
    public function projectId()
    {
        return $this->databaseInstance->projectId();
    }

    public function version()
    {
        return $this->databaseInstance->getOption('version');
    }

    public function tier()
    {
        return $this->databaseInstance->getOption('tier');
    }

    public function size()
    {
        return $this->databaseInstance->getOption('size');
    }

    public function settings()
    {
        return [
            'tier' => $this->tier(),
            'kind' => 'sql#settings',
            'dataDiskSizeGb' => $this->size(),
            'backupConfiguration' => $this->backupConfiguration(),
        ];
    }

    public function region()
    {
        return $this->databaseInstance->getOption('region');
    }

    public function rootPassword()
    {
        return $this->databaseInstance->getOption('rootPassword');
    }

    public function name()
    {
        return $this->databaseInstance->name;
    }

    public function backupConfiguration()
    {
        return [
            'enabled' => true,
            'kind' => 'sql#backupConfiguration',
            'binaryLogEnabled' => true,
        ];
    }

    public function config()
    {
        return [
            'kind' => 'sql#instance',
            'databaseVersion' => $this->version(),
            'settings' => $this->settings(),
            'name' => $this->name(),
            'region' => $this->region(),
            'rootPassword' => $this->rootPassword(),
        ];
    }
}
