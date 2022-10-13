<?php

namespace App\Http\Requests;

use App\Models\Order;
use App\Services\OsagoService;
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
            'city_id' => 'nullable|numeric|min:1',
            'city_name' => 'nullable|string|min:1',
            'foreign_check' => 'required|boolean',
            'discount_check' => 'required|boolean',
            'tariff' => ['required', Rule::in(OsagoService::TARIFFS)],
            'polis_start' => 'required|date|after:tomorrow',
            'email' => 'nullable|email',
            'upload_docs' => 'required|boolean',
            'doc_files.*' => 'required_if:upload_docs,true|mimes:jpg,jpeg,png,bmp,pdf',

            'transport.transport_category_id' => 'nullable|required_if:upload_docs,false|exists:App\Models\TransportCategory,id',
            'transport.transport_power_id' => 'nullable|required_if:upload_docs,false|exists:App\Models\TransportPower,id',
            'transport.car_mark' => 'nullable|required_if:upload_docs,false|string',
            'transport.car_model' => 'nullable|required_if:upload_docs,false|string',
            'transport.gov_num' => 'nullable|required_if:upload_docs,false|string|min:6',
            'transport.vin' => 'nullable|required_if:upload_docs,false|string|min:6',
            'transport.car_year' => 'nullable|required_if:upload_docs,false|digits:4|integer|min:1970|max:' . date('Y'),

            'insurant.type' => ['nullable', 'required_if:upload_docs,false', Rule::in(Order::INSURANT_TYPES)],
            'insurant.phone' => 'nullable|required_if:upload_docs,false|string|min:6',
            'insurant.surname' => 'nullable|required_if:upload_docs,false|string',
            'insurant.name' => 'nullable|required_if:upload_docs,false|string',
            'insurant.patname' => 'nullable|nullable|string',
            'insurant.inn' => 'nullable|required_if:upload_docs,false|string|min:10|max:10',
            'insurant.birth' => 'nullable|required_if:upload_docs,false|date|before_or_equal:18 years ago',
            'insurant.address' => 'nullable|required_if:upload_docs,false|string',
            'insurant.street' => 'nullable|required_if:upload_docs,false|string',
            'insurant.house' => 'nullable|required_if:upload_docs,false|string',
            'insurant.flat' => 'nullable|string',
            'insurant.doc_type' => ['nullable', 'required_if:upload_docs,false', Rule::in(Order::DOC_TYPES)],
            'insurant.doc_number' => 'nullable|required_if:upload_docs,false|string',
            'insurant.doc_series' => 'nullable|required_if:upload_docs,false|string',
            'insurant.doc_given' => 'nullable|required_if:upload_docs,false|string',
            'insurant.doc_date' => 'nullable|required_if:upload_docs,false|date|before_or_equal:today',
        ];
    }
}
