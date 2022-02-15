<?php

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Room::insert([
            ["name"   => "会議室1", "detail" => "詳細はありません",],
            ["name"   => "会議室2", "detail" => "詳細はありません",],
            ["name"   => "会議室3", "detail" => "詳細はありません",],
            ["name"   => "会議室4", "detail" => "詳細はありません",],
            ["name"   => "会議室5", "detail" => "詳細はありません",],
            ["name"   => "会議室6", "detail" => "詳細はありません",],
            ["name"   => "会議室7", "detail" => "詳細はありません",],
            ["name"   => "会議室8", "detail" => "詳細はありません",]
        ]);
    }
}
