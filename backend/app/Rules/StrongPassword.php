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
        return strlen($value) >= 12 &&
               preg_match('/[A-Z]/', $value) && // Au moins une majuscule
               preg_match('/[a-z]/', $value) && // Au moins une minuscule
               preg_match('/[0-9]/', $value) && // Au moins un chiffre
               preg_match('/[^A-Za-z0-9]/', $value); // Au moins un caractère spécial
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