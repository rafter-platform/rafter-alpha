<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\BuildInstructionsController;
use App\Http\Controllers\CommandController;
use App\Http\Controllers\DatabaseInstanceController;
use App\Http\Controllers\DeploymentController;
use App\Http\Controllers\EnvironmentController;
use App\Http\Controllers\EnvironmentDatabaseController;
use App\Http\Controllers\EnvironmentDomainsController;
use App\Http\Controllers\EnvironmentLogController;
use App\Http\Controllers\EnvironmentSettingsController;
use App\Http\Controllers\GoogleProjectController;
use App\Http\Controllers\HookDeploymentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RedeployDeploymentController;
use App\Http\Controllers\SourceProviderController;
use App\Jobs\TestJob;
use Illuminate\Support\Facades\Auth;

if (app()->environment('local')) {
    Auth::loginUsingId(1);
}

// Dynamic Dockerfiles and entrypoints for Cloud Build
Route::get('/build/{type}/{file}', BuildInstructionsController::class.'@show')->name('build-instructions');

// Incoming GitHub webhooks
Route::post('/hooks/{type}', HookDeploymentController::class.'@store');

// TODO: Remove
Route::get('/test', function () {
    TestJob::dispatch(12);
});

Route::view('/', 'welcome');

Auth::routes();

Route::group(['middleware' => ['auth']], function () {
    // Dashboard
    Route::get('/home', HomeController::class.'@index')->name('home');

    // Google Projects
    Route::resource('google-projects', GoogleProjectController::class);

    // Projects
    Route::resource('projects', ProjectController::class);

    // Environments
    Route::resource('projects.environments', EnvironmentController::class);

    // Environment Databases
    Route::resource('projects.environments.database', EnvironmentDatabaseController::class)
        ->only(['index', 'store', 'destroy']);

    // Environment Settings
    Route::resource('projects.environments.settings', EnvironmentSettingsController::class)
        ->only(['index', 'store']);

    // Deployments
    Route::resource('projects.environments.deployments', DeploymentController::class);
    Route::post('projects/{project}/environments/{environment}/deployments/{deployment}/redeploy', RedeployDeploymentController::class)
        ->name('projects.environments.deployments.redeploy');

    // Logs
    Route::get('projects/{project}/environments/{environment}/logs', EnvironmentLogController::class)
        ->name('projects.environments.logs');

    // Domains
    Route::get('projects/{project}/environments/{environment}/domains', EnvironmentDomainsController::class)
        ->name('projects.environments.domains');

    // Commands
    Route::get('projects/{project}/environments/{environment}/commands', CommandController::class.'@index')
        ->name('projects.environments.commands.index');
    Route::get('projects/{project}/environments/{environment}/commands/{command}', CommandController::class.'@show')
        ->name('projects.environments.commands.show');

    // Database Instances
    Route::resource('database-instances', DatabaseInstanceController::class);

    // Source Providers
    Route::resource('source-providers', SourceProviderController::class);

    // Inbound Source Provider Authorizations
    Route::get('auth/github', SourceProviderController::class.'@store');
});
