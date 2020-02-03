<?php

namespace App\GoogleCloud;

class CloudRunIamPolicy
{
    protected $policy;

    public function __construct($policy) {
        $this->policy = $policy;
    }

    /**
     * Whether the Cloud Run policy is public.
     *
     * @return boolean
     */
    public function isPublic()
    {
        $binding = $this->getBinding('roles/run.invoker');

        if (! $binding) {
            return false;
        }

        return collect($binding['members'])->contains('allUsers');
    }

    /**
     * Get all bindings from the plicy.
     *
     * @return array
     */
    protected function getBindings()
    {
        return $this->policy['bindings'];
    }

    /**
     * Get a specific binding
     *
     * @param string $role
     * @return array|null
     */
    protected function getBinding($role)
    {
        return collect($this->getBindings())
            ->firstWhere('role', $role);
    }

    /**
     * Make this policy public
     *
     * @return self
     */
    public function setPublic()
    {
        $this->policy['bindings'][] = [
            'role' => 'roles/run.invoker',
            'members' => [
                'allUsers',
            ],
        ];

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
