@extends('template')

@section('title', trans('pages.home'))

@section('scripts')
	@parent
	<meta http-equiv="refresh" content="60">
@endsection

@section('content')
	<div class="container transparent-bg">

		<table class="table">
			<thead>
				<tr>
					<th>Rank</th>
					<th>Name</th>
					<th>Institute</th>
					<th>Time</th>
					<th>Discovered Clues</th>
				</tr>
			</thead>
			<tbody>
				@foreach( $ranklist->users() as $player )
				<tr>
					<td>{{$player->rank}}</td>
					<td>{{$player->name}}</td>
					<td>{{$player->institute}}</td>
					<td>{{$player->time}}</td>
					<td>{{$player->passed_locations}}</td>
				</tr>
				@endforeach
		    </tbody>
		</table>

	</div>
@endsection