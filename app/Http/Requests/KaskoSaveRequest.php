<?php

namespace App\Http\Requests;

use App\KaskoService;
use App\Rules\Boolean;
use App\Traits\RequestFailedValidationResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KaskoSaveRequest extends FormRequest
{
    use RequestFailedValidationResponse;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'insured_sum' => 'required|numeric|min:1',
            'is_truck' => 'boolean',
            'tariff' => 'required|exists:App\Models\KaskoTariff,alias',
            'email' => 'nullable|email',
            'dont_call' => ['nullable', new Boolean],
            'transport.car_mark' => 'required|string',
            'transport.car_model' => 'required|string',
            'transport.car_year' => 'required|digits:4|integer|min:' . (date('Y') - 18) . '|max:' . date('Y'),
            'insurant.phone' => 'required|string|min:6',
            'insurant.surname' => 'nullable|string',
            'insurant.name' => 'nullable|string',
            'insurant.patronymic' => 'nullable|string',
            'promocode' => 'nullable|string',

            'ga_id' => 'nullable'
        ];
    }
}
