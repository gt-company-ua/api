<?php

namespace App\Http\Requests;

use App\Models\Order;
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
            'transport.transport_category_id' => 'required|exists:App\Models\TransportCategory,id',
            'transport.transport_power_id' => 'required|exists:App\Models\TransportPower,id',

            'city_id' => 'required_without:city_name|numeric|min:1',
            'city_name' => 'nullable|string|min:1',
            'foreign_check' => 'required|boolean',
            'discount_check' => 'required|boolean',

            'insurant.type' => ['required', Rule::in(Order::INSURANT_TYPES)],
            'promocode' => 'nullable|string',
        ];
    }
}
