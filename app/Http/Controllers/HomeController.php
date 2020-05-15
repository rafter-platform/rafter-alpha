<?php

namespace App\Http\Controllers;

use App\Services\GitHubApp;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home', [
            'githubAppUrl' => GitHubApp::installationUrl(),
        ]);
    }
}
