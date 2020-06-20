<?php

namespace App\GoogleCloud;

use App\Environment;
use Illuminate\Support\Collection;

class CloudBuildSecrets
{
    protected $environment;

    protected $secrets;

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
        $this->secrets = collect();
    }

    protected function addGitToken()
    {
        $this->secrets->push([
            'type' => 'git-token',
            'name' => $this->secretName('git-token'),
            'env_var' => 'TOKEN',
            'value' => $this->environment->sourceProvider()->token(),
        ]);
    }

    public function add(string $name)
    {
        $secret = PendingCloudBuildSecret::make($this->environment, $name);

        $this->secrets->push($secret);

        return $secret;
    }

    protected function secretName(string $type): string
    {
        return $this->environment->slug() . '-' . $type;
    }

    public function get(): Collection
    {
        $this->addGitToken();

        if ($this->environment->project->isRails() && $this->environment->hasEnvVar('RAILS_MASTER_KEY')) {
            $this->secrets->push([
                'type' => 'rails-master-key',
                'name' => $this->secretName('rails-master-key'),
                'env_var' => 'RAILS_MASTER_KEY',
                'value' => $this->environment->getEnvVar('RAILS_MASTER_KEY'),
            ]);
        }

        return $this->secrets;
    }
}
