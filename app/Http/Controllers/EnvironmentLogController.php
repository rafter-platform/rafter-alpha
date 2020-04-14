<?php

namespace App\Http\Controllers;

use App\Environment;
use App\Project;
use Illuminate\Http\Request;

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
