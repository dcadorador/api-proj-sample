<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiSubscribersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_subscribers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 255);
            $table->string('password', 255);
            $table->string('userable_id',255)->nullable();
            $table->string('userable_type');
            $table->timestamps();
            $table->softDeletes();

            // unique key
            $table->unique('username');

            // indexes
            $table->index(['username', 'password']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_subscribers');
    }
}
