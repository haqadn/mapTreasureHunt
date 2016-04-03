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

use Illuminate\Support\Facades\Input;

Route::group(['middleware' => ['web']], function () {

    Route::get('/', function () {
        return view('home');
    })->name('home');

    Route::get('game', function () {
        $user = Auth::id();
        $player_started = DB::table('game_log')->where('user', '=', $user)->count();
        if( !$player_started ){
            DB::table('game_log')->insert(
                [
                    'user' => $user,
                    'location' => DB::table('locations')->select('id')->where('order', '=', 0)->first()->id
                ]
            );
        }
        return view('game');
    })->name('game')->middleware('auth');

    Route::get('location', function () {
        $user = Auth::id();
        if (!Request::ajax()) return [];

        $location = DB::table('locations')
            ->where(
                'id',
                '=',
                DB::table('game_log')
                    ->select('location')
                    ->where('user', '=', $user)
                    ->whereNull('end_time')
                    ->first()
                    ->location
                )
            ->first();
        
        if(
            is_in_range($location->lat, Input::get('top'), Input::get('bottom')) &&
            is_in_range($location->lng, Input::get('left'), Input::get('right')) &&
            Input::get('zoom') >= $location->min_zoom
        ) {
            return [
                'success' => true,
                'coords' => [
                    'lat' => $location->lat,
                    'lng' => $location->lng
                ]
            ];
        }
    })->name('location');

    Route::get('ranklist', function () {
        return view('ranklist');
    })->name('ranklist');

    Route::get('help', function () {
        return view('help');
    })->name('help');

    Route::auth();
});
