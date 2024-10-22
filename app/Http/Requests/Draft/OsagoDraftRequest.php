<?php

namespace App\Http\Requests\Draft;

use App\Models\Order;
use App\Rules\Boolean;
use App\Rules\Inn;
use App\Services\api\Ingo;
use App\Traits\RequestFailedValidationResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OsagoDraftRequest extends FormRequest
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
            'city_id' => 'nullable|exists:App\Models\OsagoCity,id',
            'trip_duration' => 'nullable|numeric|min:0|max:12',
            'dgo_limit' => 'nullable|integer',
            'franchise' => ['nullable', Rule::in(Ingo::OSAGO_FRANCHISES)],
            'polis_start' => 'nullable|date',
            'email' => 'nullable|email',
            'upload_docs' => ['nullable', new Boolean],
            'files' => 'nullable|array',
            'files.*' => 'mimes:jpg,jpeg,png,bmp,pdf,zip,rar,7z,heic,heif,hevc,hevf',
            'comment' => 'nullable|string',
            'dont_call' => ['nullable', new Boolean],
            'discount_check' => ['nullable', new Boolean],
            'discount_type' => 'nullable|integer|min:1|max:4',
            'use_as_taxi' => ['nullable', new Boolean],

            'transport.transport_category_id' => 'nullable|exists:App\Models\TransportCategory,id',
            'transport.transport_power_id' => 'nullable|exists:App\Models\TransportPower,id',
            'transport.car_mark_id' => 'nullable|exists:App\Models\CarMark,id',
            'transport.car_model_id' => 'nullable|exists:App\Models\CarModel,id',
            'transport.car_mark' => 'nullable|string',
            'transport.car_model' => 'nullable|string',
            'transport.gov_num' => 'nullable|string|min:6',
            'transport.vin' => 'nullable|string|min:6',
            'transport.car_year' => 'nullable',
            'transport.otk_date' => 'nullable|date|before_or_equal:today',

            'insurant.type' => ['nullable', Rule::in(Order::INSURANT_TYPES)],
            'insurant.phone' => 'nullable|string|min:6',
            'insurant.surname' => 'nullable|string',
            'insurant.name' => 'nullable|string',
            'insurant.patronymic' => 'nullable|nullable|string',
            'insurant.inn' => ['nullable', new Inn],
            'insurant.birth' => 'nullable|date',
            'insurant.address' => 'nullable|string',
            'insurant.doc_type' => 'nullable|integer|min:1|max:14',
            'insurant.doc_number' => 'nullable|string',
            'insurant.doc_series' => 'nullable|string',
            'insurant.doc_given' => 'nullable|string',
            'insurant.doc_date' => 'nullable|date',
            'insurant.doc_adv' => 'nullable|string',

            'insurant.discount_doc_number' => 'nullable|string',
            'insurant.discount_doc_series' => 'nullable|string',
            'insurant.discount_doc_given' => 'nullable|string',
            'insurant.discount_doc_date' => 'nullable|date|before_or_equal:today',

            'promocode' => 'nullable|string',

            'ga_id' => 'nullable',
            'uuid' => 'exists:App\Models\Order,uuid'
        ];
    }
}
