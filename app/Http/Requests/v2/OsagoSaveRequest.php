<?php

namespace App\Http\Requests\v2;

use App\Models\Order;
use App\Rules\Boolean;
use App\Rules\Inn;
use App\Services\api\Ingo;
use App\Services\api\TasIns;
use App\Traits\RequestFailedValidationResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OsagoSaveRequest extends FormRequest
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
            'insurance_company' => ['required', Rule::in(Order::INSURANCE_COMPANIES)],
            'city_id' => 'required|exists:App\Models\OsagoCity,id',
            'trip_duration' => 'required|numeric|min:0|max:12',
            'dgo_limit' => 'nullable|integer',
            'franchise' => ['required', Rule::in(Ingo::OSAGO_FRANCHISES)],
            'polis_start' => 'required|date|after:today',
            'email' => 'required|email',
            'upload_docs' => ['required', new Boolean],
            'files' => 'required_if:upload_docs,1|array',
            'files.*' => 'mimes:jpg,jpeg,png,bmp,pdf,zip,rar,7z,heic,heif,hevc,hevf',
            'comment' => 'nullable|string',
            'dont_call' => ['nullable', new Boolean],
            'discount_check' => ['required', new Boolean],
            'discount_type' => 'nullable|required_if:discount_check,1|integer|min:1|max:4',
            'use_as_taxi' => ['nullable', new Boolean],
            'foreign_check' => ['nullable', new Boolean],

            'transport.transport_category_id' => 'required|exists:App\Models\TransportCategory,id',
            'transport.transport_power_id' => 'required|exists:App\Models\TransportPower,id',
            'transport.car_mark_id' => 'nullable|exists:App\Models\CarMark,id',
            'transport.car_model_id' => 'nullable|exists:App\Models\CarModel,id',
            'transport.car_mark_code' => 'nullable|exists:App\Models\CarMark,external_id',
            'transport.car_model_code' => 'nullable|exists:App\Models\CarModel,external_id',
            'transport.car_mark' => 'nullable|string',
            'transport.car_model' => 'nullable|string',
            'transport.gov_num' => 'nullable|required_if:upload_docs,0|string|min:6',
            'transport.vin' => 'nullable|required_if:upload_docs,0|string|min:6',
            'transport.car_year' => 'nullable|required_if:upload_docs,0|digits:4|integer|min:1970|max:' . date('Y'),
            'transport.otk_date' => 'nullable|date|before_or_equal:today',
            'transport.engine_capacity' => 'nullable|integer',
            'transport.total_weight' => 'nullable|integer',
            'transport.own_weight' => 'nullable|integer',
            'transport.seats_count' => 'nullable|integer',
            'transport.odometer' => 'nullable|integer',
            'transport.e_power' => 'nullable|integer',

            'use_scoring' => ['nullable', new Boolean],

            'insurant.type' => ['required', Rule::in(Order::INSURANT_TYPES)],
            'insurant.phone' => 'nullable|required_if:upload_docs,0|string|min:6',
            'insurant.surname' => 'nullable|required_if:upload_docs,0|string',
            'insurant.name' => 'nullable|required_if:upload_docs,0|string',
            'insurant.patronymic' => 'nullable|nullable|string',
            'insurant.inn' => ['nullable', 'required_if:upload_docs,0', new Inn],
            'insurant.birth' => 'nullable|required_if:upload_docs,0|date|before_or_equal:18 years ago',
            'insurant.address' => 'nullable|required_if:upload_docs,0|string',
            'insurant.doc_type' => 'required|integer|min:1|max:14',
            'insurant.doc_number' => 'required_if:insurance_company,'.TasIns::API_NAME.'|string',
            'insurant.doc_series' => 'required_if:insurance_company,'.TasIns::API_NAME.'|string',
            'insurant.doc_given' => 'required_if:insurance_company,'.TasIns::API_NAME.'|string',
            'insurant.doc_date' => 'required_if:insurance_company,'.TasIns::API_NAME.'|date|before_or_equal:today',
            'insurant.doc_adv' => 'nullable|string',

            'insurant.discount_doc_number' => 'nullable|string',
            'insurant.discount_doc_series' => 'nullable|string',
            'insurant.discount_doc_given' => 'nullable|string',
            'insurant.discount_doc_date' => 'nullable|date|before_or_equal:today',

            'promocode' => 'nullable|string',

            'ga_id' => 'nullable',
            'uuid' => 'exists:App\Models\Order,uuid',

            'cashback_phone' => 'nullable|string',
            'cashback_card' => 'nullable|string',
            'cashback_to_vsu' => ['nullable', new Boolean],

            'utm_source'   => 'nullable|string|max:255',
            'utm_medium'   => 'nullable|string|max:255',
            'utm_campaign' => 'nullable|string|max:255',
            'utm_content'  => 'nullable|string|max:255',
            'utm_term'     => 'nullable|string|max:255',

        ];
    }
}
