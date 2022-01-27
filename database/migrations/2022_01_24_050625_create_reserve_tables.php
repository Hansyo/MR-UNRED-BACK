<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->text('mail_addr');
            $table->text('phone_num');
            $table->integer('guest_num')->unsigned()->nullable();
            $table->integer('room_id')->unsigned();
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
