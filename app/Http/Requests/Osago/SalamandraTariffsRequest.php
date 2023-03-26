<?php

namespace App\Http\Requests\Osago;

use App\Traits\RequestFailedValidationResponse;
use Illuminate\Foundation\Http\FormRequest;

class SalamandraTariffsRequest extends FormRequest
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
            'transport.transport_power_id' => 'required|exists:App\Models\TransportPower,id',
            'city_id' => 'required|exists:App\Models\City,id',
            'promocode' => 'nullable|string',
        ];
    }
}
