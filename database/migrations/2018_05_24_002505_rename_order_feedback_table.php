<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameOrderFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('order_feedback', 'order_ratings');

        Schema::table('order_ratings', function(Blueprint $table) {
            $table->dropColumn(['subject', 'body', 'image']);
            $table->integer('feedback_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('order_ratings', 'order_feedback');

        Schema::table('order_feedback', function(Blueprint $table) {
            $table->dropColumn(['subject', 'body', 'image']);
            $table->dropColumn('feedback_id');
        });
    }
}
