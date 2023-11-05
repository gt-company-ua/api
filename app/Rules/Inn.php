<?php

namespace App\Rules;

use App\Services\OrderService;
use Illuminate\Contracts\Validation\Rule;

class Inn implements Rule
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
        $parseInn = (new OrderService(null))->parseInn($value);

        return $parseInn['status'];
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Неправильний ІПН';
    }
}
