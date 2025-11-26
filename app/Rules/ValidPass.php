<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPass implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[#@|$%&¡!¿?_-])[A-Za-z\d#@|$%&¡!¿?_-]+$/',$value) !== 1){
            $fail('Formato de contraseña no valido.');
        }
    }
}
