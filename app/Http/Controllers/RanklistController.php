<?php


namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use App\User;
use App\Http\Controllers\Controller;

class RanklistController extends Controller
{
    public function getIndex(){
        return view('ranklist')->with('ranklist', $this);;
    }

    public function users(){
        $now = Carbon::now(config('app.timezone'));
        $start = clone $now;
        $start->timestamp = DB::table('game_config')->where('key', 'starting_time')->value('value');
        $end = clone $start;
        $end->addMinutes(DB::table('game_config')->where('key', 'duration')->value('value'));
        $freez = clone $end;
        $freez->subMinutes(DB::table('game_config')->where('key', 'freez_time')->value('value'));

        $users = DB::table('users')
            ->where('users.role', 'player')
            ->join('game_log', function( $join ) use ($freez){
                $join->on('users.id', '=', 'game_log.user')
                    ->whereNotNull('game_log.end_time')
                    ->where('game_log.end_time', '<=', $freez->toDateTimeString());
            })
            ->groupBy('game_log.user')
            ->select(DB::raw('users.name as name, users.institute as institute, TIMESTAMPDIFF(MINUTE,\''.$start->toDateTimeString().'\',max(game_log.end_time)) as time, count(*) as passed_locations'))
            ->orderBy('passed_locations', 'desc')
            ->orderBy('time', 'asc')
            ->get();

        if(!empty($users)){
        $users[0]->rank = 1;
            for( $i = 1; $i < count($users); $i++ ){
                $users[$i]->rank = $users[$i-1]->rank+1;
                if($users[$i]->time == $users[$i-1]->time && $users[$i]->passed_locations == $users[$i]->passed_locations){
                    $users[$i]->rank--;
                }
            }
        }



        return $users;
    }
}
