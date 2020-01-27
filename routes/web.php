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
use App\Rafter\Rafter;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

Auth::loginUsingId(1);

Route::get('/test', function () {
    TestJob::dispatch(12);
});

Route::get('/', function () {
    return view('welcome');
});
Auth::routes();

Route::group(['middleware' => ['auth']], function () {
    Route::get('/home', 'HomeController@index')->name('home');
    Route::resource('google-projects', 'GoogleProjectController');
    Route::resource('projects', 'ProjectController');
    Route::resource('projects.environments', 'EnvironmentController');
    Route::resource('projects.environments.deployments', 'DeploymentController');
});
