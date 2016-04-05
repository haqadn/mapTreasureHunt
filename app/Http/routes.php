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

    Route::get('game/location', function () {
        $user = Auth::id();
        if (!Request::ajax()) return [];

        $location = DB::table('locations')
            ->where(
                'id',
                '=',
                DB::table('game_log')
                    ->select('location')
                    ->where('user', $user)
                    ->whereNull('end_time')
                    ->value('location')
                )
            ->first();

        if( is_null($location) ) return [];

        $locations_left = DB::table('locations')
            ->where('order', '>', $location->order)
            ->count();

        $first_question = DB::table('questions')
            ->select('id', 'question')
            ->where('location', $location->id)
            ->orderBy('order', 'asc')
            ->first();
        
        if(
            is_in_range($location->lat, Input::get('top'), Input::get('bottom')) &&
            is_in_range($location->lng, Input::get('left'), Input::get('right')) &&
            Input::get('zoom') >= $location->min_zoom
        ) {
            return [
                'success' => true,
                'index' => $location->id,
                'lat' => $location->lat,
                'lng' => $location->lng,
                'welcome_text' => $location->welcome_text,
                'clue' => $location->clue,
                'questions' => is_null($first_question) ? [] : [ [ 'id' => $first_question->id, 'q' => $first_question->question, 'solved' => false ] ],
                'last' => $locations_left ? false : true
            ];
        }
    })->name('game.location')->middleware('auth');

    Route::get('game/verify_answer', function () {
        $user = Auth::id();
        if (!Request::ajax()) return [];

        $question = DB::table('questions')
            ->where('id', '=', Input::get('qid'))
            ->first();

        $answers = (array) unserialize($question->possible_answers);

        if( in_array( Input::get('answer'), $answers) ){

            //Store solving record in database
            DB::table('question_solved_log')->insert([
                'user' => $user,
                'question' => $question->id
            ]);
            
            // Get next question
            $next_question = DB::table('questions')
                ->where('location', '=', $question->location)
                ->where('order', '>', $question->order)
                ->orderBy('order', 'asc')
                ->first();

            if( is_null( $next_question ) ){

                return [ 'success' => true, 'next_question' => false];
            }
            else {
                return [
                    'success' => true,
                    'next_question' => [
                        'id' => $next_question->id,
                        'q' => $next_question->question
                    ]
                ];
            }

        }
            
        return [ 'success' => false ];

    })->name('game.verify_answer')->middleware('auth');

    Route::get('game/get_next_clue', function () {
        $user = Auth::id();
        // if (!Request::ajax()) return [];

        $lat = Input::get('lat');
        $lng = Input::get('lng');

        $current_location = DB::table('game_log')
            ->select('location')
            ->where('user', $user)
            ->orderBy('start_time', 'desc')
            ->value('location');

        $current_location = DB::table('locations')
            ->where('id', $current_location)
            ->first();

        // return print_r( $current_location, 1);

        $next_location = DB::table('locations')
            ->where('order', '>', $current_location->order)
            ->orderBy('order', 'asc')
            ->first();

        DB::table('game_log')
            ->where('location', $current_location->id)
            ->update([
                'end_time' => date('Y-m-d H:i:s')
                ]);

        if(!is_null($next_location)) {
            DB::table('game_log')
                ->insert(['user' => $user, 'location' => $next_location->id]);

            return $next_location->clue;
        }
        else {
            return "Congratulations, you have found the treasure!";
        }

    })->name('game.next_clue')->middleware('auth');

    Route::get('ranklist', function () {
        return view('ranklist');
    })->name('ranklist');

    Route::get('help', function () {
        return view('help');
    })->name('help');

    Route::auth();
});
