<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


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

        $repitation_duration = function ($attr, $val, $fail) {
            $start = new Carbon($this->input('start_date_time'));
            $end = new Carbon($this->input('end_date_time'));
            switch ($this->input('repitation.type')) {
                case 1:
                    if (!$end->between($start, $start->copy()->addDay())) $fail('開始日時から終了日時は24時間以内である必要があります。');
                case 2:
                    if (!$end->between($start, $start->copy()->addWeek())) $fail('開始日時から終了日時は一週間以内である必要があります。');
            }
        };

        $repitation_method_both = function ($attr, $val, $fail) {
            if ($this->input('repitation.type') != 0) {
                if ($this->has('repitation.num') && $this->has('repitation.finish_at'))
                    $fail('繰り返し回数か終了日はどちらかのみ指定することができます。');
            }
        };

        $repitation_method_nothing = function ($attr, $val, $fail) {
            if ($this->input('repitation.type') != 0) {
                if ((!$this->filled('repitation.num')) && (!$this->filled('repitation.finish_at')))
                    $fail('繰り返し回数か終了日のどちらかを指定する必要があります。');
            }
        };

        $repitation_finish_date = function ($attr, $val, $fail) {
            if (!(new Carbon($this->input('end_date_time')))->lte((new Carbon($val))->endOfDay())) $fail('少なくとも1回は予約をできる必要があります。');
        };

        $isValidDate = function ($attr, $val, $fail) {
            if (!preg_match('/^(\d{4})-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])T([0-1][0-9]|2[0-4]):[0-5][0-9]:[0-5][0-9].\d{3}Z$/', $val, $matches))
                $fail('日付の形式が間違っています。');
            else if (!checkdate(intval($matches[2]), intval($matches[3]), intval($matches[1]))) // month, day, year
                $fail("$attr は存在しません。");
        };

        return [
            'guest_name'      => ['string',],
            'start_date_time' => ['bail', 'required', $isValidDate, 'after:now', 'before:end_date_time', ],
            'end_date_time'   => ['bail', 'required', $isValidDate, 'after:start_date_time',],
            'purpose'         => ['required', 'string',],
            'guest_detail'    => ['string',],
            'room_id'         => ['required', 'integer', 'exists:rooms,id'],
            'repitation.type' => ['required', 'integer', 'between:0,2', $repitation_duration, $repitation_method_both, $repitation_method_nothing,],
            'repitation.num'  => ['integer', 'min:1',],
            'repitation.finish_at' => ['date_format:Y-m-d', $repitation_finish_date,],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(["message" => "The given data was invalid.", "errors" => $validator->errors()], 422));
    }
}
