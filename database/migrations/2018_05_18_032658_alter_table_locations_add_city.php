<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableLocationsAddCity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('locations', function(Blueprint $table) {
            $table->integer('city_id')->unsigned()->nullable();
            $table->string('line1')->nullable();
            $table->string('line2')->nullable();
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
        Schema::table('locations', function(Blueprint $table) {
            $table->integer('city_id')->unsigned()->nullable();
            $table->string('line1')->nullable();
            $table->string('line2')->nullable();
        });
    }
}
