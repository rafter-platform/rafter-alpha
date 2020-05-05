<?php

namespace App\Jobs\Middleware;

use Throwable;

class Tracked
{
    /**
     * Wrap the Trackable job with a try/catch and logic to ensure it is tracked.
     *
     * @param mixed $job
     * @param callable $next
     * @return void
     */
    public function handle($job, $next)
    {
        $job->trackedJob->markAsStarted();

        try {
            $response = $next($job);

            // If the response is truthy, then we can assume
            // the trackedJob has been completed.
            if ($response) {
                $job->trackedJob->markAsFinished($response);
            }
        } catch (Throwable $e) {
            $job->fail($e);
        }
    }
}
