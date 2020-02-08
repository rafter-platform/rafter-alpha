<?php

namespace App\Http\Controllers;

use App\Environment;
use App\Project;
use Illuminate\Http\Request;

class EnvironmentSettingsController extends Controller
{
    public function index(Project $project, Environment $environment)
    {
        return view('environments.settings', [
            'environment' => $environment,
            'project' => $project,
        ]);
    }

    public function store(Request $request, Project $project, Environment $environment)
    {
        $this->validate($request, [
            'environmental_variables' => ['string'],
        ]);

        if (! empty($request->environmental_variables)) {
            $environment->update(['environmental_variables' => $request->environmental_variables]);

            $environment->activeDeployment()->redeploy(auth()->user()->id);
        }

        return back()->with('status', 'Settings updated');
    }
}
