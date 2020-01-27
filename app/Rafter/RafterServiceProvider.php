<?php

namespace App\Rafter;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class RafterServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $manager = $this->app['queue'];

        $manager->addConnector('rafter', function () {
            return new RafterConnector;
        });
    }

    public function register()
    {
        $this->ensureQueueIsConfigured();
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
    }
}
