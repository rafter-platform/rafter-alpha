<?php

namespace Tests\Unit;

use App\GoogleCloud\CloudRunIamPolicy;
use Tests\TestCase;

class CloudRunIamPolicyTest extends TestCase
{
    public function test_it_knows_whether_a_service_is_public()
    {
        $payload = [
            'version' => 1,
            'etag' => "BwWk/fff4lc=",
            'bindings' => []
        ];

        $policy = new CloudRunIamPolicy($payload);

        $this->assertFalse($policy->isPublic());

        $payload = [
            'version' => 1,
            'etag' => "BwWk/fff4lc=",
            'bindings' => [
                [
                    'role' => 'roles/run.invoker',
                    'members' => ['allUsers'],
                ]
            ]
        ];

        $policy = new CloudRunIamPolicy($payload);

        $this->assertTrue($policy->isPublic());
    }

    public function test_it_sets_public()
    {
        $payload = [
            'version' => 1,
            'etag' => "BwWk/fff4lc=",
            'bindings' => []
        ];

        $policy = new CloudRunIamPolicy($payload);

        $this->assertFalse($policy->isPublic());

        $policy->setPublic();

        $this->assertTrue($policy->isPublic());
        $this->assertEquals([
            [
                'role' => 'roles/run.invoker',
                'members' => ['allUsers'],
            ]
        ], $policy->getPolicy()['bindings']);
    }

    public function test_it_does_not_add_duplicate_members()
    {
        $payload = [
            'version' => 1,
            'etag' => "BwWk/fff4lc=",
            'bindings' => [
                [
                    'role' => 'roles/run.invoker',
                    'members' => ['allUsers'],
                ]
            ]
        ];

        $policy = new CloudRunIamPolicy($payload);
        $policy->setPublic();

        $this->assertTrue($policy->isPublic());
        $this->assertEquals([
            [
                'role' => 'roles/run.invoker',
                'members' => ['allUsers'],
            ]
        ], $policy->getPolicy()['bindings']);
    }
}
