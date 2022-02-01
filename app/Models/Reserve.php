<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserve extends Model
{
    use HasFactory;
    protected $table = 'reserves';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $dates = [
        'start_date_time',
        'end_date_time',
    ];
}
