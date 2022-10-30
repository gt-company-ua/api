<?php

namespace App\Http\Requests;

use App\Models\Order;
use App\Traits\RequestFailedValidationResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GreenCardCalculateRequest extends FormRequest
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

            'trip_country' => ['required', Rule::in(Order::TRIP_COUNTRIES)],
            'trip_duration' => 'required|numeric|min:0|max:12',
        ];
    }
}
