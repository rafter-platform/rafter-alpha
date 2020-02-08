<?php

namespace App\Rafter\Http\Middleware;

use Closure;

class EnsureRafterWorker
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! env('IS_RAFTER_WORK', false)) {
            return response('Not a valid worker service', 403);
        }

        return $next($request);
    }
}
