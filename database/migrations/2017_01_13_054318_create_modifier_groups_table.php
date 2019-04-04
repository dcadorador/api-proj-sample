<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModifierGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modifier_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->nullable();
            $table->integer('concept_id')->unsigned()->nullable();
            $table->integer('name')->unsigned()->nullable();
            $table->integer('display_order');
            $table->string('image_uri')->nullable();
            $table->integer('minimum')->default(0);
            $table->integer('maximum')->default(5);
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
        Schema::dropIfExists('modifier_groups');
    }
}
