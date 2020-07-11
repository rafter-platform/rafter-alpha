<?php

namespace App;

use App\GoogleCloud\DatabaseUserConfig;
use App\Jobs\MonitorDatabaseUserCreation;
use App\Services\GoogleApi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DatabaseUser extends Model
{
    const STATUS_CREATING = 'creating';
    const STATUS_ACTIVE = 'active';
    const STATUS_FAILED = 'failed';

    const MAX_NAME_LENGTH = 32;

    protected $guarded = [];

    public function databaseInstance()
    {
        return $this->belongsTo(DatabaseInstance::class);
    }

    public function provision()
    {
        $config = new DatabaseUserConfig($this);
        $operation = $this->client()->createDatabaseUser($config);

        MonitorDatabaseUserCreation::dispatch($this, $operation['name']);
    }

    public static function generateUniqueName($name)
    {
        $alphaLength = 4;

        return substr($name, 0, static::MAX_NAME_LENGTH - $alphaLength - 1) . '-' . Str::random($alphaLength);
    }

    public function setCreating()
    {
        $this->update(['status' => static::STATUS_CREATING]);
    }

    public function setActive()
    {
        $this->update(['status' => static::STATUS_ACTIVE]);
    }

    public function isActive()
    {
        return $this->status == static::STATUS_ACTIVE;
    }

    public function setFailed()
    {
        $this->update(['status' => static::STATUS_FAILED]);
    }

    public function client(): GoogleApi
    {
        return $this->databaseInstance->client();
    }
}
