<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AlfaNunSpacePunct implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(preg_match('/^(?=(?:.*[A-Za-z]){3,})[ÁÄÉËÍÏÓÖÚÜáäéëíïóöúüñÑA-Za-z0-9 \s,;.:¡!¿?\'"@()<>#=_*\/+-]+$/',$value) !== 1){
            $fail(':attribute tiene que tener al menos 3 letras.
            Puede tener numetos y los siguientes caracteres especiales:
            , ; . : ¡ ! ¿ ? \' " @ () <> # = _ * / + -');
        }
    }
}
