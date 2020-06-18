<?php

namespace App\Services;

use App\Contracts\SourceProviderClient;
use App\Deployment;

class FakeSourceProviderClient implements SourceProviderClient
{
    /**
     * Determine if the source control credentials are valid.
     *
     * @return bool
     */
    public function valid()
    {
        return true;
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
        return true;
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
        return true;
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
        return 'abc123';
    }

    /**
     * Get the tarball URL for the given deployment.
     *
     * @param  \App\Deployment  $deployment
     * @return string
     */
    public function tarballUrl(Deployment $deployment)
    {
        return 'https://some.url';
    }

    public function getRepositories()
    {
        return [
            'repositories' => [
                [
                    'full_name' => 'fake/repository',
                ],
            ],
        ];
    }

    public function cloneUrl(Deployment $deployment)
    {
        return 'https://some.url';
    }

    public function refreshInstallation()
    {
        // void
    }

    public function getInstallation()
    {
        return [];
    }

    public function token(): string
    {
        return 'notatoken';
    }

    public function createDeployment(Deployment $deployment)
    {
        //
    }

    public function updateDeploymentStatus(Deployment $deployment, string $state)
    {
        //
    }
}
