<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Reserve extends Model
{
    protected $table = 'reserves';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $dates = [
        'start_date_time',
        'end_date_time',
    ];

    public function toArray()
    {
        return [
            "id" => $this->id,
            "guest_name" => $this->guest_name,
            "start_date_time" => $this->start_date_time->toIsoString(),
            "end_date_time" => $this->end_date_time->toIsoString(),
            "purpose" => $this->purpose,
            "guest_detail" => $this->guest_detail,
            "room_id" => $this->room_id,
            "created_at" => $this->created_at->toIsoString(),
            "updated_at" => $this->updated_at->toIsoString(),
        ];
    }

    public function scopeWhereHasReservation($query, $start, $end)
    {

        $query->where(function ($q) use ($start, $end) {
            $q->where('start_date_time', '>=', $start)->where('start_date_time', '<', $end);
        })->orWhere(function ($q) use ($start, $end) {
            $q->where('end_date_time', '>', $start)->where('end_date_time', '<=', $end);
        })->orWhere(function ($q) use ($start, $end) {
            $q->where('start_date_time', '<', $start)->where('end_date_time', '>', $end);
        });
    }

    public function scopeRoomId($query, $room_id)
    {
        $query->where('room_id', $room_id);
    }

    public function scopeDayOfWeeks($query, $start, $end)
    {
        // TODO: 次の週の同じ曜日まで予定を取れる。その処理を行っていない。
        $sd = new Carbon($start);
        $ed = new Carbon($end);
        $sw = $sd->dayOfWeek + 1; // 日曜: 1 ~ 土曜: 7
        $ew = $ed->dayOfWeek + 1;   // 日曜: 1 ~ 土曜: 7
        if (!$sd->isSameDay($ed) && $sw === $ew) return $query;
        if ($sw <= $ew)
            return $query->whereRaw("NOT ((DAYOFWEEK(start_date_time) <= DAYOFWEEK(end_date_time) AND (${ew} < DAYOFWEEK(start_date_time) OR DAYOFWEEK(end_date_time) < ${sw})) OR (DAYOFWEEK(start_date_time) >= DAYOFWEEK(end_date_time) AND ${ew} < DAYOFWEEK(start_date_time) AND DAYOFWEEK(end_date_time) < ${sw}))");
        else
            return $query->whereRaw("NOT (DAYOFWEEK(start_date_time) <= DAYOFWEEK(end_date_time) AND ${ew} < DAYOFWEEK(start_date_time) AND DAYOFWEEK(end_date_time) < ${sw})");
    }

    public function repitation()
    {
        return $this->belongsTo(Repitation::class);
    }
}
