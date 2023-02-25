<?php

namespace App\Http\Requests;

use App\Models\Order;
use App\Rules\Boolean;
use App\Rules\Inn;
use App\Traits\RequestFailedValidationResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VzrSaveRequest extends FormRequest
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
            'polis_start' => 'required|date|after:today',
            'multiple_trip' => 'required|boolean',
            'epolis' => 'required|boolean',
            //'with_covid' => 'required|boolean',
            'with_greencard' => 'required|boolean',
            'dont_call' => ['nullable', new Boolean],
            'polis_end' => 'required_if:multiple_trip,false|date|after:polis_start',
            'vzr_range_day_id' => 'nullable|required_if:multiple_trip,true|exists:App\Models\VzrRangeDay,id',
            'territory' => ['required', Rule::in(Order::TERRITORIES)],
            'sport' => ['required', Rule::in(Order::SPORTS)],
            'target' => ['required', Rule::in(Order::TARGETS)],
            'insured_sum' => ['required', Rule::in(Order::VZR_INSURED_SUMS)],
            'tourists' => 'required|array',
            'tourists.*.birth' => 'required|date|before:today',
            'tourists.*.full_name' => 'required|string|min:3',
            'tourists.*.doc_number' => 'required|string|min:3',

            'email' => 'required|email',
            'insurant.phone' => 'required|string|min:6',
            'insurant.inn' => ['required', new Inn],
            'insurant.surname' => 'nullable|string',
            'insurant.name' => 'nullable|string',
            'promocode' => 'nullable|string',

            'ga_id' => 'nullable'
        ];
    }
}
