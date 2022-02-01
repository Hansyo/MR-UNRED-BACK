<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'start_date_time' => ['bail', 'required', 'date_format:Y-m-d\TH:i:s.ve', 'before:end_date_time'],
            'end_date_time'   => ['bail', 'required', 'date_format:Y-m-d\TH:i:s.ve', 'after:start_date_time'],
            'room_id'         => ['integer', 'between:1,6'],
        ];
    }
}
