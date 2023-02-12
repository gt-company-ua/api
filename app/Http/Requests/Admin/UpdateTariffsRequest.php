<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTariffsRequest extends FormRequest
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
            'coefficient.*' => 'required|numeric|min:0',
            'franchise.*' => 'required|numeric|min:0',
            'id.*' => 'required|exists:App\Models\OsagoTariff,id',
        ];
    }
}
