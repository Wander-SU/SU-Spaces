<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class TwoNames implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if( str_word_count($value)!= 2){
            $fail('The attribute must only be two names.');
        }
        else if (preg_match('/^[a-zA-Z ]+$/',$value)==0){
            $fail('There should only be letters and spaces in the names');
        }
    }
}
