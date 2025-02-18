<?php

namespace App\Http\Requests\v2;

use App\Models\Order;
use App\Rules\Boolean;
use App\Services\api\Ingo;
use App\Traits\RequestFailedValidationResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OsagoCalculateRequest extends FormRequest
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
            'trip_duration' => 'required|numeric|min:0|max:12',
            'discount_check' => ['required', new Boolean],
            'discount_type' => 'nullable|required_if:discount_check,1|integer|min:1|max:4',

            'transport.transport_power_id' => 'required|exists:App\Models\TransportPower,id',
            'transport.car_year' => 'required|digits:4|integer|min:1970|max:' . date('Y'),
            'transport.otk_date' => 'nullable|date|before_or_equal:today',

            'city_id' => 'nullable|exists:App\Models\OsagoCity,id',
            'dgo_limit' => 'nullable|integer',

            'franchise' => ['required', Rule::in(Ingo::OSAGO_FRANCHISES)],
            'use_as_taxi' => ['nullable', new Boolean],
            'foreign_check' => ['nullable', new Boolean],

            'use_scoring' => 'boolean',

            'insurant.birth' => 'required_if:use_scoring,true|date|before_or_equal:18 years ago',
            'insurant.type' => ['required', Rule::in(Order::INSURANT_TYPES)],

            'promocode' => 'nullable|string',
        ];
    }
}
