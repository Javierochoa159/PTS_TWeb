<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidEmail implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(preg_match('/^[a-zA-Z0-9_.]+[@]{1}[a-z0-9]+[\.][a-z]+$/',$value) !== 1){
            $fail('Ingrese un Correo valido. Ej: a@b.c');
        }
    }
}
