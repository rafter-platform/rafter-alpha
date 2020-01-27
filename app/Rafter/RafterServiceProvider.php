<?php

namespace App\Rafter;

use App\Rafter\Queue\RafterConnector;
use App\Rafter\Queue\RafterWorker;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RafterServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->ensureRoutesAreDefined();

        Queue::extend('rafter', function () {
            return new RafterConnector;
        });
    }

    public function register()
    {
        $this->ensureQueueIsConfigured();
    }

    /**
     * Define internal routes for Rafter
     */
    public function ensureRoutesAreDefined()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        // Handle queue jobs
        Route::post(Rafter::ROUTE, function () {
            // TODO: Convert this to a controller?
            Artisan::call('rafter:work', [
                'message' => request()->getContent(),
                'headers' => base64_encode(json_encode(request()->headers->all()))
            ]);
        });
    }

    /**
     * Ensure Rafter queue is configured.
     */
    public function ensureQueueIsConfigured()
    {
        Config::set('queue.connections.rafter', [
            'driver' => 'rafter',
            'queue' => $_ENV['RAFTER_QUEUE'],
            'project_id' => $_ENV['RAFTER_PROJECT_ID'],
            'region' => $_ENV['RAFTER_REGION'],
        ]);

        if ($this->app->bound(RafterWorker::class)) {
            return;
        }

        $this->app->singleton(RafterWorker::class, function () {
            $isDownForMaintenance = function () {
                return $this->app->isDownForMaintenance();
            };

            return new RafterWorker(
                $this->app['queue'],
                $this->app['events'],
                $this->app[ExceptionHandler::class],
                $isDownForMaintenance
            );
        });
    }
}
