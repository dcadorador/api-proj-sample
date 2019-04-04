<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFavItemIngTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('favorite_item_ingredients')) {
            Schema::create('favorite_item_ingredients', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('favorite_item_id')->unsigned();
                $table->integer('ingredient_id')->unsigned()->nullable();
                $table->integer('item_ingredients_id')->unsigned()->nullable();
                $table->integer('quantity');
                $table->double('price', 6, 2);
                $table->timestamps();

                $table->foreign('favorite_item_id')
                    ->references('id')
                    ->on('customer_favorites')
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
        Schema::dropIfExists('favorite_item_ingredients');
    }
}
