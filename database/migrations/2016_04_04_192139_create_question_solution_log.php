<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionSolutionLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('question_solved_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user')->unsigned();
            $table->integer('question')->unsigned();
            $table->timestamp('timestamp');
        });

        Schema::table('question_solved_log', function (Blueprint $table) {
            $table->foreign('user')->references('id')->on('users');
            $table->foreign('question')->references('id')->on('questions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('question_solved_log', function (Blueprint $table ) {
            $table->dropForeign('question_solved_log_user_foreign');
            $table->dropForeign('question_solved_log_question_foreign');
        });
        Schema::drop('question_solved_log');
    }
}
