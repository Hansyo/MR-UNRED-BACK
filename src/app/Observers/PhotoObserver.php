<?php

namespace App\Observers;

use App\Models\Photo;
use Illuminate\Support\Facades\Storage;

class UserObserver
{
    /**
     * Photo削除イベントのリッスン
     *
     * @param  \App\User  $user
     * @return void
     */
    public function deleting(Photo $photo)
    {
        Storage::delete($photo->path);
    }
}
