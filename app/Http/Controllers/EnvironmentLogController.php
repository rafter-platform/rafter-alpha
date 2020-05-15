<?php

namespace App\Http\Controllers;

use App\Environment;
use App\Project;

class EnvironmentLogController extends Controller
{
    public function __invoke(Project $project, Environment $environment)
    {
        return view('environments.logs', [
            'project' => $project,
            'environment' => $environment,
        ]);
    }
}
