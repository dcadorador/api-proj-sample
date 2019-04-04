<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimedEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('item_location')) {
            Schema::create('timed_events', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('concept_id')->unsigned();
                $table->string('label')->nullable();
                $table->tinyInteger('is_active')->nullable();
                $table->integer('value')->unsigned()->nullable();
                $table->dateTime('from_date')->nullable();
                $table->dateTime('to_date')->nullable();
                $table->text('event_times')->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
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
        Schema::dropIfExists('timed_events');
    }
}
