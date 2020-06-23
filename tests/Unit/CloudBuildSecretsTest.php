<?php

namespace Tests\Unit;

use App\Environment;
use App\GoogleCloud\CloudBuildSecrets;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CloudBuildSecretsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_always_includes_git_token()
    {
        $this->mockGitHubForDeployment();

        $environment = factory(Environment::class)->create();

        $secrets = new CloudBuildSecrets($environment);

        $this->assertCount(1, $secrets->get()->where('type', 'git-token'));

        $secret = $secrets->get()->firstWhere('type', 'git-token');

        $this->assertEquals('TOKEN', $secret['env_var']);
        $this->assertEquals($environment->slug() . '-git-token', $secret['name']);
        $this->assertEquals('notatoken', $secret['value']);
    }

    public function test_it_includes_master_key_if_rails_and_env_var_exists()
    {
        $this->mockGitHubForDeployment();

        $environment = factory(Environment::class)->state('rails')->create();

        // First, assume the user has NOT provided a RAILS_MASTER_KEY env var
        $secrets = new CloudBuildSecrets($environment);

        $this->assertCount(0, $secrets->get()->where('type', 'rails-master-key'));

        // Then we'll assume the user *has* added that key
        $environment->addEnvVar('RAILS_MASTER_KEY', 'somekeyhere');

        $secrets = new CloudBuildSecrets($environment->refresh());

        $this->assertCount(1, $secrets->get()->where('type', 'rails-master-key'));

        $secret = $secrets->get()->firstWhere('type', 'rails-master-key');
        $this->assertSame('RAILS_MASTER_KEY', $secret['env_var']);
        $this->assertSame('somekeyhere', $secret['value']);
    }
}
