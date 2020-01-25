<?php

namespace App;

use App\GoogleCloud\DatabaseConfig;
use App\Services\GoogleApi;
use Illuminate\Database\Eloquent\Model;

class Database extends Model
{
    const STATUS_CREATING = 'creating';
    const STATUS_ACTIVE = 'active';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'name',
        'status',
        'operation_name',
    ];

    public function databaseInstance()
    {
        return $this->belongsTo('App\DatabaseInstance');
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

        // TODO: Monitor the creation
    }

    public function client(): GoogleApi
    {
        return $this->databaseInstance->client();
    }
}
