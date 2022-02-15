<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    protected $table = 'photos';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $dates = [
        'start_date_time',
        'end_date_time',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
