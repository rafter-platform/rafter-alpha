<?php

namespace Tests\Unit;

use App\GoogleCloud\CloudRunService;
use Tests\TestCase;

class CloudRunServiceTest extends TestCase
{
    public $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = $this->loadStub('cloud-run-service');
    }

    public function test_it_gets_status_by_name()
    {
        $service = new CloudRunService($this->service);

        $this->assertSame('True', $service->getStatus('Ready'));
    }

    public function test_it_knows_whether_service_is_ready()
    {
        $service = new CloudRunService($this->service);

        $this->assertTrue($service->isReady());

        $service = new CloudRunService($this->loadStub('cloud-run-service-not-ready'));
        $this->assertFalse($service->isReady());
    }

    public function test_it_gets_urls()
    {
        $service = new CloudRunService($this->service);
        $this->assertSame("https://rafter-demo-nmyoncbzeq-uc.a.run.app", $service->getUrl());
    }

    public function test_it_knows_when_service_failed()
    {
        $service = new CloudRunService($this->loadStub('cloud-run-service-failed'));

        $this->assertFalse($service->isReady());
        $this->assertTrue($service->hasErrors());
        $this->assertSame(
            "Cloud Run error: Container failed to start. Failed to start and then listen on the port defined by the PORT environment variable. Logs for this revision might contain more information.",
            $service->getError()
        );
    }
}
