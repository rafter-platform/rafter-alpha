<?php

namespace App\Rafter;

class Rafter
{
    const ROUTE = '/_rafter/queue/work';

    public static function queueWorkerUrl()
    {
        return url(static::ROUTE, [], true);
    }
}
