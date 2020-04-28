<?php

namespace App;

use App\Jobs\ConfigureQueues;
use App\Jobs\CreateCloudRunService;
use App\Jobs\CreateImageForDeployment;
use App\Jobs\EnsureAppIsPublic;
use App\Jobs\FinalizeDeployment;
use App\Jobs\StartDeployment;
use App\Jobs\StartScheduler;
use App\Jobs\UpdateCloudRunService;
use App\Jobs\UpdateCloudRunServiceWithUrls;
use App\Jobs\WaitForCloudRunServiceToDeploy;
use App\Jobs\WaitForImageToBeBuilt;

class DeploymentSteps
{
    protected $deployment;

    protected $isInitialDeployment = false;

    protected $isRedeploy = false;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $steps;

    public function __construct(Deployment $deployment)
    {
        $this->deployment = $deployment;
        $this->steps = collect();
    }

    public static function for(Deployment $deployment)
    {
        return new static($deployment);
    }

    public function initialDeployment()
    {
        $this->isInitialDeployment = true;

        return $this;
    }

    public function redeploy()
    {
        $this->isRedeploy = true;

        return $this;
    }

    protected function projectType(): string
    {
        return $this->deployment->environment->project->type;
    }

    protected function shouldStartScheduler()
    {
        return $this->isInitialDeployment && $this->usesScheduler();
    }

    protected function usesScheduler(): bool
    {
        return in_array($this->projectType(), ['laravel']);
    }

    public function addStep(...$values)
    {
        foreach ($values as $step) {
            $this->steps->push($step);
        }
    }

    public function get(): array
    {
        $this->addStep(StartDeployment::class);

        if (!$this->isRedeploy) {
            $this->addStep(CreateImageForDeployment::class);
        }

        $this->addStep(ConfigureQueues::class);

        if (!$this->isRedeploy) {
            $this->addStep(WaitForImageToBeBuilt::class);
        }

        $this->addStep($this->isInitialDeployment ? CreateCloudRunService::class : UpdateCloudRunService::class);

        $this->addStep(WaitForCloudRunServiceToDeploy::class);

        if ($this->isInitialDeployment) {
            $this->addStep(
                UpdateCloudRunServiceWithUrls::class,
                // Deploy the service another time, since we now have URL env vars set
                WaitForCloudRunServiceToDeploy::class,
                EnsureAppIsPublic::class
            );
        }

        if ($this->shouldStartScheduler()) {
            $this->addStep(StartScheduler::class);
        }

        $this->addStep(FinalizeDeployment::class);

        return $this->steps
            ->map(fn ($step) => new $step($this->deployment))
            ->toArray();
    }
}
