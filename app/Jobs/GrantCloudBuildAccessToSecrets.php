<?php

namespace App\Jobs;

use App\GoogleProject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class GrantCloudBuildAccessToSecrets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $googleProject;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(GoogleProject $googleProject)
    {
        $this->googleProject = $googleProject;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $policy = $this->googleProject->client()->getProjectIamPolicy();
            $member = sprintf(
                'serviceAccount:%s@cloudbuild.gserviceaccount.com',
                $this->googleProject->project_number
            );

            $policy->addMemberToRole($member, 'roles/secretmanager.secretAccessor');

            $this->googleProject->client()->setProjectIamPolicy($policy);
        } catch (Throwable $e) {
            Log::error($e->getMessage());
        }
    }
}
