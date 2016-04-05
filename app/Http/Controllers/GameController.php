<?php


namespace App\Http\Controllers;

use Auth;
use DB;
use Request;
use Illuminate\Support\Facades\Input;
use App\User;
use App\Http\Controllers\Controller;

class GameController extends Controller
{

    /**
         * Instantiate a new GameController instance.
         */
    public function __construct(){
        $this->middleware('auth');
    }

    public function getIndex(){
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
        return view('game')->with('game', $this);;
    }

    public function getLocation(){
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
    }

    public function getVerifyAnswer(){
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

    }

    public function getNextClue(){
        $user = Auth::id();
        if (!Request::ajax()) return [];

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

    }

    public function actionList(){
        return [
            'location' => action('GameController@getLocation'),
            'verify_answer' => action('GameController@getVerifyAnswer'),
            'next_clue' => action('GameController@getNextClue')
        ];
    }

    public function nextClue(){
        $user = Auth::id();
        $current_location = DB::table('game_log')
            ->where('user', '=', $user)
            ->whereNull('end_time')
            ->value('location');

        $current_location = DB::table('locations')
            ->where('id', $current_location)
            ->first();

        return is_null($current_location) ? '' : htmlspecialchars($current_location->clue);
    }

    public function mapCenter(){
        $first_location = DB::table('locations')->select('lat', 'lng')->where('order', '=', 0)->first();
        $map_center = [
            'lat' => (double) $first_location->lat,
            'lng' => (double) $first_location->lng
        ];

        return $map_center;

    }

    public function outputLocations(){
        $user = Auth::id();
        $unlocked_locations = DB::table('game_log')
            ->where('user', $user)
            ->whereNotNull('end_time')
            ->lists('location');

        $unlocked_locations = DB::table('locations')
            ->select('id as index', 'lat', 'lng', 'clue', 'welcome_text', 'order')
            ->whereIn('id', $unlocked_locations)
            ->orderBy('order', 'asc')
            ->get();

        $solved_questions = DB::table('question_solved_log')
            ->where('user', $user)
            ->lists('question');


        $solved_questions = is_null($solved_questions) ? [] : $solved_questions;

        $questions = is_null($solved_questions) ? [] : DB::table('questions')
            ->select('id', 'question', 'location')
            ->whereIn('id', $solved_questions)
            ->orderBy('order', 'asc')
            ->get();

        $locations = [];
        foreach($unlocked_locations as $location){
            $locations[$location->index] = $location;
            $locations[$location->index]->questions = [];
            $locations[$location->index]->last = false;
        }


        foreach($questions as $question){
            if(!isset($locations[$question->location])) continue;

            $locations[$question->location]->questions[] = ['id' => $question->id, 'q' => $question->question, 'solved' => true];
        }

        // Add unlocked but unsolved question to the array
        $unfinished_location = DB::table('game_log')
            ->where('user', $user)
            ->whereNull('end_time')
            ->value('location');


        if( !is_null($unfinished_location) && !empty($locations) ){
            $keys = array_keys($locations);
            $last_key = end($keys);
            $locations[$last_key]->solved = false;
            $unsolved_question = DB::table('questions')
                ->select('id', 'question')
                ->where('location', $unfinished_location)
                ->whereNotIn('id', $solved_questions)
                ->orderBy('order', 'asc')
                ->first();

            if(!is_null($unsolved_question))
                $locations[$last_key]->questions[] = ['id' => $unsolved_question->id, 'q' => $unsolved_question->question, 'solved' => false];


        }

        // Set if the last loaded location is actually the last one
        if(!empty($locations)){
            $keys = array_keys($locations);
            $last_key = end($keys);
            $locations_left = DB::table('locations')
                ->where('order', '>', $locations[$last_key]->order)
                ->count();

            if( !$locations_left ) $locations[$last_key]->last = true;
        }

        return $locations;
    }
}
