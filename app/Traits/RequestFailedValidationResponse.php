<?php


namespace App\Traits;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

trait RequestFailedValidationResponse
{
    use ApiResponser;

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->sendError($validator->errors(), 422));
    }
}
