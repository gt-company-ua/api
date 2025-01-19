<?php

namespace App\Http\Requests\v2;

use App\Models\Order;
use App\Rules\Boolean;
use App\Rules\Inn;
use App\Traits\RequestFailedValidationResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GreenCardDraftRequest extends FormRequest
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
            'insurance_company' => ['nullable', Rule::in(Order::INSURANCE_COMPANIES)],
            'trip_country' => ['nullable', Rule::in(Order::TRIP_COUNTRIES)],
            'trip_duration' => 'nullable|numeric|min:0|max:12',
            'email' => 'nullable|email',
            'polis_start' => 'nullable|date',
            'comment' => 'nullable|string',
            'promocode' => 'nullable|string',
            'cashback_phone' => 'nullable|string',
            'cashback_card' => 'nullable|string',
            'cashback_to_vsu' => ['nullable', new Boolean],
            'dont_call' => ['nullable', new Boolean],

            'upload_docs' => ['nullable', new Boolean],
            'files' => 'array',
            'files.*' => 'mimes:jpg,jpeg,png,bmp,pdf,zip,rar,7z,heic,heif,hevc,hevf',

            'transport.transport_category_id' => 'nullable|exists:App\Models\TransportCategory,id',
            'transport.car_mark' => 'nullable|string',
            'transport.car_model' => 'nullable|string',
            'transport.gov_num' => 'nullable|string|min:6',
            'transport.vin' => 'nullable|string|min:6',
            'transport.car_year' => 'nullable',

            'city_name' => 'nullable|string|min:1',
            'insurant.phone' => 'nullable|string|min:6',
            'insurant.surname' => 'nullable|string',
            'insurant.name' => 'nullable|string',
            'insurant.surname_latin' => 'nullable|string',
            'insurant.name_latin' => 'nullable|string',

            'insurant.inn' => ['nullable', new Inn],
            'insurant.birth' => 'nullable|date',
            'insurant.doc_number' => 'nullable|string',
            'insurant.doc_series' => 'nullable|string',

            'ga_id' => 'nullable',
            'with_assist_me' => ['nullable', new Boolean],
            'uuid' => 'exists:App\Models\Order,uuid'
        ];
    }
}
