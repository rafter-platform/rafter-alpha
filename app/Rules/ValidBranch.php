<?php

namespace App\Rules;

use App\SourceProvider;
use Illuminate\Contracts\Validation\Rule;

class ValidBranch implements Rule
{
    protected $source;
    protected $repository;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($source, $repository)
    {
        $this->source = $source;
        $this->repository = $repository;
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
        if (!$this->source instanceof SourceProvider) {
            return false;
        }

        return $this->source->client()->validRepository(
            $this->repository,
            $value
        );
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The given branch is not valid.';
    }
}
