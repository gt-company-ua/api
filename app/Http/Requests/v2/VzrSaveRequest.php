<?php

namespace App\Http\Requests\v2;

use App\Models\Order;
use App\Rules\Boolean;
use App\Rules\Inn;
use App\Services\api\Ingo;
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
            'dont_call' => ['nullable', new Boolean],
            'polis_end' => 'required_if:multiple_trip,false|date|after:polis_start',
            'vzr_range_day_id' => 'nullable|required_if:multiple_trip,true|exists:App\Models\VzrRangeDay,id',
            'territories' => 'required|array',
            'territories.*' => ['required', Rule::in(Ingo::TERRITORIES_IDS)],
            'insured_sum' => ['required', Rule::in(Order::VZR_INSURED_SUMS)],
            'tariff' => ['required', Rule::in(Ingo::VZR_TARIFFS)],

            'tourists' => 'required|array',
            'tourists.*.birth' => 'required|date|before:today',
            'tourists.*.surname' => 'required|string|min:1',
            'tourists.*.name' => 'required|string|min:1',
            'tourists.*.doc_series' => 'required|string|min:1',
            'tourists.*.doc_number' => 'required|string|min:3',
            'tourists.*.doc_type' => 'required|integer|min:1|max:14',
            'tourists.*.goal' => ['required', Rule::in(Ingo::GOAL_IDS)],

            'email' => 'required|email',
            'insurant.phone' => 'required|string|min:6',
            'insurant.inn' => ['required', new Inn],
            'insurant.surname' => 'required|string',
            'insurant.name' => 'required|string',
            //'insurant.patronymic' => 'required|string',
            'insurant.birth' => 'required|date|before:today',
            'insurant.address' => 'required|string',
            'insurant.doc_type' => 'required|integer|min:1|max:14',
            'insurant.doc_number' => 'required|string',
            'insurant.doc_series' => 'required|string',

            'promocode' => 'nullable|string',

            'code' => 'nullable|string',
            'code_date_end' => 'nullable|date',

            'ga_id' => 'nullable',
            'uuid' => 'exists:App\Models\Order,uuid',

            'cashback_phone' => 'nullable|string',
            'cashback_card' => 'nullable|string',
            'cashback_to_vsu' => ['nullable', new Boolean],
            'is_abroad' => ['nullable', new Boolean],

            'utm_source'   => 'nullable|string|max:255',
            'utm_medium'   => 'nullable|string|max:255',
            'utm_campaign' => 'nullable|string|max:255',
            'utm_content'  => 'nullable|string|max:255',
            'utm_term'     => 'nullable|string|max:255',

        ];
    }
}
