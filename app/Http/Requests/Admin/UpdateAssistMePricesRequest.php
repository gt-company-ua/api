<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssistMePricesRequest extends FormRequest
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
            'trip_duration' => 'array',
            'trip_duration.*.*' => 'required|numeric|min:0',
            'price' => 'array',
            'price.*.*' => 'nullable|numeric|min:0',
            'transport_category_id.*' => 'required|exists:App\Models\TransportCategory,id',
        ];
    }
}
