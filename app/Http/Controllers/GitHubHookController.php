<?php

namespace App\Http\Controllers;

use App\Environment;
use App\Exceptions\GitHubAutoMergedException;
use App\Exceptions\GitHubDeploymentConflictException;
use App\Http\Requests\GitHubHookPushRequest;
use App\PendingSourceProviderDeployment;
use App\Services\GitHubApp;
use App\User;
use Illuminate\Http\Request;

class GitHubHookController extends Controller
{
    public function __invoke(Request $request)
    {
        if (!GitHubApp::verifyWebhookPayload($request)) {
            return response('', 403);
        }

        $event = $request->header('X-GitHub-Event');
        $methodName = 'handle' . ucfirst($event);

        if (method_exists($this, $methodName)) {
            return $this->{$methodName}($request);
        }

        return response('', 200);
    }

    public function handlePush($_request)
    {
        $request = app(GitHubHookPushRequest::class);

        foreach ($request->environments() as $environment) {
            if ($environment->shouldWaitForChecks($request->repository(), $request->hash())) {
                continue;
            }

            $pendingDeployment = PendingSourceProviderDeployment::make()
                ->forEnvironment($environment)
                ->forHash($request->hash())
                ->byUserId($environment->getInitiator($request->senderEmail())->id);

            try {
                $environment->sourceProvider()->client()->createDeployment($pendingDeployment);
            } catch (GitHubAutoMergedException $e) {
                logger("Canceled deployment for {$request->repository()}#{$request->hash()} because it auto-merged an upstream branch.");

                continue;
            } catch (GitHubDeploymentConflictException $e) {
                logger("Canceled deployment for {$request->repository()}#{$request->hash()}: {$e->getMessage()}");

                continue;
            }
        }

        return response('', 200);
    }

    public function handleStatus(Request $request)
    {
        $installationId = $request->installation['id'];
        $repository = $request->name;
        $hash = $request->sha;
        $senderEmail = $request->commit['commit']['author']['email'] ?? null;
        $branches = collect($request->branches)->map(fn ($branch) => $branch['name']);

        $environments = Environment::query()
            ->whereIn('branch', $branches)
            ->whereHas('project.sourceProvider', function ($query) use ($installationId) {
                $query->where([
                    ['installation_id', $installationId],
                    ['type', 'github'],
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

            $pendingDeployment = PendingSourceProviderDeployment::make()
                ->forEnvironment($environment)
                ->forHash($hash)
                ->byUserId($environment->getInitiator($senderEmail)->id);

            try {
                $environment->sourceProvider()->client()->createDeployment($pendingDeployment);
            } catch (GitHubAutoMergedException $e) {
                logger("Canceled deployment for {$repository}#{$hash} because it auto-merged an upstream branch.");

                continue;
            } catch (GitHubDeploymentConflictException $e) {
                logger("Canceled deployment for {$repository}#{$hash}: {$e->getMessage()}");

                continue;
            }
        }
    }

    public function handleDeployment(Request $request)
    {
        $installationId = $request->installation['id'];
        $environmentId = $request->deployment['payload']['environment_id'] ?? null;
        $manual = $request->deployment['payload']['manual'] ?? false;
        $initiatorId = $request->deployment['payload']['initiator_id'];
        $hash = $request->deployment['sha'];

        $environment = Environment::find($environmentId);

        if (!$environment || $environment->sourceProvider()->installation_id != $installationId || $manual) {
            return response('');
        }

        $environment->deployHash($hash, $initiatorId);

        return response('');
    }
}
