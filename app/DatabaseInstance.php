<?php

namespace App;

use App\Casts\Options;
use App\GoogleCloud\DatabaseInstanceConfig;
use App\Jobs\MonitorDatabaseInstanceCreation;
use App\Services\GoogleApi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DatabaseInstance extends Model
{
    use HasOptions;

    const TYPES = [
        'mysql',
        // 'postgres',
    ];

    const VERSIONS = [
        'MYSQL_5_7' => 'MySQL 5.7',
        'MYSQL_5_6' => 'MySQL 5.6',
        // 'POSTGRES_9_6' => 'Postgres 9.6',
        // 'POSTGRES_11' => 'Postgres 11 (Beta)',
    ];

    const TIERS = [
        'mysql' => [
            'db-f1-micro' => 'db-f1-micro (1vCPU, 614.4 MB) ~$8/mo',
            'db-g1-small' => 'db-g1-small (1vCPU, 1.7 GB) ~$26/mo',
            'db-n1-standard-1' => 'db-n1-standard-1 (1vCPU, 3.75 GB) ~$50/mo',
            'db-n1-standard-2' => 'db-n1-standard-2 (2vCPU, 7.5 GB) ~$100/mo',
            'db-n1-standard-4' => 'db-n1-standard-4 (4vCPU, 15 GB) ~$200/mo',
            'db-n1-standard-8' => 'db-n1-standard-8 (8vCPU, 30 GB) ~$400/mo',
            'db-n1-standard-16' => 'db-n1-standard-16 (16vCPU, 60 GB) ~$800/mo',
            'db-n1-standard-32' => 'db-n1-standard-32 (32vCPU, 120 GB) ~$1,600/mo',
            'db-n1-standard-64' => 'db-n1-standard-64 (64vCPU, 240 GB) ~$3,200/mo',
            'db-n1-highmem-2' => 'db-n1-highmem-2 (2vCPU, 13 GB) ~$130/mo',
            'db-n1-highmem-4' => 'db-n1-highmem-4 (4vCPU, 26 GB) ~$258/mo',
            'db-n1-highmem-8' => 'db-n1-highmem-8 (8vCPU, 52 GB) ~$515/mo',
            'db-n1-highmem-16' => 'db-n1-highmem-16 (16vCPU, 104 GB) ~$1,030/mo',
            'db-n1-highmem-32' => 'db-n1-highmem-32 (32vCPU, 208 GB) ~$2,056/mo',
            'db-n1-highmem-64' => 'db-n1-highmem-64 (64vCPU, 416 GB) ~$4,112/mo',
        ],
        'postgres' => [
            // TKTK
        ]
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_CREATING = 'creating';
    const STATUS_ACTIVE = 'active';
    const STATUS_FAILED = 'failed';

    protected $guarded = [];

    protected $casts = [
        'synced' => 'boolean',
        'options' => Options::class,
    ];

    public function googleProject()
    {
        return $this->belongsTo('App\GoogleProject');
    }

    public function databases()
    {
        return $this->hasMany('App\Database');
    }

    /**
     * Provision the Database Instance on Google Cloud, and then monitor it.
     */
    public function provision()
    {
        $this->assignRootPassword();

        $databaseInstanceConfig = new DatabaseInstanceConfig($this);

        $operation = $this->client()->createDatabaseInstance($databaseInstanceConfig);
        $this->update(['operation_name' => $operation['name']]);

        $this->setCreating();

        MonitorDatabaseInstanceCreation::dispatch($this);
    }

    /**
     * Assigns a random root password to the instance.
     */
    public function assignRootPassword()
    {
        $this->setOption('root_password', Str::random());
    }

    /**
     * Set the status to creating.
     */
    public function setCreating()
    {
        $this->update(['status' => static::STATUS_CREATING]);
    }

    /**
     * Set the status to active.
     */
    public function setActive()
    {
        $this->update(['status' => static::STATUS_ACTIVE]);
    }

    /**
     * Set the status to failed.
     */
    public function setFailed()
    {
        $this->update(['status' => static::STATUS_FAILED]);
    }

    public function projectId()
    {
        return $this->googleProject->project_id;
    }

    /**
     * Get the connection string to reference in Cloud Run.
     */
    public function connectionString()
    {
        return sprintf(
            "%s:%s:%s",
            $this->googleProject->project_id,
            $this->region,
            $this->name
        );
    }

    public function client(): GoogleApi
    {
        return $this->googleProject->client();
    }
}
