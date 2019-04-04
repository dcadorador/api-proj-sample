<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemIngredientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_ingredients', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('item_id')->unsigned();
            $table->integer('ingredient_id')->unsigned();
            $table->integer('quantity')->default(1);
            $table->integer('minimum_quantity')->default(0);
            $table->integer('maximum_quantity')->default(1);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->foreign('item_id')
                  ->references('id')
                  ->on('items')
                  ->onDelete('cascade');

            $table->foreign('ingredient_id')
                  ->references('id')
                  ->on('ingredients')
                  ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_ingredients');
    }
}
