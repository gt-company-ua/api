<?php

namespace App\Http\Requests\v2;

use App\Models\Order;
use App\Services\VzrService;
use App\Traits\RequestFailedValidationResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VzrCalculateRequest extends FormRequest
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
            'vzr_range_day_id' => 'nullable|required_if:multiple_trip,true|exists:App\Models\VzrRangeDay,id',
            'polis_end' => 'required_if:multiple_trip,false|date|after:polis_start',
            'territories' => 'required|array',
            'territories.*' => ['required', Rule::in(VzrService::TERRITORIES_IDS)],
            'insured_sum' => ['required', Rule::in(Order::VZR_INSURED_SUMS)],
            'tourists' => 'nullable|array',
            'tourists.*.birth' => 'required|date|before:today',
            'promocode' => 'nullable|string',
            'ranges' => 'nullable|array',
            'ranges.*' => ['required', Rule::in(VzrService::AGE_RANGES)],
        ];
    }
}
