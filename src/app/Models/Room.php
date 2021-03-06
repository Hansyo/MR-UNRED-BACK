<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = 'rooms';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $dates = [
        'start_date_time',
        'end_date_time',
    ];

    public function reserves()
    {
        return $this->hasMany(Reserve::class);
    }

    public function toArray()
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "detail" => $this->detail,
            "created_at" => $this->created_at->toIsoString(),
            "updated_at" => $this->updated_at->toIsoString(),
        ];
    }
}
