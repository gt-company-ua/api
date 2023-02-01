<?php

namespace App\Http\Requests;

use App\Models\Order;
use App\Rules\Boolean;
use App\Rules\Inn;
use App\Traits\RequestFailedValidationResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GreenCardSaveRequest extends FormRequest
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
            'trip_country' => ['required', Rule::in(Order::TRIP_COUNTRIES)],
            'trip_duration' => 'required|numeric|min:0|max:12',
            'email' => 'required|email',
            'polis_start' => 'required|date|after:today',
            'comment' => 'nullable|string',
            'promocode' => 'nullable|string',
            'cashback_phone' => 'nullable|string',
            'dont_call' => ['nullable', new Boolean],

            'upload_docs' => ['required', new Boolean],
            'files' => 'required_if:upload_docs,1|array',
            'files.*' => 'mimes:jpg,jpeg,png,bmp,pdf,zip,rar,7z,heic,heif,hevc,hevf',

            'transport.transport_category_id' => 'required|exists:App\Models\TransportCategory,id',
            'transport.car_mark' => 'nullable|required_if:upload_docs,0|string',
            'transport.car_model' => 'nullable|required_if:upload_docs,0|string',
            'transport.gov_num' => 'nullable|required_if:upload_docs,0|string|min:6',
            'transport.vin' => 'nullable|required_if:upload_docs,0|string|min:6',
            'transport.car_year' => 'nullable|required_if:upload_docs,0|digits:4|integer|min:1970|max:' . date('Y'),

            'city_name' => 'nullable|required_if:upload_docs,0|string|min:1',
            'insurant.phone' => 'required|string|min:6',
            'insurant.surname' => 'nullable|required_if:upload_docs,0|string',
            'insurant.name' => 'nullable|required_if:upload_docs,0|string',
            'insurant.surname_latin' => 'nullable|required_if:upload_docs,0|string',
            'insurant.name_latin' => 'nullable|required_if:upload_docs,0|string',

            'insurant.inn' => ['nullable', new Inn],
            'insurant.birth' => 'nullable|required_if:upload_docs,0|date|before_or_equal:18 years ago',

            'ga_id' => 'nullable'

        ];
    }
}
