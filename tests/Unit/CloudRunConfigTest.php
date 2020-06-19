<?php

namespace Tests\Unit;

use App\Deployment;
use App\GoogleCloud\CloudRunConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CloudRunConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_stuff_is_set()
    {
        $deployment = factory(Deployment::class)->create();
        $config = (new CloudRunConfig($deployment))->config();

        $this->assertSame('serving.knative.dev/v1', $config['apiVersion']);
        $this->assertSame('Service', $config['kind']);
    }

    public function test_memory_and_cpu_limits_are_set()
    {
        $deployment = factory(Deployment::class)->create();
        $deployment->environment->setOption('web_memory', '2Gi');
        $deployment->environment->setOption('web_cpu', 2);

        $config = (new CloudRunConfig($deployment))->config();
        $limits = $config['spec']['template']['spec']['containers'][0]['resources']['limits'];

        $this->assertSame('2Gi', $limits['memory']);
        $this->assertSame('2', $limits['cpu']);
    }

    public function test_other_container_limits_are_set()
    {
        $deployment = factory(Deployment::class)->create();

        $config = (new CloudRunConfig($deployment))->config();
        $spec = $config['spec']['template']['spec'];
        $annotations = $config['spec']['template']['metadata']['annotations'];

        $this->assertSame(300, $spec['timeoutSeconds']);
        $this->assertSame(80, $spec['containerConcurrency']);
        $this->assertSame('1000', $annotations['autoscaling.knative.dev/maxScale']);
    }
}
