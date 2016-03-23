<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReferForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('game_log', function ($table) {
            $table->foreign('user')->references('id')->on('users');
        });
        Schema::table('questions', function ($table) {
            $table->foreign('location')->references('id')->on('locations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('game_log', function ($table) {
            $table->dropForeign('game_log_user_foreign');
        });
        Schema::table('questions', function ($table) {
            $table->dropForeign('questions_location_foreign');
        });
    }
}
