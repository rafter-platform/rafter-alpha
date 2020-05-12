<?php

namespace Tests\Feature;

use App\DomainMapping;
use App\Jobs\CheckDomainMappingStatus;
use Google_Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\Support\FakeGoogleApiClient;
use Tests\TestCase;

class DomainMappingTest extends TestCase
{
    use RefreshDatabase;

    const API_MASK = 'https://*-run.googleapis.com/apis/domains.cloudrun.com/v1/namespaces/*/domainmappings/www.rafter.app';

    public function setUp(): void
    {
        parent::setUp();

        $this->app->instance(Google_Client::class, new FakeGoogleApiClient);

        Queue::fake();
    }

    public function test_it_marks_status_if_unverified()
    {
        $mapping = $this->createMapping();

        Http::fake([
            static::API_MASK => $this->loadStub('domain-mapping-unverified'),
        ]);

        $mapping->checkStatus();

        $this->assertEquals(DomainMapping::STATUS_UNVERIFIED, $mapping->status);
        $this->assertRegExp('/verified owner/i', $mapping->message);

        Queue::assertNothingPushed();
    }

    public function test_it_marks_status_if_pending_dns_records()
    {
        $mapping = $this->createMapping();

        Http::fake([
            static::API_MASK => $this->loadStub('domain-mapping-dns-pending'),
        ]);

        $mapping->checkStatus();

        $this->assertEquals(DomainMapping::STATUS_PENDING_DNS, $mapping->status);
        $this->assertRegExp('/CNAME/i', $mapping->message);
        $this->assertRegExp('/www/i', $mapping->message);
        $this->assertRegExp('/ghs.googlehosted.com./i', $mapping->message);

        Queue::assertPushed(CheckDomainMappingStatus::class);
    }

    public function test_it_marks_status_if_pending_certificate()
    {
        $mapping = $this->createMapping();

        Http::fake([
            static::API_MASK => $this->loadStub('domain-mapping-certificate-pending'),
        ]);

        $mapping->checkStatus();

        $this->assertEquals(DomainMapping::STATUS_PENDING_CERTIFICATE, $mapping->status);
        $this->assertRegExp('/certificate/i', $mapping->message);
        $this->assertRegExp('/issued/i', $mapping->message);

        Queue::assertPushed(CheckDomainMappingStatus::class);
    }

    public function test_it_marks_status_if_ready()
    {
        $mapping = $this->createMapping();

        Http::fake([
            static::API_MASK => $this->loadStub('domain-mapping-ready'),
        ]);

        $mapping->checkStatus();

        $this->assertEquals(DomainMapping::STATUS_ACTIVE, $mapping->status);
        $this->assertEquals('', $mapping->message);

        Queue::assertNothingPushed();
    }

    public function test_it_stays_inactive_if_status_empty()
    {
        $mapping = $this->createMapping();

        Http::fake([
            static::API_MASK => $this->loadStub('domain-mapping-empty'),
        ]);

        $mapping->checkStatus();

        $this->assertEquals(DomainMapping::STATUS_INACTIVE, $mapping->status);

        Queue::assertPushed(CheckDomainMappingStatus::class);
    }

    protected function createMapping(): DomainMapping
    {
        return factory('App\DomainMapping')->create([
            'domain' => 'www.rafter.app',
            'environment_id' => factory('App\Environment')->create([
                'name' => 'production',
                'project_id' => factory('App\Project')->create([
                    'name' => 'rafter'
                ])->id,
            ])->id,
        ]);
    }
}
