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

    'accepted' => 'The :attribute must be accepted.',
    'accepted_if' => 'The :attribute must be accepted when :other is :value.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after' => 'The :attribute must be a date after :date.',
    'after_or_equal' => 'The :attribute must be a date after or equal to :date.',
    'alpha' => 'The :attribute must only contain letters.',
    'alpha_dash' => 'The :attribute must only contain letters, numbers, dashes and underscores.',
    'alpha_num' => 'The :attribute must only contain letters and numbers.',
    'array' => 'The :attribute must be an array.',
    'before' => 'The :attribute must be a date before :date.',
    'before_or_equal' => 'The :attribute must be a date before or equal to :date.',
    'between' => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file' => 'The :attribute must be between :min and :max kilobytes.',
        'string' => 'The :attribute must be between :min and :max characters.',
        'array' => 'The :attribute must have between :min and :max items.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'current_password' => 'The password is incorrect.',
    'date' => 'The :attribute is not a valid date.',
    'date_equals' => 'The :attribute must be a date equal to :date.',
    'date_format' => 'The :attribute does not match the format :format.',
    'declined' => 'The :attribute must be declined.',
    'declined_if' => 'The :attribute must be declined when :other is :value.',
    'different' => 'The :attribute and :other must be different.',
    'digits' => 'The :attribute must be :digits digits.',
    'digits_between' => 'The :attribute must be between :min and :max digits.',
    'dimensions' => 'The :attribute has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'email' => 'The :attribute must be a valid email address.',
    'ends_with' => 'The :attribute must end with one of the following: :values.',
    'enum' => 'The selected :attribute is invalid.',
    'exists' => 'The selected :attribute is invalid.',
    'file' => 'The :attribute must be a file.',
    'filled' => 'The :attribute field must have a value.',
    'gt' => [
        'numeric' => 'The :attribute must be greater than :value.',
        'file' => 'The :attribute must be greater than :value kilobytes.',
        'string' => 'The :attribute must be greater than :value characters.',
        'array' => 'The :attribute must have more than :value items.',
    ],
    'gte' => [
        'numeric' => 'The :attribute must be greater than or equal to :value.',
        'file' => 'The :attribute must be greater than or equal to :value kilobytes.',
        'string' => 'The :attribute must be greater than or equal to :value characters.',
        'array' => 'The :attribute must have :value items or more.',
    ],
    'image' => 'The :attribute must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'in_array' => 'The :attribute field does not exist in :other.',
    'integer' => 'The :attribute must be an integer.',
    'ip' => 'The :attribute must be a valid IP address.',
    'ipv4' => 'The :attribute must be a valid IPv4 address.',
    'ipv6' => 'The :attribute must be a valid IPv6 address.',
    'json' => 'The :attribute must be a valid JSON string.',
    'lt' => [
        'numeric' => 'The :attribute must be less than :value.',
        'file' => 'The :attribute must be less than :value kilobytes.',
        'string' => 'The :attribute must be less than :value characters.',
        'array' => 'The :attribute must have less than :value items.',
    ],
    'lte' => [
        'numeric' => 'The :attribute must be less than or equal to :value.',
        'file' => 'The :attribute must be less than or equal to :value kilobytes.',
        'string' => 'The :attribute must be less than or equal to :value characters.',
        'array' => 'The :attribute must not have more than :value items.',
    ],
    'mac_address' => 'The :attribute must be a valid MAC address.',
    'max' => [
        'numeric' => 'The :attribute must not be greater than :max.',
        'file' => 'The :attribute must not be greater than :max kilobytes.',
        'string' => 'The :attribute must not be greater than :max characters.',
        'array' => 'The :attribute must not have more than :max items.',
    ],
    'mimes' => 'The :attribute must be a file of type: :values.',
    'mimetypes' => 'The :attribute must be a file of type: :values.',
    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
        'file' => 'The :attribute must be at least :min kilobytes.',
        'string' => 'The :attribute must be at least :min characters.',
        'array' => 'The :attribute must have at least :min items.',
    ],
    'multiple_of' => 'The :attribute must be a multiple of :value.',
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute format is invalid.',
    'numeric' => 'The :attribute must be a number.',
    'password' => 'The password is incorrect.',
    'present' => 'The :attribute field must be present.',
    'prohibited' => 'The :attribute field is prohibited.',
    'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
    'prohibits' => 'The :attribute field prohibits :other from being present.',
    'regex' => 'The :attribute format is invalid.',
    'required' => 'The :attribute field is required.',
    'required_array_keys' => 'The :attribute field must contain entries for: :values.',
    'required_if' => 'The :attribute field is required when :other is :value.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'The :attribute and :other must match.',
    'size' => [
        'numeric' => 'The :attribute must be :size.',
        'file' => 'The :attribute must be :size kilobytes.',
        'string' => 'The :attribute must be :size characters.',
        'array' => 'The :attribute must contain :size items.',
    ],
    'starts_with' => 'The :attribute must start with one of the following: :values.',
    'string' => 'The :attribute must be a string.',
    'timezone' => 'The :attribute must be a valid timezone.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The :attribute failed to upload.',
    'url' => 'The :attribute must be a valid URL.',
    'uuid' => 'The :attribute must be a valid UUID.',

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
        'polis_start' => [
            'required' => 'Не вказано дату початку дії поліса',
            'date' => 'Неправильний формат дати початку дії поліса',
            'after' => 'Неправильна дата початку дії поліса',
        ],

        'polis_end' => [
            'required_if' => 'Не вказано дату закінчення дії поліса',
            'date' => 'Неправильний формат дати закінчення дії поліса',
            'after' => 'Неправильна дата закінчення дії поліса',
        ],
        'vzr_range_day_id' => [
            'required_if' => 'Не вибрано період дії поліса',
            'exists' => 'Вибраний період дії поліса не існує',
        ],
        'multiple_trip' => [
            'required' => 'Не вказано значення для "Багаторазових поїздок"',
            'boolean' => 'Вказано неприпустиме значення для "Багаторазових поїздок"',
        ],
        'territories' => [
            'required' => 'Не вибрано територію дії поліса',
            'array' => 'Неправильний формат даних для значення "територія дії поліса"',
        ],
        'territories.*' => [
            'required' => 'Не вибрано територію дії поліса',
            'in' => 'Вибрана теориторія дії поліса не існує',
        ],
        'insured_sum' => [
            'required' => 'Не вибрано страхову суму',
            'in' => 'Вибрана страхова сума не існує',
        ],
        'tariff' => [
            'required' => 'Не вибрано тариф',
            'exists' => 'Вибраний тариф не існує',
            'in' => 'Вибраний тариф не існує',
        ],
        'tourists' => [
            'required' => 'Не вказано туристів',
            'array' => 'Неправильний формат даних для туристів',
        ],
        'tourists.*.birth.required' => 'Не вказано дату народження',
        'tourists.*.birth.date' => 'Неправильний формат дати народження',
        'tourists.*.birth.before' => 'Дата народження повинна бути не пізніше ніж за попередній день.',
        'tourists.*.surname.required' => 'Не вказано прізвище',
        'tourists.*.name.required' => 'Не вказано ім\'я',
         'tourists.*.doc_series.required' => 'Не вказано серію документа',
         'tourists.*.doc_number.required' => 'Не вказано номер документа',
         'tourists.*.doc_number.min' => 'Неправильний формат номера документа',
         'tourists.*.doc_type.required' => 'Не вказано тип документа',
         'tourists.*.doc_type.min' => 'Неправильний формат типу документа',
         'tourists.*.doc_type.max' => 'Неправильний формат типу документа',
         'tourists.*.goal.required' => 'Не вказана мета подорожі',
         'tourists.*.goal.in' => 'Вибраної мети поїздки не існує',
         'email' => [
             'required' => 'Не вказано E-mail',
             'email' => 'Неправильний формат E-mail',
         ],
         'insurant.phone' => [
             'required' => 'Не вказано телефон',
             'required_if' => 'Не вказано телефон',
             'min' => 'Неправильний формат номера',
         ],
         'insurant.inn' => [
             'required' => 'Не вказано ІПН',
             'required_if' => 'Не вказано ІПН',
         ],
         'insurant.surname' => [
             'required' => 'Не вказано прізвище',
             'required_if' => 'Не вказано прізвище',
         ],
         'insurant.name' => [
             'required' => 'Не вказано ім\'я',
             'required_if' => 'Не вказано ім\'я',
         ],
         'insurant.patronymic' => [
             'required' => 'Не вказано по батькові',
             'required_if' => 'Не вказано по батькові',
         ],
         'insurant.birth' => [
             'required' => 'Не вказано дату народження',
             'required_if' => 'Не вказано дату народження',
             'date' => 'Неправильний формат дати народження',
             'before' => 'Дата народження повинна бути не пізніше ніж за попередній день.',
             'before_or_equal' => 'Страхувальник повинен досягти віку 18 років',
         ],
         'insurant.address' => [
             'required' => 'Не вказана адреса',
             'required_if' => 'Не вказана адреса',
         ],
         'insurant.doc_type' => [
             'required' => 'Не вказано тип документа',
             'required_if' => 'Не вказано тип документа',
             'integer' => 'Неправильний формат типу документа',
             'min' => 'Неправильний формат типу документа',
             'max' => 'Неправильний формат типу документа',
         ],
         'insurant.doc_number' => [
             'required' => 'Не вказано номер документа',
             'required_if' => 'Не вказано номер документа',
         ],
         'insurant.doc_series' => [
             'required' => 'Не вказано серію документа',
             'required_if' => 'Не вказано серію документа',
         ],
         'insurant.doc_date' => [
             'required' => 'Не вказано дату видачі документа',
             'required_if' => 'Не вказано дату видачі документа',
             'date' => 'Неправильний формат дати видачі документа',
             'before' => 'Неправильна дата видачі документа',
             'before_or_equal' => 'Неправильна дата видачі документа',
         ],
         'insurant.doc_given' => [
             'required' => 'Не вказано орган видачі документа',
             'required_if' => 'Не вказано орган видачі документа',
         ],

         'transport.transport_category_id' => [
             'required' => 'Не вказано тип ТЗ',
             'exists' => 'Такий тип ТЗ не існує, зверніться до операторів',
         ],
         'transport.transport_power_id' => [
             'required' => 'Не вказано потужність ТС',
             'exists' => 'Така потужність ТЗ не існує',
         ],
         'transport.car_mark_id' => [
             'required' => 'Не вказано марку ТС',
             'exists' => 'Така марка не існує, зверніться до операторів',
         ],
         'transport.car_model_id' => [
             'required' => 'Не вказано модель ТЗ',
             'exists' => 'Така модель не існує, зверніться до операторів',
         ],
         'transport.car_mark' => [
             'required' => 'Не вказано марку ТС',
         ],
         'transport.car_model' => [
             'required' => 'Не вказано модель ТЗ',
         ],
         'transport.car_mark_code' => [
             'exists' => 'Така марка не існує, зверніться до операторів',
         ],
         'transport.car_model_code' => [
             'exists' => 'Така модель не існує, зверніться до операторів',
         ],
         'transport.gov_num' => [
             'required' => 'Не вказано держ. номер ТС',
             'required_if' => 'Не вказано держ. номер ТС',
             'min' => 'Неправильний формат держ. номери',
         ],
         'transport.vin' => [
             'required' => 'Не вказано VIN номер ТЗ',
             'required_if' => 'Не вказано VIN номер ТЗ',
             'vin' => 'Неправильний формат VIN номера',
         ],
         'transport.car_year' => [
             'required' => 'Не вказано рік випуску ТЗ',
             'required_if' => 'Не вказано рік випуску ТЗ',
             'digits' => 'Вкажіть рік випуску ТС у правильному форматі',
             'min' => 'Мінімальний рік випуску ТС - :min',
             'max' => 'Рік випуску ТЗ не може бути більшим, ніж :max',
         ],
         'city_id' => [
             'required' => 'Не вказано місто',
             'exists' => 'Таке місто не знайдено',
         ],
         'city_name' => [
             'required' => 'Не вказано місто',
             'required_if' => 'Не вказано місто',
         ],
         'trip_duration' => [
             'required' => 'Не вказано тривалість поїздки',
             'numeric' => 'Неправильний формат, значення має бути числом',
             'min' => 'Вказаний неіснуючий варіант тривалості поїздки',
             'max' => 'Вказаний неіснуючий варіант тривалості поїздки',
         ],
         'trip_country' => [
             'required' => 'Не вказано країни подорожі',
             'in' => 'Вибрані країни не знайдені',
         ],
         'dgo_limit' => [
             'integer' => 'Неправильний формат, значення має бути цілим числом',
         ],
         'franchise' => [
             'required' => 'Не обрано франшизу',
             'in' => 'Обрана франшиза не існує',
         ],
         'files' => [
             'required_if' => 'Не вибрані файли документів',
         ],
         'files.*' => [
             'mimes' => 'Такий формат файлу не підтримується',
         ],

         'phone' => [
             'required' => 'Не вказано телефон',
             'required_if' => 'Не вказано телефон',
             'min' => 'Неправильний формат номера',
             'digits_between' => 'Неправильний формат номера',
         ],
         'code' => [
             'required' => 'Не вказано код',
             'digits' => 'Неправильний формат коду',
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
