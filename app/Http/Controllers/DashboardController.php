<?php

namespace App\Http\Controllers;

use App\Services\GitHubApp;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (currentTeam()->projects()->count() == 0) {
            return redirect('/projects/create');
        }

        return view('dashboard');
    }
}
