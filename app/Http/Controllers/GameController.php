<?php


namespace App\Http\Controllers;

use Carbon\Carbon;
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
        $user = Auth::user();
        if( 'player' == $user->role ){
            $now = Carbon::now(config('app.timezone'));
            $start = clone $now;
            $start->timestamp = DB::table('game_config')->where('key', 'starting_time')->value('value');
            $end = clone $start;
            $end->addMinutes(DB::table('game_config')->where('key', 'duration')->value('value'));


            if(!$now->between($start, $end) || !DB::table('locations')->count()) return redirect('/');

            $player_started = DB::table('game_log')->where('user', '=', $user->id)->count();
            if( !$player_started ){
                DB::table('game_log')->insert(
                    [
                        'user' => $user->id,
                        'location' => DB::table('locations')->select('id')->where('order', '=', 0)->first()->id
                    ]
                );
            }

            return view('game')->with('game', $this);
        }
        elseif( 'admin' == $user->role ){
            return view('game-admin')->with('game', $this);
        }
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

        if( in_array( strtolower(Input::get('answer')), $answers) ){

            $existing = DB::table('question_solved_log')
                ->where('user', $user)
                ->where('question', $question->id)
                ->count();

            if(!$existing){
                //Store solving record in database
                DB::table('question_solved_log')->insert([
                    'user' => $user,
                    'question' => $question->id
                ]);
            }
            
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

        $lat = round(Input::get('lat'), 4);
        $lng = round(Input::get('lng'), 4);

        $current_location = DB::table('game_log')
            ->select('location')
            ->where('user', $user)
            ->orderBy('start_time', 'desc')
            ->value('location');

        $current_location = DB::table('locations')
            ->where('id', $current_location)
            ->first();

        //Coordinates sent from user matched what is stored in the database?
        if(round($current_location->lat, 4) != $lat || round($current_location->lng, 4) != $lng) return [];

        
        // Check if all questions were answered before approving the location to be done
        $answered_questions = $this->answeredQuestions();

        $unanswered_questions = DB::table('questions')
            ->where('location', $current_location->id)
            ->whereNotIn('id', $answered_questions)
            ->count();

        if( $unanswered_questions ) return [];


        $next_location = DB::table('locations')
            ->where('order', '>', $current_location->order)
            ->orderBy('order', 'asc')
            ->first();

        DB::table('game_log')
            ->where('location', $current_location->id)
            ->where('user', $user)
            ->update([
                'end_time' => Carbon::now(config('app.timezone'))
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


    public function postConfig(){
        $user = Auth::user();
        if( 'admin' != $user->role ) return [];

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('game_log')->truncate();
        DB::table('question_solved_log')->truncate();
        DB::table('questions')->truncate();
        DB::table('locations')->truncate();
        DB::table('game_config')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $config = Input::get('config');
        DB::table('game_config')->insert(['key' => 'starting_time', 'value' => strtotime($config['starting_time'])]);
        DB::table('game_config')->insert(['key' => 'duration', 'value' => (int) $config['duration']]);
        DB::table('game_config')->insert(['key' => 'freez_time', 'value' => (int) $config['freez_time']]);

        $locations = Input::get('locations');
        $order = 0;
        foreach($locations as $location){
            $id = DB::table('locations')->insertGetId([
                'lat' => round($location['lat'], 4),
                'lng' => round($location['lng'], 4),
                'min_zoom' => $location['zoom'],
                'clue' => $location['clue'],
                'welcome_text' => $location['welcome_text'],
                'order' => $order++
                ]);

            $questions = explode("\n", $location['questions']);
            $question_order = 0;
            $processed_questions = [];
            foreach($questions as $question){
                $parts = explode('|', $question);
                if( count($parts) != 2 ) continue;

                $q = trim($parts[0]);
                $a = explode(',', $parts[1]);

                $a = array_map( function($ans){
                    return trim($ans);
                }, $a);

                $processed_questions[] = [
                    'location' => $id,
                    'order' => $question_order++,
                    'question' => $q,
                    'possible_answers' => serialize($a)
                ];

            };

            DB::table('questions')->insert($processed_questions);

        }
    }

    public function config(){

        $locations = DB::table('locations')->get();
        $processed_locations = [];
        foreach($locations as $location){
            $questions = DB::table('questions')->where('location', $location->id)->get();
            $processed_questions = [];

            foreach($questions as $question){
                $ans = implode(',', unserialize($question->possible_answers));
                $processed_questions[] = $question->question . '|' . $ans;
            }
            $questions = implode("\n", $processed_questions);

            $location->questions = $questions;
            $processed_locations[] = $location;
        }

        $config = DB::table('game_config')->select('key', 'value')->get();
        $configuration = [];
        foreach($config as $conf){
            $configuration[$conf->key] = $conf->value;
        }

        $starting_time = Carbon::now(config('app.timezone'));
        $starting_time->timestamp = isset( $configuration['starting_time'] )?$configuration['starting_time']:time();
        $configuration['starting_time'] = $starting_time->toDateTimeString();

        return [
            'config' => $configuration,
            'locations' => $locations
        ];
    }

    public function answeredQuestions(){
        $user = Auth::id();

        return DB::table('question_solved_log')
            ->where('user', $user)
            ->lists('question');
    }

    public function actionList(){
        return [
            'location' => action('GameController@getLocation'),
            'verify_answer' => action('GameController@getVerifyAnswer'),
            'next_clue' => action('GameController@getNextClue'),
            'config' => action('GameController@postConfig')
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
