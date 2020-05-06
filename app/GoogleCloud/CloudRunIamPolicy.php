<?php

namespace App\GoogleCloud;

class CloudRunIamPolicy extends IamPolicy
{
    /**
     * Whether the Cloud Run policy is public.
     *
     * @return boolean
     */
    public function isPublic()
    {
        $binding = $this->getBinding('roles/run.invoker');

        if (!$binding) {
            return false;
        }

        return collect($binding['members'])->contains('allUsers');
    }

    /**
     * Make this policy public
     *
     * @return self
     */
    public function setPublic()
    {
        return $this->addMemberToRole('allUsers', 'roles/run.invoker');
    }
}
