<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class DayOfWeek implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(!preg_match('/Monday/',trim($value))&&
        !preg_match('/Tuesday/',trim($value))&&
        !preg_match('/Wednesday/',trim($value))&&
        !preg_match('/Thursday/',trim($value))&&
        !preg_match('/Friday/',trim($value))&&
        !preg_match('/Saturday/',trim($value))){
            $fail('The attribute may only contain Days of the Week between Monday & Saturday');
        }
    }
}
