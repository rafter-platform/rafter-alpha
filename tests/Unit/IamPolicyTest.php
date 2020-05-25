<?php

namespace Tests\Unit;

use App\GoogleCloud\IamPolicy;
use Tests\TestCase;

class IamPolicyTest extends TestCase
{
    public function test_it_adds_a_new_member_to_a_new_role()
    {
        $payload = [
            'version' => 1,
            'etag' => "BwWk/fff4lc=",
            'bindings' => []
        ];

        $policy = new IamPolicy($payload);

        $policy->addMemberToRole('new-service@account.com', 'roles/someNewRole');

        $expected = [
            [
                'role' => 'roles/someNewRole',
                'members' => [
                    'new-service@account.com',
                ],
            ]
        ];

        $this->assertSame($expected, $policy->getPolicy()['bindings']);
    }

    public function test_it_adds_a_new_member_to_an_existing_role()
    {
        $payload = [
            'version' => 1,
            'etag' => "BwWk/fff4lc=",
            'bindings' => [
                [
                    'role' => 'roles/existingRole',
                    'members' => [
                        'old-service@account.com',
                    ],
                ]
            ]
        ];

        $policy = new IamPolicy($payload);

        $policy->addMemberToRole('new-service@account.com', 'roles/existingRole');

        $expected = [
            [
                'role' => 'roles/existingRole',
                'members' => [
                    'old-service@account.com',
                    'new-service@account.com',
                ],
            ]
        ];

        $this->assertSame($expected, $policy->getPolicy()['bindings']);
    }


    public function test_it_does_not_add_duplicate_members()
    {
        $payload = [
            'version' => 1,
            'etag' => "BwWk/fff4lc=",
            'bindings' => [
                [
                    'role' => 'roles/existingRole',
                    'members' => [
                        'old-service@account.com',
                    ],
                ]
            ]
        ];

        $policy = new IamPolicy($payload);

        $policy->addMemberToRole('old-service@account.com', 'roles/existingRole');

        $expected = [
            [
                'role' => 'roles/existingRole',
                'members' => [
                    'old-service@account.com',
                ],
            ]
        ];

        $this->assertSame($expected, $policy->getPolicy()['bindings']);
    }
}
