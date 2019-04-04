<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemOrderIngredientTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('item_order_ingredient')) {
            Schema::create('item_order_ingredient', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('item_order_id')->unsigned();
                $table->integer('ingredient_id')->unsigned()->nullable();
                $table->integer('item_ingredients_id')->unsigned()->nullable();
                $table->integer('quantity');
                $table->double('price', 6, 2);
                $table->timestamps();

                $table->foreign('item_order_id')
                    ->references('id')
                    ->on('item_orders')
                    ->onDelete('cascade');

                $table->foreign('item_ingredients_id')
                    ->references('id')
                    ->on('item_ingredients')
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
        //
        Schema::dropIfExists('item_order_ingredient');
    }
}
