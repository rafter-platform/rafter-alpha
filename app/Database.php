<?php

namespace App;

use App\GoogleCloud\DatabaseConfig;
use App\Jobs\MonitorDatabaseCreation;
use App\Services\GoogleApi;
use Illuminate\Database\Eloquent\Model;
use App\DatabaseInstance;

class Database extends Model
{
    const STATUS_CREATING = 'creating';
    const STATUS_ACTIVE = 'active';
    const STATUS_FAILED = 'failed';

    protected $guarded = [];

    public function databaseInstance()
    {
        return $this->belongsTo(DatabaseInstance::class);
    }

    /**
     * Get the user for the database.
     *
     * @return string
     */
    public function databaseUser()
    {
        return 'root';
    }

    /**
     * Get the password for the database
     *
     * @return string
     */
    public function databasePassword()
    {
        return $this->databaseInstance->root_password;
    }

    /**
     * Get the connection string to the database instance.
     *
     * @return string
     */
    public function connectionString()
    {
        return $this->databaseInstance->connectionString();
    }

    public function setCreating()
    {
        $this->update(['status' => static::STATUS_CREATING]);
    }

    public function setActive()
    {
        $this->update(['status' => static::STATUS_ACTIVE]);
    }

    public function setFailed()
    {
        $this->update(['status' => static::STATUS_FAILED]);
    }

    /**
     * Provision this database on the DatabaseInstance on Google Cloud.
     */
    public function provision()
    {
        $databaseConfig = new DatabaseConfig($this);

        $operation = $this->client()->createDatabase($databaseConfig);
        $this->update(['operation_name' => $operation['name']]);
        $this->setCreating();

        MonitorDatabaseCreation::dispatch($this);
    }

    public function client(): GoogleApi
    {
        return $this->databaseInstance->client();
    }
}
