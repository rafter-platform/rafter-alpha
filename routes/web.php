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

use App\Jobs\TestJob;
use Illuminate\Support\Facades\Auth;

if (app()->environment('local')) {
    Auth::loginUsingId(1);
}

// GitHub authorization flow
Route::get('auth/github', 'SourceProviderController@store');

// Dynamic Dockerfiles and entrypoints for Cloud Build
Route::get('/build/{type}/{file}', 'BuildInstructionsController@show')->name('build-instructions');

// Incoming GitHub webhooks
Route::post('/hooks/{type}', 'HookDeploymentController@store');

// TODO: Remove
Route::get('/test', function () {
    TestJob::dispatch(12);
});

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::group(['middleware' => ['auth']], function () {
    // Dashboard
    Route::get('/home', 'HomeController@index')->name('home');

    // Google Projects
    Route::resource('google-projects', 'GoogleProjectController');

    // Projects
    Route::resource('projects', 'ProjectController');

    // Environments
    Route::resource('projects.environments', 'EnvironmentController');

    // Environment Databases
    Route::get('/projects/{project}/environments/{environment}/database', 'EnvironmentDatabaseController@show')
        ->name('projects.environments.database');
    Route::put('/projects/{project}/environments/{environment}/database', 'EnvironmentDatabaseController@update')
        ->name('projects.environments.database.update');

    // Deployments
    Route::resource('projects.environments.deployments', 'DeploymentController');

    // Database Instances
    Route::resource('databases', 'DatabaseInstanceController');
});
