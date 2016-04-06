<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
use Carbon\Carbon;

Route::group(['middleware' => ['web']], function () {

    Route::get('/', function () {
        $now = Carbon::now(config('app.timezone'));
        $start = clone $now;
        $start->timestamp = DB::table('game_config')->where('key', 'starting_time')->value('value');
        $end = clone $start;
        $end->addMinutes(DB::table('game_config')->where('key', 'duration')->value('value'));

        return view('home')->with(['start' => $start, 'end' => $end, 'now' => $now]);
    })->name('home');

    Route::controller('game', 'GameController', [
        'getIndex' => 'game',
    ]);

    Route::get('ranklist', ['uses' => 'RanklistController@getIndex', 'as' => 'ranklist']);

    Route::get('help', function () {
        return view('help');
    })->name('help');

    Route::auth();
});
