<?php

namespace App\Jobs;

use App\Deployment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EnsureAppIsPublic implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Deployment $deployment;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Deployment $deployment)
    {
        $this->deployment = $deployment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $project = $this->deployment->project;
        $client = $project->googleProject->client();

        // Get the existing policies
        $policy = $client->getIamPolicyForCloudRunService($project);

        // Add the invoker role to allUsers (public, anon)
        $policy['bindings'][] = [
            'role' => 'roles/run.invoker',
            'members' => [
                'allUsers',
            ],
        ];

        // Update the policy
        $client->setIamPolicyForCloudRunService($project, $policy);

        // Assuming nothing went wrong, we are good.
    }
}
