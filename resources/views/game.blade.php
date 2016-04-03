@extends('template')

@section('title', trans('pages.game'))

@section('scripts')
	@parent

	<!-- Google MAPS API -->
	<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_API_KEY') }}&libraries=places"
  type="text/javascript"></script>

@endsection

@section('content')
	<div id="map"></div>
	<img class="modal-call hidden" src="{{ asset('asset/images/game-ask-genie-default.png') }}">
	@include('modal')
@endsection