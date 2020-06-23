<?php

namespace App\Http\Controllers;

use App\Environment;
use App\Services\GitHubApp;
use App\User;
use Exception;
use Illuminate\Http\Request;

class HookDeploymentController extends Controller
{
    public function store(Request $request, $type)
    {
        if ($request->header('X-GitHub-Event') !== 'push') {
            return response('', 200);
        }

        if (!GitHubApp::verifyWebhookPayload($request)) {
            return response('', 403);
        }

        // Log::info($request->all());

        $installationId = $request->installation['id'];
        $branch = str_replace("refs/heads/", "", $request->ref);
        $repository = $request->repository['full_name'];
        $hash = $request->head_commit['id'];
        $message = $request->head_commit['message'];
        $senderEmail = $request->pusher['email'] ?? null;

        $environments = Environment::query()
            ->where('branch', $branch)
            ->whereHas('project.sourceProvider', function ($query) use ($installationId, $type) {
                $query->where([
                    ['installation_id', $installationId],
                    ['type', $type],
                ]);
            })
            ->whereHas('project', function ($query) use ($repository) {
                $query->where('repository', $repository);
            })
            ->get();

        foreach ($environments as $environment) {
            if ($environment->sourceProvider()->isGitHub() && $environment->getOption('wait_for_checks')) {
                if (!$environment->sourceProvider()->client()->commitChecksSuccessful($repository, $hash)) {
                    continue;
                }
            }

            try {
                $environment->sourceProvider()->client()->createDeployment(
                    $repository,
                    $hash,
                    $environment->name
                );
            } catch (Exception $e) {
                $name = class_basename($e);
                logger("Canceled deployment for {$repository}#{$hash} because received {$name} from GitHub");

                continue;
            }

            $user = User::where('email', $senderEmail)->first();
            $initiatorId = null;

            if ($user && $environment->project->team->hasUser($user)) {
                $initiatorId = $user->id;
            }

            $environment->deployHash($hash, $message, $initiatorId);
        }

        return response('', 200);
    }
}
