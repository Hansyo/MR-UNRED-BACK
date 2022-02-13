<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Monolog\Logger;

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

        $isValidDate = function($attr, $val, $fail)
        {
            if(! preg_match('/^(\d{4})-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])T([0-1][0-9]|2[0-4]):[0-5][0-9]:[0-5][0-9].\d{3}Z$/', $val, $matches))
                $fail('Date format is wrong.');
            else if(! checkdate(intval($matches[2]), intval($matches[3]), intval($matches[1]))) // month, day, year
                $fail('Date does not exit.');
        };

        return [
            'guest_name'      => ['string',],
            'start_date_time' => ['bail', 'required', $isValidDate, 'before:end_date_time'],
            'end_date_time'   => ['bail', 'required', $isValidDate, 'after:start_date_time'],
            'purpose'         => ['required', 'string',],
            'guest_detail'    => ['string',],
            'room_id'         => ['required', 'integer', 'between:1,6'],
        ];
    }
}
