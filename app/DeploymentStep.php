<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class DeploymentStep extends Model
{
    const STATUS_STARTED = 'started';
    const STATUS_FINISHED = 'finished';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'name',
        'started_at',
        'finished_at',
        'status',
    ];

    public function deployment()
    {
        return $this->belongsTo('App\DeploymentStep');
    }

    /**
     * Mark the step as started
     *
     * @return void
     */
    public function markAsStarted()
    {
        if (! $this->hasStarted()) {
            $this->update([
                'status' => static::STATUS_STARTED,
                'started_at' => Carbon::now(),
            ]);
        }
    }

    /**
     * Whether the job has already started.
     *
     * @return boolean
     */
    public function hasStarted()
    {
        return $this->status === static::STATUS_STARTED;
    }

    /**
     * Mark the step as finished
     *
     * @return void
     */
    public function markAsFinished()
    {
        $this->update([
            'status' => static::STATUS_FINISHED,
            'finished_at' => Carbon::now(),
        ]);
    }

    /**
     * Mark the step as failed
     *
     * @return void
     */
    public function markAsFailed()
    {
        $this->update([
            'status' => static::STATUS_FAILED,
            'finished_at' => Carbon::now(),
        ]);
    }
}
