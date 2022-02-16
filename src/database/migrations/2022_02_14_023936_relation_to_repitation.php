<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RelationToRepitation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reserves', function (Blueprint $table) {
            $table->bigInteger('repitation_id')->unsigned()->nullable();
            $table->foreign('repitation_id')->references('id')->on('repitations')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reserves', function (Blueprint $table) {
            $table->dropForeign('reserves_repitation_id_foreign');
            $table->dropColumn('repitation_id');
        });
    }
}
