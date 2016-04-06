@extends('template')

@section('title', trans('pages.game'))

@section('scripts')
	

	@parent
	<script>
	var urls = {!! json_encode($game->actionList()) !!}
	var locations = {!! json_encode($game->outputLocations()) !!};
	var clue = jQuery('<div/>').html({!! json_encode($game->nextClue()) !!}).text();
	var map_center = {!! json_encode( $game->mapCenter() ) !!};
	</script>
	<script src="{{ asset('asset/js/game.js') }}"></script>

	<!-- Google MAPS API -->
	<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_API_KEY') }}&libraries=places"
  type="text/javascript"></script>

@endsection

@section('content')
	<div id="map"></div>
	@include('modal')
@endsection