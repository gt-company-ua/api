<?php

namespace App\Http\Requests\Osago;

use App\Traits\RequestFailedValidationResponse;
use Illuminate\Foundation\Http\FormRequest;

class SalamandraCalculateRequest extends FormRequest
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
            //'transport.transport_category_id' => 'required|exists:App\Models\TransportCategory,id',
            'transport.transport_power_id' => 'required|exists:App\Models\TransportPower,id',

            'city_id' => 'required|exists:App\Models\City,id',
            'is_pu' => 'nullable|boolean',
            'is_dms' => 'nullable|boolean',
            'dgo_limit' => 'nullable|integer',

            'franchise' => 'required|integer',
            'promocode' => 'nullable|string',
        ];
    }
}
