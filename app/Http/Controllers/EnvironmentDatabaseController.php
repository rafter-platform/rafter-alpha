<?php

namespace App\Http\Controllers;

use App\Environment;
use App\Project;
use Illuminate\Http\Request;

class EnvironmentDatabaseController extends Controller
{
    public function show(Project $project, Environment $environment)
    {
        return view('environments.database', [
            'project' => $project,
            'environment' => $environment,
            'databaseInstances' => auth()->user()->currentTeam->databaseInstances,
        ]);
    }
}
