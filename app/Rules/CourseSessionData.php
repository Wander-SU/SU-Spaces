<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class CourseSessionData implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        //Only allow numbers, brackets, square brackets and spaces
        if(!preg_match('/^[a-zA-Z0-9\s\(\)\-\[\]]*$/',$value)){
            $fail("The attribute should only contain letters, numbers, spaces, brackets and square brackets");
        }
    }
}
