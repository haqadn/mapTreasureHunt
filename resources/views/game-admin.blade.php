@extends('template')

@section('title', trans('pages.game'))

@section('scripts')
	<meta name="csrf-token" content="{{ csrf_token() }}" />

	@parent
	
	<script>
	$.ajaxSetup({
	    headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    </script>
    
	<script>
	var urls = {!! json_encode($game->actionList()) !!}
	var data = {!! json_encode($game->config()) !!}
	</script>
	<script src="{{ asset('asset/js/game-admin.js') }}"></script>
	<script src="{{ asset('asset/js/jquery-ui.js') }}"></script>

	<!-- Google MAPS API -->
	<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_API_KEY') }}&libraries=places"
  type="text/javascript"></script>
@endsection

@section('content')
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				
				<div class="transparent-bg content">
					<div class="row">
						<div class="form-group col-md-4">
							<label for="starting_time">Starting Time</label>
							<input type="text" class="form-control" id="starting_time" placeholder="yyyy-mm-dd HH:MM:SS">
						</div>
						<div class="form-group col-md-4">
							<label for="duration">Duration</label>
							<input type="text" class="form-control" id="duration" placeholder="Number of minutes">
						</div>
						<div class="form-group col-md-4">
							<label for="freez_time">Freez Time</label>
							<input type="text" class="form-control" id="freez_time" placeholder="Number of minutes"><br>
						</div>
						<div class="col-md-12">
							<label for="final_greeting">Final Acknowledgement Message</label>
							<textarea name="final_greeting" id="final_greeting" class="form-control"></textarea>
						</div>
						<div class="col-md-offset-4 col-md-4 text-center">
							<br>
							<button type="submit" id="save-config" class="btn btn-default" onclick="save_config()">Save Settings</button>
							<span class="saving" style="display:none">Saving...</span>
							<span class="saved" style="display:none">Saved</span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<br>
		<div class="row">
			<div class="col-md-4">
				<div class="transparent-bg">
					<div id="clues" class="content"></div>
				</div>
			</div>
			<div class="col-md-8 map-block">
				<div id="map"></div>
			</div>
		</div>
		<br>
		<div class="row">
			<div class="col-md-12">
				<div class="transparent-bg">
					<div class="text-center content">
						<div class="alert alert-warning">Do not save when game is ongoing. All previous data will be lost.</div>
						<button type="submit" id="save-config" class="btn btn-default" onclick="save_all()">Save Everyting</button>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection