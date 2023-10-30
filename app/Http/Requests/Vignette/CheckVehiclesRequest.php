<?php

namespace App\Http\Requests\Vignette;

use App\Traits\RequestFailedValidationResponse;
use Illuminate\Foundation\Http\FormRequest;

class CheckVehiclesRequest extends FormRequest
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
            'cars' => 'required|array',
            'cars.*.gov_num' => 'required|string|min:6',
            'cars.*.country_id' => 'required|exists:App\Models\Country,id',
        ];
    }
}
