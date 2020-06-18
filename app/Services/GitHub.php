<?php

namespace App\Services;

use App\SourceProvider;
use App\Contracts\SourceProviderClient;
use App\Deployment;
use Exception;
use Firebase\JWT\JWT;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class GitHub implements SourceProviderClient
{
    protected $source;

    public function __construct(SourceProvider $source)
    {
        $this->source = $source;
    }

    /**
     * Determine if the source control credentials are valid.
     *
     * @return bool
     */
    public function valid()
    {
        try {
            $response = $this->request('user/repos');

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Validate the given repository and branch are valid.
     *
     * @param  string  $repository
     * @param  string  $branch
     * @return bool
     */
    public function validRepository($repository, $branch)
    {
        if (empty($repository)) {
            return false;
        }

        try {
            $response = $this->request("repos/{$repository}/branches");
        } catch (RequestException $e) {
            return false;
        }

        if (empty($branch)) {
            return true;
        }

        return collect($response)->contains(function ($b) use ($branch) {
            return $b['name'] === $branch;
        });
    }

    /**
     * Validate the given repository and commit hash are valid.
     *
     * @param  string  $repository
     * @param  string  $hash
     * @return bool
     */
    public function validCommit($repository, $hash)
    {
        if (empty($repository) || empty($hash)) {
            return false;
        }

        try {
            $response = $this->request("repos/{$repository}/commits/{$hash}");
        } catch (RequestException $e) {
            return false;
        }

        return $response['sha'] === $hash;
    }

    /**
     * Get the latest commit hash for the given repository and branch.
     *
     * @param  string  $repository
     * @param  string  $branch
     * @return string
     */
    public function latestHashFor($repository, $branch)
    {
        return $this->request("repos/{$repository}/commits?sha={$branch}&per_page=1")[0]['sha'];
    }

    /**
     * Get the tarball URL for the given deployment.
     *
     * @param  \App\Deployment  $deployment
     * @return string
     */
    public function tarballUrl(Deployment $deployment)
    {
        return sprintf(
            'https://api.github.com/repos/%s/tarball/%s?access_token=%s',
            $deployment->repository(),
            $deployment->commit_hash,
            '$$TOKEN'
        );
    }

    /**
     * Get the clone URL with a token.
     *
     * @param Deployment $deployment
     * @return string
     */
    public function cloneUrl(Deployment $deployment)
    {
        return sprintf(
            "https://x-access-token:%s@github.com/%s.git",
            $this->token(),
            $deployment->repository()
        );
    }

    /**
     * Get the commit hash from the given hook payload.
     *
     * @param  array  $payload
     * @return string|null
     */
    public function extractCommitFromHookPayload(array $payload)
    {
        return $payload['head_commit']['id'] ?? null;
    }

    /**
     * Get repositories available for this installation
     *
     * @return array
     */
    public function getRepositories($token = null)
    {
        $installationToken = $token ?: $this->token();

        return Http::withHeaders([
            'Accept' => "application/vnd.github.machine-man-preview+json",
            'Authorization' => "token $installationToken",
        ])
            ->get('https://api.github.com/installation/repositories')
            ->throw()
            ->json();
    }

    /**
     * Fetches new information about an installation and saves it.
     *
     * @return void
     */
    public function refreshInstallation()
    {
        $installation = $this->getInstallation();

        $this->source->meta = $installation;
        $this->source->save();
    }

    /**
     * Get an array of useful data about an installation.
     *
     * @return array
     */
    public function getInstallation(): array
    {
        $installation = $this->getInstallationAccessToken();
        $token = $installation['token'];

        $repositories = $this->getRepositories($token);

        $repositories = $repositories['repositories'];
        $avatar = $repositories[0]['owner']['avatar_url'];

        return [
            'installation_token' => $token,
            'installation_token_expires_at' => $installation['expires_at'],
            'repositories' => collect($repositories)->map->full_name->toArray(),
            'avatar' => $avatar,
        ];
    }

    /**
     * Get an installation access token.
     *
     * @return array
     */
    public function getInstallationAccessToken(): array
    {
        $jwt = $this->createJwt();
        $installationId = $this->source->installation_id;

        return Http::withHeaders([
            'Authorization' => "Bearer $jwt",
            'Accept' => 'application/vnd.github.machine-man-preview+json',
        ])
            ->post("https://api.github.com/app/installations/$installationId/access_tokens")
            ->throw()
            ->json();
    }

    public function createDeployment(Deployment $deployment)
    {
        try {
            $response = $this->request("repos/{$deployment->repository()}/deployments", 'POST', [
                'ref' => $deployment->commit_hash,
                'environment' => $deployment->environment->name,
                'description' => 'Deploy request from Rafter',

                // We tell GitHub we want to start this deployment regardless of whether tests have passed.
                // TODO: Implement user's preference about whether we should wait for tests.
                'required_contexts' => [],
            ]);

            $deploymentId = $response['id'];
            $deployment->meta['github_deployment_id'] = $deploymentId;
            $deployment->save();

            $this->updateDeploymentStatus($deployment, 'in_progress');
        } catch (RequestException $e) {
            /**
             * TODO:
             * 1. Inspect what error it is
             * 2. If it's about commit statuses not being ready, bubble it up somehow?
             * 3. If it's about the fact that a merge commit was done, bubble it up so we somehow don't deploy until the merge
             * commit comes through.
             */
        }
    }

    public function updateDeploymentStatus(Deployment $deployment, $state)
    {
        if (empty($deployment->meta['github_deployment_id'])) return;

        $this->request("repos/{$deployment->repository()}/deployments/{$deployment->meta['github_deployment_id']}/statuses", 'POST', [
            'state' => $state,
            'log_url' => route('projects.environments.deployments.show', [
                $deployment->project(),
                $deployment->environment,
                $deployment
            ]),
            'environment_url' => $deployment->environment->url,
        ]);
    }

    /**
     * Create a JWT to call the GitHub API.
     *
     * @return void
     */
    protected function createJwt()
    {
        $secret = config('services.github.private_key');

        $payload = [
            'iat' => time(),
            'exp' => time() + 10 * 60,
            'iss' => config('services.github.app_id'),
        ];

        return JWT::encode($payload, $secret, 'RS256');
    }

    protected function request($endpoint, $method = 'get', $data = [])
    {
        try {
            return Http::withToken($this->token(), 'token')
                ->accept("application/vnd.github.machine-man-preview+json, application/vnd.github.ant-man-preview+json, application/vnd.github.flash-preview+json")
                ->{$method}('https://api.github.com/' . $endpoint, $data)
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            logger()->error($exception->response->body());

            throw $exception;
        }
    }

    /**
     * Determine whether the stored installation access token is expired.
     *
     * @return boolean
     */
    protected function tokenIsExpired()
    {
        return now() > Carbon::parse($this->source['meta']['installation_token_expires_at']);
    }

    /**
     * Get the access token for the given SourceProvider.
     */
    public function token(): string
    {
        if ($this->tokenIsExpired()) {
            $this->refreshInstallation();
        }

        return $this->source->refresh()->meta['installation_token'];
    }
}
