<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NullaleGameLogEndTime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = DB::getQueryGrammar()->wrapTable('game_log');
        DB::statement('ALTER TABLE '.$tableName.' MODIFY `end_time` TIMESTAMP NULL;');
        DB::statement('UPDATE '.$tableName.' SET `end_time` = NULL WHERE `end_time` = \'0000-00-00 00:00:00\';');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableName = DB::getQueryGrammar()->wrapTable('game_log');
        DB::statement('UPDATE '.$tableName.' SET `end_time` = \'0000-00-00 00:00:00\' WHERE `end_time` IS NULL;');
        DB::statement('ALTER TABLE '.$tableName.' MODIFY `end_time` TIMESTAMP NOT NULL;');
    }
}
