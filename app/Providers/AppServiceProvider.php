<?php

namespace App\Providers;

use App\DatabaseInstance;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Since we use /databases to actually look up DatabaseInstances, we need to tell
        // Laravel that's what we mean instead of the Database model
        Route::model('database', DatabaseInstance::class);
    }
}
