<?php

namespace App\Http\Requests;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

abstract class ApiRequest extends FormRequest
{
    protected function failedValidation( Validator $validator )
    {
        throw new HttpResponseException(response()->json(["message" =>"The given data was invalid.", "errors" => $validator->errors()], 422));
    }
}
