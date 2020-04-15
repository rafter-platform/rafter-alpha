<?php

namespace App\Http\Controllers;

use App\Services\GitHubApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
