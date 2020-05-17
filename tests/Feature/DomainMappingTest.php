<?php

namespace Tests\Feature;

use App\DomainMapping;
use App\Http\Livewire\EnvironmentDomains;
use App\Jobs\CheckDomainMappingStatus;
use App\Jobs\ReverifyDomainMapping;
use Exception;
use Google_Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\Support\FakeGoogleApiClient;
use Tests\TestCase;
use Illuminate\Support\Str;
use Livewire\Livewire;

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
        $this->assertRegExp('/certificate/i', $mapping->message);
        $this->assertRegExp('/issue/i', $mapping->message);

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

    public function test_a_mapping_can_be_deleted()
    {
        Http::fake([
            '*' => Http::response([], 200),
        ]);

        $mapping = $this->createMapping();

        $mapping->markActive();

        $mapping->delete();

        Http::assertSent(function ($request) {
            return Str::of($request->url())->contains('domainmappings/www.rafter.app')
                && $request->method() == 'DELETE';
        });
    }

    public function test_an_errored_mapping_is_not_deleted_remotely()
    {
        $mapping = $this->createMapping();

        $mapping->markError(new Exception('Some Error'));

        $mapping->delete();

        Http::assertNothingSent();
    }

    public function test_user_can_delete_mapping()
    {
        Http::fake([
            '*' => Http::response([], 200),
        ]);

        $mapping = $this->createMapping();
        $mapping->markActive();

        $user = $mapping->environment->project->team->owner;
        $user->setCurrentTeam($mapping->environment->project->team);
        $user->refresh();

        $this->actingAs($user);

        Livewire::test(EnvironmentDomains::class, ['environment' => $mapping->environment])
            ->call('deleteDomain', $mapping->id);

        $this->assertFalse($mapping->exists());

        Http::assertSent(function ($request) {
            return Str::of($request->url())->contains('domainmappings/www.rafter.app')
                && $request->method() == 'DELETE';
        });
    }

    public function test_user_cannot_delete_other_mappings()
    {
        Http::fake([
            '*' => Http::response([], 200),
        ]);

        $mapping = $this->createMapping();
        $mapping->markActive();

        $this->actingAs(factory('App\User')->create());

        Livewire::test(EnvironmentDomains::class, ['environment' => $mapping->environment])
            ->call('deleteDomain', $mapping->id);

        $this->assertTrue($mapping->exists());

        Http::assertNothingSent();
    }

    public function test_user_can_check_domain_status()
    {
        Http::fake([
            '*' => Http::response([], 200),
        ]);

        Http::assertNothingSent();

        $mapping = $this->createMapping();
        $mapping->markActive();

        $user = $mapping->environment->project->team->owner;
        $user->setCurrentTeam($mapping->environment->project->team);
        $user->refresh();

        $this->actingAs($user);

        Livewire::test(EnvironmentDomains::class, ['environment' => $mapping->environment])
            ->call('checkDomainStatus', $mapping->id);

        Http::assertSent(function ($request) {
            return Str::of($request->url())->contains('domainmappings/www.rafter.app')
                && $request->method() == 'GET';
        });
    }

    public function test_user_cannot_check_status_of_other_mappings()
    {
        Http::fake([
            '*' => Http::response([], 200),
        ]);

        Http::assertNothingSent();

        $mapping = $this->createMapping();
        $mapping->markActive();

        $this->actingAs(factory('App\User')->create());

        Livewire::test(EnvironmentDomains::class, ['environment' => $mapping->environment])
            ->call('checkDomainStatus', $mapping->id);

        Http::assertNothingSent();
    }

    public function test_user_can_reverify_domain()
    {
        Http::fake([
            '*' => Http::response([], 200),
        ]);

        Queue::fake();

        $mapping = $this->createMapping();
        $mapping->markUnverified();

        $user = $mapping->environment->project->team->owner;
        $user->setCurrentTeam($mapping->environment->project->team);
        $user->refresh();

        $this->actingAs($user);

        Livewire::test(EnvironmentDomains::class, ['environment' => $mapping->environment])
            ->call('verifyDomain', $mapping->id);

        Queue::assertPushed(ReverifyDomainMapping::class, function ($job) use ($mapping) {
            return $job->mappingId === $mapping->id;
        });
    }

    public function test_user_cannot_reverify_other_mappings()
    {
        Http::fake([
            '*' => Http::response([], 200),
        ]);

        Queue::fake();

        $mapping = $this->createMapping();
        $mapping->markUnverified();

        $this->actingAs(factory('App\User')->create());

        Livewire::test(EnvironmentDomains::class, ['environment' => $mapping->environment])
            ->call('verifyDomain', $mapping->id);

        Queue::assertNothingPushed();
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
