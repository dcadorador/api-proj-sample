<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('applications')) {
            Schema::create('applications', function (Blueprint $table) {
                $table->increments('id');
                $table->text('label')->nullable();
                $table->integer('concept_id')->unsigned();
                $table->text('google_arn')->nullable();
                $table->text('apple_arn')->nullable();
                $table->text('web_arn')->nullable();
                $table->text('broadcast_topic_arn')->nullable();
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
        Schema::dropIfExists('applications');
    }
}
