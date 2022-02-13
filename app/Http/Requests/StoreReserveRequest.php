<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Enums\RepitationType;
use Carbon\Carbon;

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

        $repitation_duration = function($attr, $val, $fail)
        {
            $start = new Carbon($this->input('start_date_time'));
            $end = new Carbon($this->input('end_date_time'));
            switch($this->input('repitation.type')) {
                case 1:
                    if (! $end->between($start, $start->copy()->addDay())) $fail('start_date_time and end_date_time must be scheduled within 24 hours.');
                case 2:
                    if (! $end->between($start, $start->copy()->addWeek())) $fail('start_date_time and end_date_time must be scheduled within a week.');
            }
        };

        $repitation_method_both = function($attr, $val, $fail)
        {
            if($this->input('repitation.type') != 0) {
                if ($this->has('repitation.num') && $this->has('repitation.finish_at'))
                $fail('Only one of repitation.num and repitation.finish_at can be specified, not both.');
            }
        };

        $repitation_method_nothing = function($attr, $val, $fail)
        {
            if($this->input('repitation.type') != 0) {
                if ($this->missing('repitation.num') && $this->missing('repitation.finish_at'))
                $fail('Either repitation.num or repitation.finish_at must be specified.');
            }
        };

        $repitation_finish_date = function($attr, $val, $fail)
        {
            if(! (new Carbon($this->input('end_date_time')))->lte((new Carbon($val))->endOfDay())) $fail('Reservations must be made at least once.');
        };

        return [
            'guest_name'      => ['string',],
            'start_date_time' => ['bail', 'required', 'date_format:Y-m-d\TH:i:s.ve', 'before:end_date_time'],
            'end_date_time'   => ['bail', 'required', 'date_format:Y-m-d\TH:i:s.ve', 'after:start_date_time'],
            'purpose'         => ['required', 'string',],
            'guest_detail'    => ['string',],
            'room_id'         => ['required', 'integer', 'between:1,6'],
            'repitation.type' => ['required', 'between:0,2', $repitation_duration, $repitation_method_both, $repitation_method_nothing],
            'repitation.num'  => ['integer', 'min:1', ],
            'repitation.finish_at' => ['date_format:Y-m-d', $repitation_finish_date],
        ];
    }
}
