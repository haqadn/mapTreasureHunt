<?php


namespace App\Http\Controllers;

use DB;
use App\User;
use App\Http\Controllers\Controller;

class RanklistController extends Controller
{
    public function getIndex(){
        return view('ranklist')->with('ranklist', $this);;
    }

    public function users(){

        $users = DB::table('users')
            ->where('users.role', 'player')
            ->join('game_log', function( $join ){
                $join->on('users.id', '=', 'game_log.user')
                    ->whereNotNull('game_log.end_time');
            })
            ->groupBy('game_log.user')
            ->select(DB::raw('users.name as name, users.institute as institute, TIMESTAMPDIFF(MINUTE,min(game_log.start_time),max(game_log.end_time)) as time, count(*) as passed_locations'))
            ->orderBy('passed_locations', 'desc')
            ->orderBy('time', 'asc')
            ->get();

        $users[0]->rank = 1;
        for( $i = 1; $i < count($users); $i++ ){
            $users[$i]->rank = $users[$i-1]->rank+1;
            if($users[$i]->time == $users[$i-1]->time && $users[$i]->passed_locations == $users[$i]->passed_locations){
                $users[$i]->rank--;
            }
        }



        return $users;
    }
}
