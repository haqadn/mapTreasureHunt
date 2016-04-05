<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DisableGameLogStartTimeAutoUpdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = DB::getQueryGrammar()->wrapTable('game_log');
        DB::statement('ALTER TABLE '.$tableName.' CHANGE `start_time` `start_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableName = DB::getQueryGrammar()->wrapTable('game_log');
        DB::statement('ALTER TABLE '.$tableName.' CHANGE `start_time` `start_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;');
    }
}
