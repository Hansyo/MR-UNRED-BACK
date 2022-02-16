<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GetIndexReserveRequest extends FormRequest
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

        $isValidDate = function($attr, $val, $fail)
        {
            if(! preg_match('/^(\d{4})-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])T([0-1][0-9]|2[0-4]):[0-5][0-9]:[0-5][0-9].\d{3}Z$/', $val, $matches))
                $fail('Date format is wrong.');
            else if(! checkdate(intval($matches[2]), intval($matches[3]), intval($matches[1]))) // month, day, year
                $fail('Date does not exit.');
        };

        return [
            'start_date_time' => ['bail', 'required_with_all:start_date_time', $isValidDate, 'before:end_date_time'],
            'end_date_time'   => ['bail', 'required_with_all:end_date_time',   $isValidDate, 'after:start_date_time'],
            'room_id'         => ['integer', 'between:1,6'],
        ];
    }

    protected function failedValidation(Validator $validator) {
        throw new HttpResponseException(response()->json(["message" =>"The given data was invalid.", "errors" => $validator->errors()], 422));
    }
}
