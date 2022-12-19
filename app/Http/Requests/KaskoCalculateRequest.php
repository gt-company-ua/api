<?php

namespace App\Http\Requests;

use App\KaskoService;
use App\Traits\RequestFailedValidationResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KaskoCalculateRequest extends FormRequest
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
            'transport.car_year' => 'required|digits:4|integer|min:' . (date('Y') - 18) . '|max:' . date('Y'),
            'promocode' => 'nullable|string',
        ];
    }
}
