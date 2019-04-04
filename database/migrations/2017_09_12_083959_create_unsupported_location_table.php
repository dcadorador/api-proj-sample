<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnsupportedLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasTable('unsupported_locations')) {
            Schema::create('unsupported_locations',function (Blueprint $table) {
                $table->increments('id');
                $table->integer('customer_id')->unsigned()->nullable();
                $table->decimal('latitude',15,13)->nullable();
                $table->decimal('longitude',15,13)->nullable();
                $table->integer('concept_id')->unsigned()->nullable();
                $table->timestamps();
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
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::drop('unsupported_locations');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1 ');
    }
}
