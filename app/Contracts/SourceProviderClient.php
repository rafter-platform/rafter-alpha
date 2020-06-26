<?php

namespace App\Contracts;

use App\Deployment;
use App\Environment;

interface SourceProviderClient
{
    /**
     * Determine if the source control credentials are valid.
     *
     * @return bool
     */
    public function valid();

    /**
     * Validate the given repository and branch are valid.
     *
     * @param  string  $repository
     * @param  string  $branch
     * @return bool
     */
    public function validRepository($repository, $branch);

    /**
     * Validate the given repository and commit hash are valid.
     *
     * @param  string  $repository
     * @param  string  $hash
     * @return bool
     */
    public function validCommit($repository, $hash);

    /**
     * Git the commit message for a given hash.
     *
     * @param string $repository
     * @param string $hash
     * @return string
     */
    public function messageForHash($repository, $hash): string;

    /**
     * Get the latest commit hash for the given repository and branch.
     *
     * @param  string  $repository
     * @param  string  $branch
     * @return string
     */
    public function latestHashFor($repository, $branch): string;

    /**
     * Get the latest commit for the given repository and branch.
     *
     * @param  string  $repository
     * @param  string  $branch
     * @return array
     */
    public function latestCommitFor($repository, $branch): array;

    /**
     * Get the tarball URL for the given deployment.
     *
     * @param  \App\Deployment  $deployment
     * @return string
     */
    public function tarballUrl(Deployment $deployment);

    /**
     * Get the repositories available to the user.
     *
     * @return array
     */
    public function getRepositories();

    /**
     * Get the URL to clone a given repository.
     *
     * @return string
     */
    public function cloneUrl(Deployment $deployment);

    /**
     * Only needed for GitHub.
     */
    public function refreshInstallation();

    /**
     * Only needed for GitHub.
     */
    public function getInstallation();

    /**
     * Fetch a token.
     */
    public function token(): string;

    /**
     * Create a new deployment within the provider's API.
     */
    public function createDeployment($repository, $commitHash, Environment $environment, $initiatorId);

    /**
     * Update a deployment's status.
     */
    public function updateDeploymentStatus(Deployment $deployment, string $state);

    /**
     * Whether the checks for a given commit are successful and complete.
     *
     * @param string $repository
     * @param string $hash
     * @return boolean
     */
    public function commitChecksSuccessful(string $repository, string $hash): bool;
}
