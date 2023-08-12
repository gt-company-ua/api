<?php

namespace App\Http\Requests\Osago;

use App\Models\Order;
use App\Rules\Boolean;
use App\Rules\Inn;
use App\Services\api\Ingo;
use App\Traits\RequestFailedValidationResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IngoSaveRequest extends FormRequest
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

            'transport.transport_category_id' => 'required|exists:App\Models\TransportCategory,id',
            'transport.transport_power_id' => 'required|exists:App\Models\TransportPower,id',
            'transport.car_mark_id' => 'nullable|required_if:upload_docs,0|exists:App\Models\CarMark,id',
            'transport.car_model_id' => 'nullable|required_if:upload_docs,0|exists:App\Models\CarModel,id',
            'transport.gov_num' => 'nullable|required_if:upload_docs,0|string|min:6',
            'transport.vin' => 'nullable|required_if:upload_docs,0|string|min:6',
            'transport.car_year' => 'nullable|required_if:upload_docs,0|digits:4|integer|min:1970|max:' . date('Y'),

            'insurant.type' => ['required', Rule::in(Order::INSURANT_TYPES)],
            'insurant.phone' => 'nullable|required_if:upload_docs,0|string|min:6',
            'insurant.surname' => 'nullable|required_if:upload_docs,0|string',
            'insurant.name' => 'nullable|required_if:upload_docs,0|string',
            'insurant.patronymic' => 'nullable|nullable|string',
            'insurant.inn' => ['nullable', 'required_if:upload_docs,0', new Inn],
            'insurant.birth' => 'nullable|required_if:upload_docs,0|date|before_or_equal:18 years ago',
            'insurant.address' => 'nullable|required_if:upload_docs,0|string',
            'insurant.doc_type' => 'required|integer|min:1|max:14',
            'insurant.doc_number' => 'nullable|required_if:upload_docs,0|string',
            'insurant.doc_series' => 'nullable|string',
            'insurant.doc_given' => 'nullable|required_if:upload_docs,0|string',
            'insurant.doc_date' => 'nullable|required_if:upload_docs,0|date|before_or_equal:today',

            'promocode' => 'nullable|string',

            'ga_id' => 'nullable'
        ];
    }
}
