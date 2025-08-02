<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class StrongPassword implements Rule
{
    /**
     * Check if the given password meets strength requirements.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return strlen($value) >= 12 && // Min 12 characters
               preg_match('/[A-Z]/', $value) && // At least one uppercase letter
               preg_match('/[a-z]/', $value) && // At least one lowercase letter
               preg_match('/[0-9]/', $value) && // At least one digit
               preg_match('/[^A-Za-z0-9]/', $value); // At least one special character
    }

    /**
     * Return the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The password must contain at least 12 characters, one uppercase letter, one lowercase letter, one number and one special character.';
    }
}
