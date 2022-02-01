<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReserveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // 認証を未実装のため、常にtrue
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
            'guest_name'      => ['required', 'string',],
            'start_date_time' => ['bail', 'required', 'date', 'regex:/^(\d{4})-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])T([0-1][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', 'before:end_date_time'],
            'end_date_time'   => ['bail', 'required', 'date', 'regex:/^(\d{4})-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])T([0-1][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', 'after:start_date_time'],
            'purpose'         => ['required', 'string',],
            'guest_detail'    => ['required', 'string',],
            'room_id'         => ['required', 'min:1', 'max:6'],
        ];
    }
}
