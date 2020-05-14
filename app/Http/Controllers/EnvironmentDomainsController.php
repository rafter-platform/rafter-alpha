<?php

namespace App\Http\Controllers;

use App\Environment;
use App\Project;
use Illuminate\Http\Request;

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
