<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Repitation;

class CreateReserveTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reserves', function (Blueprint $table) {
            $table->id();
            $table->text("guest_name");
            $table->dateTime("start_date_time");
            $table->dateTime("end_date_time");
            $table->longText("purpose");
            $table->longText("guest_detail");
            $table->integer('room_id')->unsigned();
            $table->foreignIdFor(Repitation::class)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reserves');
    }
}
