<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);

        $user = User::first();
        $team = $user->currentTeam;

        /**
         * Connect a GitHub source provider, if provided.
         */
        $gitHubInstallationId = env('SEED_GITHUB_INSTALLATION_ID');

        if ($gitHubInstallationId) {
            $sourceProvider = $user->sourceProviders()->create([
                'name' => 'rafter-platform',
                'type' => 'github',
                'installation_id' => $gitHubInstallationId,
                'meta' => [],
            ]);

            $sourceProvider->refreshGitHubInstallation();
        }

        /**
         * Connect a Google Project, if provided.
         */
        $googleProjectId = env('SEED_GOOGLE_PROJECT_ID');
        $googleProjectNumber = env('SEED_GOOGLE_PROJECT_NUMBER');

        if ($googleProjectId && $googleProjectNumber && File::exists(__DIR__ . '/../../service-account.json')) {
            $googleProject = $team->googleProjects()->create([
                'status' => 'ready',
                'project_id' => $googleProjectId,
                'project_number' => $googleProjectNumber,
                'service_account_json' => json_decode(File::get(__DIR__ . '/../../service-account.json')),
            ]);

            $project = $team->projects()->create([
                'name' => 'rafter-example-laravel',
                'type' => 'laravel',
                'google_project_id' => $googleProject->id,
                'source_provider_id' => $sourceProvider->id ?? null,
                'repository' => 'rafter-platform/rafter-example-laravel',
                'region' => 'us-central1',
            ]);

            /**
             * TODO: Make this more flexible for other developers. The following logic assumes
             * the developer has deployed the service *at least once* already, and thus is short-circuiting
             * the normal initial environment and deployment creation process.
             *
             * A new developer may want to comment this out, or we can put it behind some sort of flag.
             */
            $environment = $project->environments()->create([
                'name' => 'production',
                'branch' => 'master',
                'url' => env('SEED_SERVICE_WEB_URL', 'https://laravel-example-production-nmyoncbzeq-uc.a.run.app'),
                'worker_url' => env('SEED_SERVICE_WORKER_URL', 'https://laravel-example-production-worker-nmyoncbzeq-uc.a.run.app'),
                'web_service_name' => 'laravel-example-production',
                'worker_service_name' => 'laravel-example-production-worker',
            ]);

            $environment->setInitialEnvironmentVariables();

            $deployment = $environment->deployments()->create([
                'initiator_id' => $user->id,
                'commit_message' => 'Initial (seeded) deploy.',
                'status' => 'successful',
            ]);

            $environment->activeDeployment()->associate($deployment);
            $environment->save();

            $environment->domainMappings()->create([
                'domain' => 'laravel-demo.rafter.app',
                'status' => 'active',
            ]);
        }
    }
}
