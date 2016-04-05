@extends('template')

@section('title', trans('pages.game'))

@section('scripts')
	
	<?php
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

		$current_location = DB::table('game_log')
			->where('user', '=', $user)
			->whereNull('end_time')
			->value('location');

		$current_location = DB::table('locations')
			->where('id', $current_location)
			->first();
	?>
	<script>
	var locations = {!! json_encode($locations) !!};
	
	<?php 
	$first_location = DB::table('locations')->select('lat', 'lng')->where('order', '=', 0)->first();
	$map_center = [
		'lat' => (double) $first_location->lat,
		'lng' => (double) $first_location->lng
	];
	?>
	var clue = "{{ is_null($current_location) ? '' : htmlspecialchars($current_location->clue) }}";
	var map_center = {!! json_encode( $map_center ) !!};
	</script>

	@parent
	<script src="{{ asset('asset/js/game.js') }}"></script>

	<!-- Google MAPS API -->
	<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_API_KEY') }}&libraries=places"
  type="text/javascript"></script>

@endsection

@section('content')
	<div id="map"></div>
	<img class="modal-call hidden" src="{{ asset('asset/images/game-ask-genie-default.png') }}">
	@include('modal')
@endsection