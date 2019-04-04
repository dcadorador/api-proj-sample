<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSocialMediaTab extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('social_authentication', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('api_subscriber_id')->unsigned()->nullable();
            $table->text('provider_user_id')->nullable();
            $table->string('provider_id')->nullable();
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
        //
        Schema::dropIfExists('providers');
    }
}
