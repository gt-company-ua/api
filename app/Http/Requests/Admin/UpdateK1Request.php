<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateK1Request extends FormRequest
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
            'api_id.*' => 'nullable|integer|min:1',
            'id.*' => 'required|exists:App\Models\TransportPower,id',
        ];
    }
}
