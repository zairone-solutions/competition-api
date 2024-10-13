<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class IdentityRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        return str_contains($value, "@") ?  filter_var($value, FILTER_VALIDATE_EMAIL) : TRUE;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Please provide a valid email address.';
    }
}
