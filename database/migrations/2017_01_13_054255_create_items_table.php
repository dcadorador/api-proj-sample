<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->nullable();
            $table->integer('category_id')->unsigned()->nullable();
            $table->integer('display_order');
            // $table->string('name');
            $table->integer('name')->unsigned()->nullable();
            // $table->string('description');
            $table->integer('description')->unsigned()->nullable();
            $table->string('image_uri')->nullable();
            $table->double('price', 6, 2);
            $table->boolean('in_stock');
            $table->integer('calorie_count')->default(0);
            $table->tinyInteger('enabled')->default(1);
            $table->softDeletes();
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
        Schema::dropIfExists('items');
    }
}
