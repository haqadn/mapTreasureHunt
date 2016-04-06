var new_hint_visible = false;
var map;
var next_clue;

function initMap() {
	$('.pac-container').remove();

	// Specify features and elements to define styles.
	
	// Create a map object and specify the DOM element for display.
	map = new google.maps.Map(document.getElementById('map'), {
    	center:new google.maps.LatLng(24.9095, 91.8611),
		scrollwheel: true,
		// Apply the map style array to the map.
		zoom: 12
	});

	google.maps.event.trigger(map, 'resize');

	// Create the search box and link it to the UI element.
	var i = document.createElement('input');
	$(i)
		.addClass('controls')
		.attr('type', 'text')
		.attr('id', 'pac-input')
		.attr('placeholder', 'Search...');

	var searchBox = new google.maps.places.SearchBox(i);
	map.controls[google.maps.ControlPosition.TOP_LEFT].push(i);


	var searchMarkers = [];
	// Listen for the event fired when the user selects a prediction and retrieve
	// more details for that place.
	searchBox.addListener('places_changed', function() {
		var places = searchBox.getPlaces();

		if (places.length == 0) {
			return;
		}

		// Clear out the old searchMarkers.
		searchMarkers.forEach(function(marker) {
			marker.setMap(null);
		});
		searchMarkers = [];
		new_hint_visible = false;

		// For each place, get the icon, name and location.
		var bounds = new google.maps.LatLngBounds();
		places.forEach(function(place) {
			var icon = {
				url: place.icon,
				size: new google.maps.Size(71, 71),
				origin: new google.maps.Point(0, 0),
				anchor: new google.maps.Point(17, 34),
				scaledSize: new google.maps.Size(25, 25)
			};

			// Create a marker for each place.
			searchMarkers.push(new google.maps.Marker({
				map: map,
				icon: icon,
				title: place.name,
				position: place.geometry.location
			}));

			if (place.geometry.viewport) {
				// Only geocodes have viewport.
				bounds.union(place.geometry.viewport);
			} else {
				bounds.extend(place.geometry.location);
			}
		});
		map.fitBounds(bounds);
	});

	google.maps.event.addListener(map, 'click', function(event) {
	   placeMarker(event.latLng);
	});
}

function placeMarker(location, old_data) {
	console.log(old_data)
    var marker = new google.maps.Marker({
        position: location,
    	animation: google.maps.Animation.DROP,
    	draggable:true,
        map: map
    });

    if(typeof old_data == 'undefined'){
    	old_data = {
    		id: '',
    		clue: '',
    		welcome_text: '',
    		questions: '',
    		min_zoom: map.getZoom()
    	}
    }

    

    var group = $('<div class="group"/>');
    $('<h6/>').text(marker.getPosition().toString()).appendTo(group);
    var content = $('<div/>').appendTo(group);
    $('<br/>').appendTo(content);
    group.appendTo('#clues');

    $('<input type="hidden" class="lat"/>').appendTo(content).val(marker.getPosition().lat());
    $('<input type="hidden" class="lng"/>').appendTo(content).val(marker.getPosition().lng);
    $('<input type="hidden" class="id"/>').appendTo(content).val(old_data.id);
    
    $('<p/>').appendTo(content).text("Zoom :").append( $('<input type="text" class="zoom"/>').val(old_data.min_zoom).addClass('form-control')	);

    $('<p/>').text('Clue')
    	.append($('<textarea/>').addClass('form-control clue').val(old_data.clue))
    	.appendTo(content);

    $('<p/>').text('Welcome Text')
    	.append($('<textarea/>').addClass('form-control welcome-text').val(old_data.welcome_text))
    	.appendTo(content);

    $('<p/>').text('Question Answers')
    	.append($('<textarea/>').addClass('form-control question-answers').val(old_data.questions).attr('placeholder', 'Put in the format "question|ans1,ans2,ans3..." one per line'))
    	.appendTo(content)
    	.val(old_data.questions);

    $('<button/>')
    	.addClass('btn btn-default delete')
    	.text('Remove')
    	.appendTo(content)
    	.click(function(){
    		group.remove();
    		marker.setMap(null);
    	});

    $('#clues').accordion( "refresh" );
    $('#clues').sortable( "refreshPositions" );

    marker.config = group;

    google.maps.event.addListener(marker,'mousedown',function(event){
    	marker.config.css('opacity', 0.5);
    });

    google.maps.event.addListener(marker,'mouseup',function(event){
    	marker.config.css('opacity', 1);
    });

    google.maps.event.addListener(marker,'dragend',function(event){
        marker.config.find('h6').text(marker.getPosition().toString());
        marker.config.find('.lat').val(marker.getPosition().lat());
        marker.config.find('.lng').val(marker.getPosition().lng());
    });
}

jQuery(window).on('load', function(){
	if( 'undefined' != typeof google ){

		initMap();


		jQuery.each(data.locations, function(k, v){
			placeMarker({lat:v.lat, lng:v.lng}, v);
		});
	}
});

jQuery(document).ready(function(){
	$('#clues').accordion({
		header: "> div > h6",
		collapsible: true
	})
	.sortable({
		axis: "y",
		handle: "h6",
		stop: function( event, ui ) {
			// IE doesn't register the blur when sorting
			// so trigger focusout handlers to remove .ui-state-focus
			ui.item.children( "h3" ).triggerHandler( "focusout" );

			// Refresh accordion to handle new order
			$( this ).accordion( "refresh" );
		}
	});

	jQuery.each(data.config, function(k, v){
		$('#'+k).val(v);
	});
});

function save_config(){
	console.log('clicked');
	var yes = window.confirm("Sure you want to save? All previous data excluding user accounts will be lost.");

	if( !yes ) return;


	var conf = {
		starting_time: $('#starting_time').val(),
		duration: $('#duration').val(),
		freez_time: $('#freez_time').val()
	}

	var locations = [];
	$('#clues .group').each(function(){
		var loc = {
			lat: $(this).find('.lat').val(),
			lng: $(this).find('.lng').val(),
			zoom: $(this).find('.zoom').val(),
			clue: $(this).find('.clue').val(),
			welcome_text: $(this).find('.welcome-text').val(),
			questions: $(this).find('.question-answers').val(),
		}

		locations.push(loc)
	})

	$('.saving').fadeIn();

	$.post(urls.config, { config: conf, locations: locations }, function(){
		$('.saving').hide();
		$('.saved').show();
		setTimeout(function(){
			$('.saved').fadeOut();
		}, 3000);
	});
}






//# sourceMappingURL=game-admin.js.map
