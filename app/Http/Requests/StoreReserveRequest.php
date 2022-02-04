<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Enums\RepitationType;
use BenSampo\Enum\Rules\EnumValue;
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
                case RepitationType::DAILY:
                    if (! $end->between($start, $start->copy()->addDay())) $fail('start_date_time and end_date_time must be scheduled within 24 hours.');
                case RepitationType::WEEKLY:
                    if (! $end->between($start, $start->copy()->addWeek())) $fail('start_date_time and end_date_time must be scheduled within a week.');
            }
        };

        $repitation_method = function($attr, $val, $fail)
        {
            if($this->input('repitation.type') != RepitationType::NOTHING) {
                if ($this->has('repitation.num') && $this->has('repitation.finish_at'))
                $fail('Only one of repitation.num and repitation.finish_at can be specified, not both.');
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
            'repitation.type' => ['required', new EnumValue(RepitationType::class), $repitation_duration, $repitation_method],
            'repitation.num'  => ['integer', 'min:1', 'required_without:repitation.finish_at', ],
            'repitation.finish_at' => ['date_format:Y-m-d\TH:i:s.ve', 'required_without:repitation.num', $repitation_finish_date],
        ];
    }
}
