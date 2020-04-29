<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Command extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_FINISHED = 'finished';
    const STATUS_FAILED = 'failed';

    protected $guarded = [];

    public function dispatch()
    {
        # code...
    }

    public function markRunning()
    {
        $this->update(['status' => static::STATUS_RUNNING]);
    }

    public function markFinished(string $output)
    {
        $this->update([
            'status' => static::STATUS_FINISHED,
            'output' => $output,
        ]);
    }

    public function markFailed(string $output)
    {
        $this->update([
            'status' => static::STATUS_FAILED,
            'output' => $output,
        ]);
    }

    public function isRunning()
    {
        return $this->status == static::STATUS_RUNNING;
    }

    public function isFinished()
    {
        return $this->status == static::STATUS_FINISHED;
    }

    public function isFailed()
    {
        return $this->status == static::STATUS_FAILED;
    }

    /**
     * Get the elapsed time for a given command.
     *
     * @return string
     */
    public function elapsedTime(): string
    {
        return $this->updated_at->longAbsoluteDiffForHumans($this->created_at);
    }
}
