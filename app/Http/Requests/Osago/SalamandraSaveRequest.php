<?php

namespace App\Http\Requests\Osago;

use App\Models\Order;
use App\Rules\Boolean;
use App\Rules\Inn;
use App\Traits\RequestFailedValidationResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalamandraSaveRequest extends FormRequest
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
            'city_id' => 'required|exists:App\Models\City,id',
            'dgo_limit' => 'nullable|integer',
            'is_pu' => ['nullable', new Boolean],
            'is_dms' => ['nullable', new Boolean],
            'franchise' => 'required|integer',
            'polis_start' => 'required|date|after:today',
            'email' => 'required|email',
            'upload_docs' => ['required', new Boolean],
            'files' => 'required_if:upload_docs,1|array',
            'files.*' => 'mimes:jpg,jpeg,png,bmp,pdf,zip,rar,7z,heic,heif,hevc,hevf',
            'comment' => 'nullable|string',
            'dont_call' => ['nullable', new Boolean],

            'transport.transport_category_id' => 'required|exists:App\Models\TransportCategory,id',
            'transport.transport_power_id' => 'required|exists:App\Models\TransportPower,id',
            'transport.car_mark' => 'nullable|required_if:upload_docs,0|string',
            'transport.car_model' => 'nullable|required_if:upload_docs,0|string',
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
            'insurant.street' => 'nullable|required_if:upload_docs,0|string',
            'insurant.house' => 'nullable|required_if:upload_docs,0|string',
            'insurant.flat' => 'nullable|string',
            'insurant.doc_type' => ['nullable', 'required_if:upload_docs,0', Rule::in(Order::DOC_TYPES)],
            'insurant.doc_number' => 'nullable|required_if:upload_docs,0|string',
            'insurant.doc_series' => 'nullable|string',
            'insurant.doc_given' => 'nullable|required_if:upload_docs,0|string',
            'insurant.doc_date' => 'nullable|required_if:upload_docs,0|date|before_or_equal:today',
            'promocode' => 'nullable|string',

            'ga_id' => 'nullable'
        ];
    }
}
