@extends('template')

@section('title', trans('pages.home'))

@section('scripts')
	@parent
	<script src="asset/js/jquery.countdown.min.js"></script>

@endsection

@section('content')
	<div class="transparent-bg content container-fluid">
		<h1 class="text-center">Game starts in...</h1>
		<div class="row countdown text-center"></div>
	</div>


	<script>
	$('.countdown').countdown({
	    end_time: "2017/06/21 14:27:28 +0600",
	    show_day: false,
	    wrapper: function( unit ){
	    	var wrapper = $('<div class="' + unit.toLowerCase() + '_wrapper col-md-4" />');
			wrapper.append('<span class="counter" />');
			wrapper.append('<span class="title">' + unit + '</span>');

			return wrapper;
	    }
	});
	</script>
@endsection