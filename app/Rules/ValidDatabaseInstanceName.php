<?php

namespace App\Rules;

use App\GoogleProject;
use Illuminate\Contracts\Validation\Rule;

class ValidDatabaseInstanceName implements Rule
{
    protected $googleProject;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(GoogleProject $googleProject)
    {
        $this->googleProject = $googleProject;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!$this->googleProject instanceof GoogleProject) {
            return false;
        }

        try {
            $instances = $this->googleProject->client()->getDatabaseInstances();
            return collect($instances['items'] ?? [])->every('name', '!=', $value);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'This database name is already taken within your selected project.';
    }
}
