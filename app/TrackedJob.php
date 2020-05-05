<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class TrackedJob extends Model
{
    const STATUS_STARTED = 'started';
    const STATUS_FINISHED = 'finished';
    const STATUS_FAILED = 'failed';

    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function trackable()
    {
        return $this->morphTo('trackable');
    }

    /**
     * Mark the step as started
     *
     * @return void
     */
    public function markAsStarted()
    {
        if (!$this->hasStarted()) {
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
        return !empty($this->started_at);
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
     * Whether this job has finished or failed
     *
     * @return boolean
     */
    public function hasFinished()
    {
        return !empty($this->finished_at);
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

        if (method_exists($this->trackable, 'markAsFailed')) {
            $this->trackable->markAsFailed();
        }
    }

    /**
     * Get the duration of the job, in human diff.
     *
     * @return string
     */
    public function duration()
    {
        if (!$this->hasStarted()) return '';

        return ($this->finished_at ?? Carbon::now())
            ->diffAsCarbonInterval($this->started_at)
            ->forHumans(['short' => true]);
    }
}
