<?php

namespace App\Http\Controllers;

use App\Environment;
use App\Project;

class EnvironmentSettingsController extends Controller
{
    public function index(Project $project, Environment $environment)
    {
        return view('environments.settings', [
            'environment' => $environment,
            'project' => $project,
        ]);
    }
}
