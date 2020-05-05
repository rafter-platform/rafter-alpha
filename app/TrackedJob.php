<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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
     * @param string $output
     * @return void
     */
    public function markAsFinished($output = '')
    {
        $this->update([
            'status' => static::STATUS_FINISHED,
            'finished_at' => Carbon::now(),
        ]);

        $this->setOutput($output);
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
    public function markAsFailed($exception)
    {
        $this->update([
            'status' => static::STATUS_FAILED,
            'finished_at' => Carbon::now(),
        ]);

        $this->setOutput($exception);

        if (method_exists($this->trackable, 'markAsFailed')) {
            $this->trackable->markAsFailed();
        }
    }

    /**
     * Whether the job has failed.
     *
     * @return boolean
     */
    public function hasFailed()
    {
        return $this->status == static::STATUS_FAILED;
    }

    /**
     * Set the output of the tracked job.
     *
     * If the output is just `true`, we just blank out the log.
     *
     * @param string $output
     * @return void
     */
    public function setOutput($output)
    {
        if ($output === true) {
            $output = '';
        }

        $this->update(['output' => $output]);
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

    /**
     * Get a pretty formatted label based on the name of the job.
     *
     * @return string
     */
    public function label(): string
    {
        return str_replace('-', ' ', Str::title(Str::kebab($this->name)));
    }
}
