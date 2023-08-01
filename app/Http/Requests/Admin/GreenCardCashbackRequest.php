<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GreenCardCashbackRequest extends FormRequest
{
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
            'months.*' => 'required|numeric|min:0|max:12',
            'amount_eu_truck.*' => 'nullable|numeric|min:0',
            'amount_sng_truck.*' => 'nullable|numeric|min:0',
            'amount_eu_default.*' => 'nullable|numeric|min:0',
            'amount_sng_default.*' => 'nullable|numeric|min:0',
        ];
    }
}
