<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class PasswordPolicy implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /**
         * Contain the rules to fail
         * Length should be longer than 8 characters
         * Cannot contain spaces
         * Should not be something simple like password or qwertyuiop
        */
        if(strlen(trim($value))<8){
            $fail("Password must have a minimum of 8 valid characters");
        }
        if(preg_match('/\s/',$value)){
            $fail("Password should not contain spaces");
        }
        if(preg_match('/password/i',$value)||preg_match('/qwertyuiop/i',$value)){
            $fail("Password should not be very simple to guess");
        }
        if(!preg_match('/[a-zA-Z0-9]/',$value)){
            $fail("Make sure you have at least 1 alphanumeric character in the password");
        }
        if(!preg_match('/[^a-zA-Z0-9]/',$value)){
            $fail("Make sure you have at least 1 special character in the password");
        }
    }
}
