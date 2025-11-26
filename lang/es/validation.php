<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'El campo :attribute debe aceptarse.',
    'accepted_if' => 'El campo :attribute debe aceptarse cuando :other es :value.',
    'active_url' => 'El campo :attribute debe ser una URL válida.',
    'after' => 'El campo :attribute debe ser una fecha posterior a :date.',
    'after_or_equal' => 'El campo :attribute debe ser una fecha posterior o igual a :date.',
    'alpha' => 'El campo :attribute solo debe contener letras.',
    'alpha_dash' => 'El campo :attribute solo debe contener letras, números, guiones y guiones bajos.',
    'alpha_num' => 'El campo :attribute solo debe contener letras y números.',
    'any_of' => 'El campo :attribute no es válido.',
    'array' => 'El campo :attribute debe ser un array.',
    'ascii' => 'El campo :attribute solo debe contener caracteres alfanuméricos y símbolos de un solo byte.',
    'before' => 'El campo :attribute debe ser una fecha anterior a :date.',
    'before_or_equal' => 'El campo :attribute debe ser una fecha anterior o igual a :date.',
    'between' => [
        'array' => ':attribute debe tener entre :min y :max elementos.',
        'file' => ':attribute debe tener entre :min y :max kilobytes.',
        'numeric' => ':attribute debe estar entre :min y :max.',
        'string' => ':attribute debe tener entre :min y :max caracteres.',
    ],
    'boolean' => ':attribute debe ser verdadero o falso.',
    'can' => ':attribute contiene un valor no válido.',
    'confirmed' => 'La confirmación del campo :attribute no coincide.',
    'contains' => 'Al campo :attribute le falta un valor obligatorio.',
    'current_password' => 'La contraseña es incorrecta.',
    'date' => ':attribute debe ser una fecha válida.',
    'date_equals' => ':attribute debe ser una fecha igual a :date.',
    'date_format' => ':attribute debe coincidir con el formato :format.',
    'decimal' => ':attribute debe tener :decimal decimales.',
    'declined' => ':attribute debe ser rechazado.',
    'declined_if' => ':attribute debe rechazarse cuando :other es :value.',
    'different' => ':attribute y :other deben ser diferentes.',
    'digits' => ':attribute debe tener :digits dígitos.',
    'digits_between' => ':attribute debe tener entre :min y :max dígitos.',
    'dimensions' => ':attribute tiene dimensiones de imagen no válidas.',
    'distinct' => ':attribute tiene un valor duplicado.',
    'doesnt_contain' => ':attribute no debe contener ninguno de los siguientes: :values.',
    'doesnt_end_with' => ':attribute no debe terminar con uno de los siguientes: :values.',
    'doesnt_start_with' => ':attribute no debe comenzar con uno de los siguientes: :values.',
    'email' => ':attribute debe ser una dirección de correo electrónico válida.',
    'ends_with' => ':attribute debe terminar con uno de los siguientes: :values.',
    'enum' => ':attribute seleccionado no válido.',
    'exists' => ':attribute seleccionado no válido.',
    'extensions' => ':attribute debe tener una de las siguientes extensiones: :values.',
    'file' => ':attribute debe ser un archivo.',
    'filled' => ':attribute debe tener un valor.',
    'gt' => [
        'array' => ':attribute debe tener más de :value elementos.',
        'file' => ':attribute debe tener más de :value kilobytes.',
        'numeric' => ':attribute debe ser mayor que :value.',
        'string' => ':attribute debe tener más de :value caracteres.',
    ],
    'gte' => [
        'array' => ':attribute debe tener :value elementos o más.',
        'file' => ':attribute debe ser mayor o igual a :value kilobytes.',
        'numeric' => ':attribute debe ser mayor o igual a :value.',
        'string' => ':attribute debe ser mayor o igual a :value caracteres.',
    ],
    'hex_color' => ':attribute debe ser un color hexadecimal válido.',
    'image' => ':attribute debe ser una imagen.',
    'in' => ':attribute seleccionado no válido.',
    'in_array' => ':attribute debe existir en :other.',
    'in_array_keys' => ':attribute debe contener al menos una de las siguientes claves: :values.',
    'integer' => ':attribute debe ser un entero.',
    'ip' => ':attribute debe ser una dirección IP válida.',
    'ipv4' => ':attribute debe ser una dirección IPv4 válida.',
    'ipv6' => ':attribute debe ser una dirección IPv6 válida.',
    'json' => ':attribute debe ser una cadena JSON válida.',
    'list' => ':attribute debe ser una lista.',
    'lowercase' => ':attribute debe estar en minúsculas.',
    'lt' => [
        'array' => ':attribute debe tener menos de :value elementos.',
        'file' => ':attribute debe tener menos de :value kilobytes.',
        'numeric' => ':attribute debe tener menos de :value.',
        'string' => ':attribute debe tener menos de :value caracteres.',
    ],
    'lte' => [
        'array' => ':attribute no debe tener más de :value elementos.',
        'file' => ':attribute debe tener menos o igual a :value kilobytes.',
        'numeric' => ':attribute debe ser menor o igual a :value.',
        'string' => ':attribute debe tener menos o igual a :value caracteres.',
    ],
    'mac_address' => ':attribute debe ser una dirección MAC válida.',
    'max' => [
        'array' => ':attribute no debe tener más de :max elementos.',
        'file' => ':attribute no debe tener más de :max kilobytes.',
        'numeric' => ':attribute no debe ser mayor a :max',
        'string' => ':attribute no debe tener más de :max caracteres.',
    ],
    'max_digits' => ':attribute no debe tener más de :max dígitos.',
    'mimes' => ':attribute debe ser un archivo de tipo: :values.',
    'mimetypes' => ':attribute debe ser un archivo de tipo: :values.',
    'min' => [
        'array' => ':attribute debe tener al menos :min elementos.',
        'file' => ':attribute debe tener al menos :min kilobytes.',
        'numeric' => ':attribute no debe ser menor a :min',
        'string' => ':attribute debe tener al menos :min caracteres.',
    ],
    'min_digits' => ':attribute debe tener al menos :min dígitos.',
    'missing' => 'El campo :attribute debe faltar.',
    'missing_if' => 'El campo :attribute debe faltar cuando :other es :value.',
    'missing_unless' => 'El campo :attribute debe faltar a menos que :other sea :value.',
    'missing_with' => 'El campo :attribute debe faltar cuando :values está presente.',
    'missing_with_all' => 'El campo :attribute debe faltar cuando :values está presente.',
    'multiple_of' => ':attribute debe ser un múltiplo de :value.',
    'not_in' => ':attribute seleccionado no válido.',
    'not_regex' => 'El formato del campo :attribute no es válido.',
    'numeric' => ':attribute debe ser un número.',
    'password' => [
        'letters' => ':attribute debe contener al menos una letra.',
        'mixed' => ':attribute debe contener al menos una letra mayúscula y una minúscula.',
        'numbers' => ':attribute debe contener al menos un número.',
        'symbols' => ':attribute debe contener al menos un símbolo.',
        'uncompromised' => 'Su :attribute ha aparecido en una fuga de datos. Cambie su :attribute.',
    ],
    'present' => 'El campo :attribute debe estar presente.',
    'present_if' => 'El campo :attribute debe estar presente cuando :other es :value.',
    'present_unless' => 'El campo :attribute debe estar presente a menos que :other sea :value.',
    'present_with' => 'El campo :attribute debe estar presente cuando :values está presente.',
    'present_with_all' => 'El campo :attribute debe estar presente cuando :values está presente.',
    'prohibited' => 'El campo :attribute está prohibido.',
    'prohibited_if' => 'El campo :attribute está prohibido cuando :other es :value.',
    'prohibited_if_accepted' => 'El campo :attribute está prohibido cuando :other es aceptado.',
    'prohibited_if_declined' => 'El campo :attribute está prohibido cuando se rechaza :other.',
    'prohibited_unless' => 'El campo :attribute está prohibido a menos que :other esté en :values.',
    'prohibits' => 'El campo :attribute impide que :other esté presente.',
    'regex' => 'El formato del campo :attribute no es válido.',
    'required' => 'El campo :attribute es obligatorio.',
    'required_array_keys' => 'El campo :attribute debe contener entradas para :values.',
    'required_if' => 'El campo :attribute es obligatorio cuando :other es :value.',
    'required_if_accepted' => 'El campo :attribute es obligatorio cuando se acepta :other.',
    'required_if_declined' => 'El campo :attribute es obligatorio cuando se rechaza :other.',
    'required_unless' => 'El campo :attribute es obligatorio a menos que :other esté en :values.',
    'required_with' => 'El campo :attribute es obligatorio cuando :values está presente.',
    'required_with_all' => 'El campo :attribute es obligatorio cuando :values está presente.',
    'required_without' => 'El campo :attribute es obligatorio cuando :values no está presente.',
    'required_without_all' => 'El campo :attribute es obligatorio cuando ninguno de los :values está presente.',
    'same' => ':attribute debe coincidir con :other.',
    'size' => [
        'array' => ':attribute debe contener elementos :size.',
        'file' => ':attribute debe tener :size kilobytes.',
        'numeric' => ':attribute debe ser :size.',
        'string' => ':attribute debe tener :size caracteres.',
    ],
    'starts_with' => ':attribute debe comenzar con uno de los siguientes: :values.',
    'string' => ':attribute debe ser una palabra.',
    'timezone' => ':attributed debe ser una zona horaria válida.',
    'unique' => ':attribute ya está en uso.',
    'uploaded' => ':attribute no se pudo cargar',
    'uppercase' => ':attribute debe estar en mayúsculas.',
    'url' => ':attribute debe ser una URL válida.',
    'ulid' => ':attribute debe ser un ULID válido.',
    'uuid' => ':attribute debe ser un UUID válido.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
