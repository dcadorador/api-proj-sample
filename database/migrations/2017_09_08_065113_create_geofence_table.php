<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGeofenceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('geofences',function (Blueprint $table) {
            $table->increments('id');
            $table->integer('location_id')->unsigned();
            $table->text('inner')->nullable();
            $table->text('outer')->nullable();
            $table->timestamps();
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::drop('geofences');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1 ');
    }
}
