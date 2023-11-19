<?php

namespace App\Http\Requests\Draft;

use App\Models\Order;
use App\Rules\Boolean;
use App\Rules\Inn;
use App\Services\api\Ingo;
use App\Traits\RequestFailedValidationResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VzrDraftRequest extends FormRequest
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
            'uuid' => 'exists:App\Models\Order,uuid',
            'polis_start' => 'nullable|date',
            'multiple_trip' => 'nullable|boolean',
            'dont_call' => ['nullable', new Boolean],
            'polis_end' => 'nullable|date',
            'vzr_range_day_id' => 'nullable|exists:App\Models\VzrRangeDay,id',
            'territories' => 'nullable|array',
            'territories.*' => ['nullable', Rule::in(Ingo::TERRITORIES_IDS)],
            'insured_sum' => ['nullable', Rule::in(Order::VZR_INSURED_SUMS)],
            'tariff' => ['nullable', Rule::in(Ingo::VZR_TARIFFS)],

            'tourists' => 'nullable|array',
            'tourists.*.birth' => 'nullable|date|before:today',
            'tourists.*.surname' => 'nullable|string|min:1',
            'tourists.*.name' => 'nullable|string|min:1',
            'tourists.*.doc_series' => 'nullable|string|min:1',
            'tourists.*.doc_number' => 'nullable|string|min:3',
            'tourists.*.doc_type' => 'nullable|integer|min:1|max:14',
            'tourists.*.goal' => ['nullable', Rule::in(Ingo::GOAL_IDS)],

            'email' => 'nullable|email',
            'insurant.phone' => 'nullable|string|min:6',
            'insurant.inn' => ['nullable', new Inn],
            'insurant.surname' => 'nullable|string',
            'insurant.name' => 'nullable|string',
            //'insurant.patronymic' => 'required|string',
            'insurant.birth' => 'nullable|date|before:today',
            'insurant.address' => 'nullable|string',
            'insurant.doc_type' => 'nullable|integer|min:1|max:14',
            'insurant.doc_number' => 'nullable|string',
            'insurant.doc_series' => 'nullable|string',

            'promocode' => 'nullable|string',

            'code' => 'nullable|string',
            'code_date_end' => 'nullable|date',

            'ga_id' => 'nullable'
        ];
    }
}
