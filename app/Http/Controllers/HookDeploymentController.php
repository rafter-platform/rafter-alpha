<?php

namespace App\Http\Controllers;

use App\Environment;
use App\Exceptions\GitHubAutoMergedException;
use App\Exceptions\GitHubDeploymentConflictException;
use App\Services\GitHubApp;
use App\User;
use Exception;
use Illuminate\Http\Request;

class HookDeploymentController extends Controller
{
    public function store(Request $request, $type)
    {
        if (!GitHubApp::verifyWebhookPayload($request)) {
            return response('', 403);
        }

        $event = $request->header('X-GitHub-Event');
        $methodName = 'handle' . ucfirst($event);

        if (method_exists($this, $methodName)) {
            return $this->{$methodName}($request, $type);
        }

        return response('', 200);
    }

    public function handlePush(Request $request, $type)
    {
        // TODO: Make this accessible in a FormRequest
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
            } catch (GitHubAutoMergedException $e) {
                logger("Canceled deployment for {$repository}#{$hash} because it auto-merged an upstream branch.");

                continue;
            } catch (GitHubDeploymentConflictException $e) {
                logger("Canceled deployment for {$repository}#{$hash}: {$e->getMessage()}");

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

    public function handleStatus(Request $request, $type)
    {
        $installationId = $request->installation['id'];
        $repository = $request->name;
        $hash = $request->sha;
        $message = $request->commit['commit']['message'];
        $senderEmail = $request->commit['commit']['author']['email'] ?? null;
        $branches = collect($request->branches)->map(fn ($branch) => $branch['name']);

        $environments = Environment::query()
            ->whereIn('branch', $branches)
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
            if (!$environment->getOption('wait_for_checks')) {
                continue;
            }

            $latestHashOnBranch = $environment->sourceProvider()->client()->latestHashFor(
                $repository,
                $environment->branch
            );

            if ($latestHashOnBranch != $hash) {
                continue;
            }

            try {
                $environment->sourceProvider()->client()->createDeployment(
                    $repository,
                    $hash,
                    $environment->name
                );
            } catch (GitHubAutoMergedException $e) {
                logger("Canceled deployment for {$repository}#{$hash} because it auto-merged an upstream branch.");

                continue;
            } catch (GitHubDeploymentConflictException $e) {
                logger("Canceled deployment for {$repository}#{$hash}: {$e->getMessage()}");

                continue;
            }

            $user = User::where('email', $senderEmail)->first();
            $initiatorId = null;

            if ($user && $environment->project->team->hasUser($user)) {
                $initiatorId = $user->id;
            }

            $environment->deployHash($hash, $message, $initiatorId);
        }
    }
}
