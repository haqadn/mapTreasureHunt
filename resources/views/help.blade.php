@extends('template')

@section('title', trans('page.help'))

@section('content')
<div class="container transparent-bg">
	<div class="col-xs-12">
		<article>
			<h1>{{ trans('pages.help') }}</h1>
			<p>
				Please follow the instructions below instruction to play LUCC Tech Storm Online Treasure Hunt!
			</p>
			<ol>
				<li>Keep in mind that LUCC rocks B|.</li>
				<li>Your clock starts ticking instantly when the contest begins.</li>
				<li>Start by going to the "Game" page from the navigation bar.</li>
				<li>You will be given a clue when you first arrive at the page.</li>
				<li>Use this clue to find out the location of the next clue in the map.</li>
				<li>Use the search feature of the map to find clue location faster.</li>
				<li>Different clues require different minimum zoom level to be spotted. If you don't see your clue on your expected spot, look closer.</li>
				<li>The team which finds the treaser before everyone else, wins.</li>
				<li>If at least three teams are not able to find the treasure, winners will be decided by number of clues discovered.</li>
				<li>When two or more teams have same number of clues discovered, time taken to discover the clues will be taken into account.</li>
				<li>If you don't understand the ranking rules, just keep an eye on the "Leaderboard".</li>
				<li>You can see all of your opponents progress in the leaderboard.</li>
				<li>The questions and clues are given in Bengali, you must provide answer in English.</li>
				<li>You are free to look into web or call someone for answers that you may face during the treasure hunting journey.</li>
				<li>Any attempt to hack the system to manupulate the results will result in disqualification.</li>
				<li>Leaderboard may be frozen during the end of the contest.</li>
			</ol>
		</article>
	</div>
</div>
@endsection
