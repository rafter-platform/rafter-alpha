<?php

namespace App\GoogleCloud;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ProjectIamPolicy
{
    protected $policy;

    public function __construct($policy)
    {
        $this->policy = $policy;
    }

    /**
     * Get all bindings from the plicy.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getBindings(): Collection
    {
        return collect($this->policy['bindings'] ?? []);
    }

    /**
     * Get a specific binding
     *
     * @param string $role
     * @return array|null
     */
    protected function getBinding($role)
    {
        return $this->getBindings()
            ->firstWhere('role', $role);
    }

    /**
     * Add a member to a given role.
     *
     * @param string $member The member, e.g. service account email.
     * @param string $role The role, starting with `roles/`
     * @return self
     */
    public function addMemberToRole($member, $role)
    {
        $bindings = $this->getBindings();
        $existing = $this->getBinding($role);

        if ($existing) {
            array_push($existing['members'], $member);
            $existing['members'] = array_unique($existing['members']);

            $this->policy['bindings'] = $bindings->where('role', '!=', $role)
                ->push($existing)
                ->toArray();

            return $this;
        }

        $bindings->push([
            'role' => $role,
            'members' => [
                $member,
            ],
        ]);

        $this->policy['bindings'] = $bindings->toArray();

        return $this;
    }

    /**
     * Get the policy
     *
     * @return array
     */
    public function getPolicy()
    {
        return $this->policy;
    }
}
