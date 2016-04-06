@extends('template')

@section('title', trans('pages.home'))

@section('scripts')
	@parent
	<script src="asset/js/jquery.countdown.min.js"></script>
	
	@if($start->gt($now))
	<script>var ts = '{{$start->toIso8601String()}}';</script>
	@elseif($end->gt($now))
	<script>var ts = '{{$end->toIso8601String()}}';</script>
	@endif

@endsection

@section('content')
	<div class="transparent-bg content container page">
		<div style="verticle-aligh:middle">
			@if($start->gt($now))
			<h1 class="text-center">Game starts in...</h1>
			<div class="row countdown text-center"></div>
			@elseif($end->gt($now))
			<h1 class="text-center">Time Left</h1>
			<div class="row countdown text-center"></div>
			<div class="text-center">
				<br>
				<a class="btn btn-primary" href="{{route('game')}}">Enter Game</a>
			</div>
			@else
			<h1 class="text-center">Time Up!</h1>
			@endif
			
		</div>
	</div>
@endsection