<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModifiersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modifiers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->nullable();
            $table->integer('modifier_group_id')->unsigned()->nullable();
            $table->integer('name')->unsigned()->nullable();
            $table->integer('display_order');
            $table->string('image_uri')->nullable();
            $table->double('price', 6, 2);
            $table->integer('calorie_count')->default(0);
            $table->integer('minimum')->default(0);
            $table->integer('maximum')->default(3);
            $table->tinyInteger('enabled')->default(1);
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
        Schema::dropIfExists('modifiers');
    }
}
