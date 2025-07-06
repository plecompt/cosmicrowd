<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class StrongPassword implements Rule
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
        return strlen($value) >= 12 && // Min 12 length
               preg_match('/[A-Z]/', $value) && // At least one UpperCase char
               preg_match('/[a-z]/', $value) && // At least one lowerCase char
               preg_match('/[0-9]/', $value) && // At least one number
               preg_match('/[^A-Za-z0-9]/', $value); // At least one special char
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The password must contain at least 12 characters, one uppercase letter, one lowercase letter, one number and one special character.';
    }
} 