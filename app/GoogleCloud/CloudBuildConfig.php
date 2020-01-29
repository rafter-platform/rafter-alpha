<?php

namespace App\GoogleCloud;

use App\Deployment;

class CloudBuildConfig
{
    const DOCKERFILES = [
        'laravel' => 'https://storage.googleapis.com/rafter-dockerfiles/Dockerfile-laravel',
    ];

    protected $attributes = [];
    protected $manual = false;
    protected $deployment;
    protected $environment;

    public function __construct(Deployment $deployment) {
        $this->deployment = $deployment;
        $this->environment = $deployment->environment;
    }

    /**
     * Mark a manual deployment (i.e. not Git-based)
     *
     * @param string $bucket
     * @param string $object
     * @return self
     */
    public function forManualPush($bucket, $object)
    {
        $this->manual = true;
        $this->attributes['bucket'] = $bucket;
        $this->attributes['object'] = $object;

        return $this;
    }

    /**
     * Whether it's a manual deployment
     *
     * @return boolean
     */
    public function isManual()
    {
        return $this->manual;
    }

    /**
     * Whether it's a git-based project
     *
     * @return boolean
     */
    public function isGitBased()
    {
        return ! $this->isManual();
    }

    /**
     * Get the name of the blank (public) bucket hosting the blank ZIP for Git builds.
     *
     * @return string
     */
    protected function blankBucket()
    {
        return 'rafter-dockerfiles';
    }

    /**
     * Get the name of the blank ZIP file to be used to start Git builds.
     *
     * @return string
     */
    protected function blankZip()
    {
        return 'blank.tar.gz';
    }

    /**
     * Get the source spec for the Build.
     *
     * @return array
     */
    public function source()
    {
        if ($this->isManual()) {
            return [
                'storageSource' => [
                    'bucket' => $this->attributes['bucket'],
                    'object' => $this->attributes['object'],
                ],
            ];
        } else {
            return [
                'storageSource' => [
                    'bucket' => $this->blankBucket(),
                    'object' => $this->blankZip(),
                ]
            ];
        }
    }

    /**
     * The step to download the Git repository
     *
     * @return array
     */
    protected function downloadGitRepoStep()
    {
        return [
            'name' => 'gcr.io/cloud-builders/curl',
            'args' => [$this->deployment->tarballUrl(), '--output', 'repo.tar.gz'],
        ];
    }

    protected function extractGitRepoStep()
    {
        return [
            'name' => 'ubuntu',
            'args' => ['tar', 'xzvf', 'repo.tar.gz', '&&', 'mv', 'repo/*', ''],
        ];
    }

    /**
     * The steps required to build this image
     *
     * @return array
     */
    public function steps()
    {
        $steps = [
            // Pull the image down so we can build from cache
            [
                'name' => 'gcr.io/cloud-builders/docker',
                'entrypoint' => 'bash',
                'args' => ['-c', "docker pull {$this->imageLocation()}:latest || exit 0"],
            ],

            $this->isGitBased() ? $this->downloadGitRepoStep() : [],
            $this->isGitBased() ? $this->extractGitRepoStep() : [],

            // Copy the Dockerfile we need
            [
                'name' => 'gcr.io/cloud-builders/curl',
                'args' => [static::DOCKERFILES['laravel'], '--output', 'Dockerfile'],
            ],

            // TEST: Show the dir
            [
                'name' => 'ubuntu',
                'args' => ['ls', '-la', './'],
            ],

            // Build the image
            [
                'name' => 'gcr.io/cloud-builders/docker',
                'args' => [
                    'build',
                    '-t', $this->imageLocation(),
                    '--cache-from', "{$this->imageLocation()}:latest",
                    '.'
                ],
            ],

            // Upload it to GCR
            [
                'name' => 'gcr.io/cloud-builders/docker',
                'args' => ['push', $this->imageLocation()],
            ],
        ];

        return collect($steps)->filter();
    }

    public function imageLocation()
    {
        return "gcr.io/\$PROJECT_ID/{$this->environment->slug()}";
    }

    public function images()
    {
        return [
            $this->imageLocation(),
        ];
    }

    public function instructions()
    {
        return [
            'source' => $this->source(),
            'steps' => $this->steps(),
            'images' => $this->images(),
        ];
    }
}
