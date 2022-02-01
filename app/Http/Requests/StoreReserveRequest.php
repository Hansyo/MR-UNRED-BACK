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
            'guest_name'      => ['string',],
            'start_date_time' => ['bail', 'required', 'date_format:Y-m-d\TH:i:s.ve', 'before:end_date_time'],
            'end_date_time'   => ['bail', 'required', 'date_format:Y-m-d\TH:i:s.ve', 'after:start_date_time'],
            'purpose'         => ['required', 'string',],
            'guest_detail'    => ['string',],
            'room_id'         => ['required', 'integer', 'between:1,6'],
        ];
    }
}
