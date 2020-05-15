<?php

namespace App\Http\Controllers;

use App\Environment;
use App\Project;

class EnvironmentDomainsController extends Controller
{
    public function __invoke(Project $project, Environment $environment)
    {
        return view('environments.domains', [
            'project' => $project,
            'environment' => $environment,
        ]);
    }
}
