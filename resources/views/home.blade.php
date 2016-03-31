@extends('template')

@section('title', trans('pages.home'))

@section('scripts')
	@parent
	<script src="asset/js/jquery.countdown.min.js"></script>

@endsection

@section('content')
	<div class="transparent-bg content container page">
		<div style="verticle-aligh:middle">
			<h1 class="text-center">Game starts in...</h1>
			<div class="row countdown text-center"></div>
		</div>
	</div>
@endsection