<?php

namespace Tests\Feature;

use App\Environment;
use App\Jobs\StartDeployment;
use App\Services\GitHub;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GitHubHookControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $sourceProvider;
    protected $environment;
    protected $gitHubMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->withoutExceptionHandling();

        $this->gitHubMock = GitHub::partialMock();
        $this->gitHubMock->shouldReceive('token')->andReturn('notatoken');
        $this->gitHubMock->shouldReceive('messageForHash')->andReturn('some message');

        $this->environment = factory(Environment::class)->create([
            'branch' => 'master',
        ]);

        $this->sourceProvider = $this->environment->project->sourceProvider;

        // Ensure sourceProvider owner is on the project's team
        $this->environment->project->team->users()->save($this->sourceProvider->user);
        $this->sourceProvider->user->setCurrentTeam($this->environment->project->team);
    }

    public function test_it_starts_deployment_immediately_if_matching_environment_found()
    {
        Queue::fake();
        Http::fake($this->getGitHubHttpMock());
        $this->environment->setOption('wait_for_checks', false);

        $this->assertCount(0, $this->environment->deployments);

        $payload = $this->generatePushPayload();

        $signature = 'sha1=' . hash_hmac('sha1', json_encode($payload), config('services.github.webhook_secret'));

        $this->postJson('/hooks/github', $payload, [
            'X-GitHub-Event' => 'push',
            'X-Hub-Signature' => $signature,
        ])
            ->assertOk();

        // We don't expect this specific hook to initiate the Rafter deployment;
        // a separate webhook from GitHub initiates that.
        $this->assertCount(0, $this->environment->refresh()->deployments);

        Queue::assertNotPushed(StartDeployment::class);
    }

    public function test_it_does_not_deploy_if_checks_are_not_passing_and_user_has_opted_to_wait()
    {
        Queue::fake();

        $this->environment->setOption('wait_for_checks', true);
        $this->environment->save();

        $payload = $this->generatePushPayload();

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

        Http::assertNotSent(function ($request) {
            return $request->url() == "https://api.github.com/repos/{$this->environment->project->repository}/deployments";
        });

        // But if the checks are passing instantly, it will deploy:
        $this->postJson('/hooks/github', $payload, [
            'X-GitHub-Event' => 'push',
            'X-Hub-Signature' => $signature,
        ])
            ->assertOk();

        Http::assertSent(function ($request) {
            return $request->url() == "https://api.github.com/repos/{$this->environment->project->repository}/deployments";
        });
    }

    public function test_it_attempts_to_create_github_deployment_and_deploys_if_successful()
    {
        Queue::fake();
        Http::fake($this->getGitHubHttpMock());

        $payload = $this->generatePushPayload();

        $signature = 'sha1=' . hash_hmac('sha1', json_encode($payload), config('services.github.webhook_secret'));

        $this->postJson('/hooks/github', $payload, [
            'X-GitHub-Event' => 'push',
            'X-Hub-Signature' => $signature,
        ])
            ->assertOk();

        Http::assertSent(function ($request) {
            return $request->url() == "https://api.github.com/repos/{$this->environment->project->repository}/deployments";
        });
    }

    public function test_it_attempts_to_create_github_deployment_and_does_not_deploy_if_not_successful()
    {
        Queue::fake();

        $payload = $this->generatePushPayload();

        $signature = 'sha1=' . hash_hmac('sha1', json_encode($payload), config('services.github.webhook_secret'));

        Http::fake([
            // NOTE: This is an odd duck. The deployment API calls (below) would normally not fail if the user is
            // first checking to see if the status is set. However, we're testing this specific path.
            "api.github.com/repos/{$this->environment->project->repository}/commits/*/status" => Http::response([
                'state' => 'success',
            ]),

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

            $times++;
        }
    }

    public function test_it_creates_deployment_on_status_webhook_if_commit_is_ready()
    {
        Queue::fake();

        $this->environment->options['wait_for_checks'] = true;
        $this->environment->update();

        $payload = $this->generateStatusPayload();

        $signature = 'sha1=' . hash_hmac('sha1', json_encode($payload), config('services.github.webhook_secret'));

        Http::fake($this->getGitHubHttpMock());

        $this->postJson('/hooks/github', $payload, [
            'X-GitHub-Event' => 'status',
            'X-Hub-Signature' => $signature,
        ])
            ->assertOk();

        Http::assertSent(function ($request) use ($payload) {
            return $request->url() == "https://api.github.com/repos/{$this->environment->project->repository}/deployments"
                && $request['payload'] == [
                    'environment_id' => $this->environment->id,
                    'initiator_id' => $this->sourceProvider->user->id,
                ]
                && $request['environment'] == $this->environment->name
                && $request['ref'] == $payload['sha'];
        });
    }

    public function test_it_does_not_start_deployment_on_status_webhook_if_not_waiting_on_checks()
    {
        Queue::fake();

        $this->environment->setOption('wait_for_checks', false);

        $payload = $this->generateStatusPayload();
        $signature = 'sha1=' . hash_hmac('sha1', json_encode($payload), config('services.github.webhook_secret'));

        $this->postJson('/hooks/github', $payload, [
            'X-GitHub-Event' => 'status',
            'X-Hub-Signature' => $signature,
        ])
            ->assertOk();

        Http::assertNothingSent();
    }

    public function test_it_does_not_start_deployment_on_status_webhook_if_head_commit_does_not_match()
    {
        Queue::fake();

        // E.g. an old status is finishing up, but the HEAD commit on the branch is newer.
        $this->gitHubMock->shouldReceive('latestHashFor')->andReturn('somenewhash');

        $payload = $this->generateStatusPayload();
        $signature = 'sha1=' . hash_hmac('sha1', json_encode($payload), config('services.github.webhook_secret'));

        $this->postJson('/hooks/github', $payload, [
            'X-GitHub-Event' => 'status',
            'X-Hub-Signature' => $signature,
        ])
            ->assertOk();

        Http::assertNothingSent();
    }

    public function test_it_starts_deployment_with_deployment_webhook()
    {
        Queue::fake();

        $payload = $this->generateDeploymentPayload();
        $signature = 'sha1=' . hash_hmac('sha1', json_encode($payload), config('services.github.webhook_secret'));

        $this->postJson('/hooks/github', $payload, [
            'X-GitHub-Event' => 'deployment',
            'X-Hub-Signature' => $signature,
        ])
            ->assertOk();

        $this->assertCount(1, $this->environment->refresh()->deployments);
        Queue::assertPushed(StartDeployment::class);

        $this->assertEquals(12345, $this->environment->deployments->first()->meta['github_deployment_id']);
    }

    public function test_it_does_not_start_deployment_if_conditions_not_met()
    {
        Queue::fake();

        // The Installation ID doesn't match
        $payload = $this->generateDeploymentPayload([
            'installation' => [
                'id' => 99999,
            ],
        ]);
        $signature = 'sha1=' . hash_hmac('sha1', json_encode($payload), config('services.github.webhook_secret'));

        $this->postJson('/hooks/github', $payload, [
            'X-GitHub-Event' => 'deployment',
            'X-Hub-Signature' => $signature,
        ])
            ->assertOk();

        $this->assertCount(0, $this->environment->refresh()->deployments);
        Queue::assertNotPushed(StartDeployment::class);

        // An environment ID was not provided
        $payload = $this->generateDeploymentPayload();
        unset($payload['deployment']['payload']['environment_id']);
        $signature = 'sha1=' . hash_hmac('sha1', json_encode($payload), config('services.github.webhook_secret'));

        $this->postJson('/hooks/github', $payload, [
            'X-GitHub-Event' => 'deployment',
            'X-Hub-Signature' => $signature,
        ])
            ->assertOk();

        $this->assertCount(0, $this->environment->refresh()->deployments);
        Queue::assertNotPushed(StartDeployment::class);

        // A deployment was already started manually elsewhere in Rafter
        $payload = $this->generateDeploymentPayload([
            'deployment' => [
                'payload' => [
                    'manual' => true,
                ]
            ]
        ]);
        $signature = 'sha1=' . hash_hmac('sha1', json_encode($payload), config('services.github.webhook_secret'));

        $this->postJson('/hooks/github', $payload, [
            'X-GitHub-Event' => 'deployment',
            'X-Hub-Signature' => $signature,
        ])
            ->assertOk();

        $this->assertCount(0, $this->environment->refresh()->deployments);
        Queue::assertNotPushed(StartDeployment::class);
    }

    protected function generatePushPayload()
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

    public function generateStatusPayload()
    {
        return [
            'id' => 6805126730,
            'sha' => "abc123",
            'name' => "rafter/rafter",
            'state' => "success",
            'installation' => [
                'id' => $this->sourceProvider->installation_id,
            ],
            'branches' => [
                [
                    'name' => 'master',
                ],
            ],
            'commit' => [
                'commit' => [
                    'message' => 'Doing some stuff',
                    'author' => [
                        'email' => $this->sourceProvider->user->email,
                    ],
                ],
            ],
        ];
    }

    protected function generateDeploymentPayload($overrides = [])
    {
        return array_merge_recursive([
            'action' => 'created',
            'deployment' => [
                'id' => 12345,
                'sha' => 'abc123',
                'environment' => 'production',
                'payload' => [
                    'environment_id' => $this->environment->id,
                    'initiator_id' => $this->sourceProvider->user->id,
                ],
            ],
            'repository' => [
                'full_name' => $this->environment->repository(),
            ],
            'installation' => [
                'id' => $this->sourceProvider->installation_id,
            ],
        ], $overrides);
    }

    protected function getGitHubHttpMock()
    {
        return [
            "api.github.com/repos/{$this->environment->project->repository}/deployments" => Http::response([
                'id' => 12345,
            ]),

            "api.github.com/repos/{$this->environment->project->repository}/commits/*/status" => Http::response([
                'state' => 'success',
            ]),

            "api.github.com/repos/{$this->environment->project->repository}/commits*" => Http::response([
                [
                    'sha' => 'abc123',
                ]
            ]),

            "*" => Http::response([], 200),
        ];
    }
}
