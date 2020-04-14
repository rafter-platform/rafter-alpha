<?php

namespace App\Contracts;

use App\Deployment;

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
     * Get the latest commit hash for the given repository and branch.
     *
     * @param  string  $repository
     * @param  string  $branch
     * @return string
     */
    public function latestHashFor($repository, $branch);

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
}
