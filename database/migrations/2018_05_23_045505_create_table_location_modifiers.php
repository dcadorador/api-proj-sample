<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableLocationModifiers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        if (!Schema::hasTable('location_modifiers')) {

            Schema::create('location_modifiers', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('modifier_id')->unsigned();
                $table->integer('location_id')->unsigned();
                $table->dateTime('created_at');
                $table->dateTime('updated_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('location_modifiers');
    }
}
