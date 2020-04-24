<?php

namespace App\Http\Controllers;

use App\Deployment;
use App\Environment;
use App\Project;
use Illuminate\Http\Request;

class RedeployDeploymentController extends Controller
{
    public function __invoke(Project $project, Environment $environment, Deployment $deployment)
    {
        $this->authorize('update', $deployment);

        $newDeployment = $deployment->redeploy(auth()->user()->id);

        return redirect()->route('projects.environments.deployments.show', [$project, $environment, $newDeployment])
            ->with('status', 'Deployment has been redeployed');
    }
}
