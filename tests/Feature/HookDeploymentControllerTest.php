<?php

namespace Tests\Feature;

use App\Environment;
use App\Jobs\StartDeployment;
use App\Services\GitHub;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class HookDeploymentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $sourceProvider;
    protected $environment;
    protected $gitHubMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->withoutExceptionHandling();

        $this->gitHubMock = GitHub::parialMock();
        $this->gitHubMock->shouldReceive('token')->andReturn('notatoken');

        $this->environment = factory(Environment::class)->create([
            'branch' => 'master',
        ]);

        $this->sourceProvider = $this->environment->project->sourceProvider;
    }

    public function test_it_starts_deployment_immediately_if_matching_environment_found()
    {
        Queue::fake();
        Http::fake($this->getGitHubHttpMock());

        $this->assertCount(0, $this->environment->deployments);

        $payload = $this->generatePayload();

        $signature = 'sha1=' . hash_hmac('sha1', json_encode($payload), config('services.github.webhook_secret'));

        $this->postJson('/hooks/github', $payload, [
            'X-GitHub-Event' => 'push',
            'X-Hub-Signature' => $signature,
        ])
            ->assertOk();

        $this->assertCount(1, $this->environment->refresh()->deployments);

        Queue::assertPushed(StartDeployment::class);
    }

    public function test_it_does_not_deploy_if_checks_are_not_passing_and_user_has_opted_to_wait()
    {
        Queue::fake();

        $this->environment->options['wait_for_checks'] = true;
        $this->environment->save();

        $this->assertCount(0, $this->environment->deployments);

        $payload = $this->generatePayload();

        $signature = 'sha1=' . hash_hmac('sha1', json_encode($payload), config('services.github.webhook_secret'));

        Http::fake(array_merge([
            "api.github.com/repos/{$this->environment->project->repository}/commits/abc123/status" => Http::sequence()
                ->push(['state' => 'pending'])
                ->push(['state' => 'success']),
        ], $this->getGitHubHttpMock()));

        $this->postJson('/hooks/github', $payload, [
            'X-GitHub-Event' => 'push',
            'X-Hub-Signature' => $signature,
        ])
            ->assertOk();

        $this->assertCount(0, $this->environment->refresh()->deployments);

        Queue::assertNotPushed(StartDeployment::class);

        // But if the checks are passing instantly, it will deploy:
        $this->postJson('/hooks/github', $payload, [
            'X-GitHub-Event' => 'push',
            'X-Hub-Signature' => $signature,
        ])
            ->assertOk();

        $this->assertCount(1, $this->environment->refresh()->deployments);

        Queue::assertPushed(StartDeployment::class);
    }

    public function test_it_attempts_to_create_github_deployment_and_deploys_if_successful()
    {
        Queue::fake();
        Http::fake($this->getGitHubHttpMock());

        $payload = $this->generatePayload();

        $signature = 'sha1=' . hash_hmac('sha1', json_encode($payload), config('services.github.webhook_secret'));

        $this->postJson('/hooks/github', $payload, [
            'X-GitHub-Event' => 'push',
            'X-Hub-Signature' => $signature,
        ])
            ->assertOk();

        Http::assertSent(function ($request) {
            return $request->url() == "https://api.github.com/repos/{$this->environment->project->repository}/deployments";
        });

        $this->assertCount(1, $this->environment->refresh()->deployments);

        Queue::assertPushed(StartDeployment::class);
    }

    public function test_it_attempts_to_create_github_deployment_and_does_not_deploy_if_not_successful()
    {
        Queue::fake();

        $payload = $this->generatePayload();

        $signature = 'sha1=' . hash_hmac('sha1', json_encode($payload), config('services.github.webhook_secret'));

        Http::fake([
            "api.github.com/repos/{$this->environment->project->repository}/deployments" => Http::sequence()
                ->push([
                    "message" => "Auto-merged master into topic-branch on deployment."
                ], 202)
                ->push([
                    "message" => "Conflict: Commit status checks failed for topic-branch."
                ], 409),

            "*" => Http::response([], 200),
        ]);

        // Try twice, with 202 and 409 expected response codes
        $times = 1;
        while ($times <= 2) {
            $this->postJson('/hooks/github', $payload, [
                'X-GitHub-Event' => 'push',
                'X-Hub-Signature' => $signature,
            ])
                ->assertOk();

            Http::assertSent(function ($request) {
                return $request->url() == "https://api.github.com/repos/{$this->environment->project->repository}/deployments";
            });

            $this->assertCount(0, $this->environment->refresh()->deployments);

            Queue::assertNotPushed(StartDeployment::class);

            $times++;
        }
    }

    protected function generatePayload()
    {
        return [
            'installation' => [
                'id' => $this->sourceProvider->installation_id,
            ],
            'ref' => 'refs/heads/master',
            'repository' => [
                'full_name' => $this->environment->project->repository,
            ],
            'head_commit' => [
                'id' => 'abc123',
                'message' => 'Changed some stuff',
            ],
            'pusher' => [
                'rafter.pal@gmail.com',
            ]
        ];
    }

    protected function getGitHubHttpMock()
    {
        return [
            "api.github.com/repos/{$this->environment->project->repository}/deployments" => Http::response([
                'id' => '12345',
            ]),

            "*" => Http::response([], 200),
        ];
    }
}
