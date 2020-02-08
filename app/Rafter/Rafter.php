<?php

namespace App\Rafter;

class Rafter
{
    const QUEUE_ROUTE = '/_rafter/queue/work';

    /**
     * Get the URL to the queue worker
     *
     * @return string
     */
    public static function queueWorkerUrl()
    {
        return $_ENV['RAFTER_WORKER_URL'] . static::QUEUE_ROUTE;
    }
}
