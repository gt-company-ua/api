<?php

namespace App\Http\Requests\Vignette;

use App\Traits\RequestFailedValidationResponse;
use Illuminate\Foundation\Http\FormRequest;

class SaveVignetteRequest extends FormRequest
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
            'email' => 'required|email',
            'start_date' => 'required|date|after:yesterday',
            'period' => 'required|string',
            'vignette_product_id' => 'required|exists:App\Models\VignetteProduct,id',
            'cars' => 'required|array',
            'cars.*.gov_num' => 'required|string|min:6',
            'cars.*.country_id' => 'required|exists:App\Models\Country,id',
        ];
    }
}
