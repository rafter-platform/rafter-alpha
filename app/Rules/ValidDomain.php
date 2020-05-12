<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

class ValidDomain implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return Str::of($value)->contains('.')
            && !Str::of($value)->contains('/')
            && !Str::of($value)->contains(':')
            && !Str::of($value)->startsWith(['.', '-'])
            && !Str::of($value)->endsWith(['.', '-'])
            && strtolower($value) == $value;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "Provide a standard domain or subdomain. Services can not be mapped to paths and should not contain any trailing slashes. "
            . "Only lowercase alphanumeric characters, in addition to '.' and '-', are allowed.";
    }
}
