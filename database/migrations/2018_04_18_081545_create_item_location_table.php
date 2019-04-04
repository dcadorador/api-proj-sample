<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('item_location')) {

            Schema::create('item_location', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('item_id')->unsigned();
                $table->integer('location_id')->unsigned();
                $table->timestamps();

                $table->foreign('item_id')
                    ->references('id')
                    ->on('items')
                    ->onDelete('cascade');

                $table->foreign('location_id')
                    ->references('id')
                    ->on('locations')
                    ->onDelete('cascade');
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
        Schema::dropIfExists('item_location');
    }
}
