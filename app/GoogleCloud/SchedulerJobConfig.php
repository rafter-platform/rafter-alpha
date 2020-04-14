<?php

namespace App\GoogleCloud;

use App\Environment;

class SchedulerJobConfig
{
    protected $environment;

    public function __construct(Environment $environment) {
        $this->environment = $environment;
    }

    /**
     * Get the google project iD
     *
     * @return string
     */
    public function projectId()
    {
        return $this->environment->projectId();
    }

    /**
     * Get the location/region.
     *
     * @return string
     */
    public function location()
    {
        return $this->environment->region();
    }

    /**
     * Get the name for the job.
     *
     * @return string
     */
    protected function name(): string
    {
        return sprintf(
            "projects/%s/locations/%s/jobs/%s",
            $this->projectId(),
            $this->location(),
            $this->environment->slug() . '-run-schedule'
        );
    }

    /**
     * Get the cron schedule. Currently set to one minute.
     *
     * @return string
     */
    protected function schedule(): string
    {
        return '* * * * *';
    }

    /**
     * Get the endpoint the Cloud Scheduler job should hit to run the schedule.
     *
     * @return string
     */
    protected function workerEndpoint(): string
    {
        return $this->environment->worker_url . '/_rafter/schedule/run';
    }

    /**
     * Get the service account email to be used for the OIDC token.
     *
     * @return string
     */
    protected function serviceAccountEmail(): string
    {
        return $this->environment->serviceAccountEmail();
    }

    /**
     * Get the HTTP Target config for the job.
     *
     * @return array
     */
    protected function httpTarget(): array
    {
        return [
            'uri' => $this->workerEndpoint(),
            'httpMethod' => 'POST',
            'oidcToken' => [
                'serviceAccountEmail' => $this->serviceAccountEmail(),
            ],
        ];
    }

    /**
     * Get the config value for the API call
     *
     * @return array
     */
    public function config()
    {
        return [
            'name' => $this->name(),
            'schedule' => $this->schedule(),
            'httpTarget' => $this->httpTarget(),
        ];
    }
}
