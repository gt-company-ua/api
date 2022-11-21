<?php

namespace App\Http\Requests\Data;

use App\Traits\RequestFailedValidationResponse;
use Illuminate\Foundation\Http\FormRequest;

class InnInfoRequest extends FormRequest
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
            'search' => 'required|digits:10|integer'
        ];
    }
}
