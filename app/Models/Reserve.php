<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserve extends Model
{
    use HasFactory;
    protected $table = 'reserves';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function scopeWhereHasReservation($query, $start, $end){

        $query->where(function($q) use($start, $end) {
            $q->where('start_date_time', '>=', $start)->where('start_date_time', '<', $end);
        })
        ->orWhere(function($q) use($start, $end){
            $q->where('end_date_time', '>', $start)->where('end_date_time', '<=', $end);
        })
        ->orWhere(function($q) use ($start, $end){
            $q->where('start_date_time', '<', $start)->where('end_date_time', '>', $end);
        });
    }
}
