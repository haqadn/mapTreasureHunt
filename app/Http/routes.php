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
        return view('game');
    })->name('game')->middleware('auth');

    Route::get('location', function () {
        if (!Request::ajax()) return [];

        $chhatak = ['lat' => 25.0387, 'lng' => 91.67];
        
        if(
            is_in_range($chhatak['lat'], Input::get('top'), Input::get('bottom')) &&
            is_in_range($chhatak['lng'], Input::get('left'), Input::get('right')) &&
            Input::get('zoom') >= 14
        ) {
            return json_encode( ['success' => true, 'coords' => $chhatak ] );
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
