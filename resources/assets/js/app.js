var map;
function initMap() {
	$('.pac-container').remove();

	// Specify features and elements to define styles.
	var styleArray=[{featureType:"landscape",elementType:"all",stylers:[{hue:"#291306"},{saturation:80.2},{lightness:-80.8},{gamma:1}]},{featureType:"landscape.natural.landcover",elementType:"geometry.fill",stylers:[{color:"#FFF"},{visibility:"on"}]},{featureType:"poi",elementType:"all",stylers:[{hue:"#7f4c1c"},{saturation:54.2},{lightness:-40.4},{gamma:1}]},{featureType:"road.highway",elementType:"all",stylers:[{hue:"#514526"},{saturation:-19.8},{lightness:-1.8},{gamma:1}]},{featureType:"road.arterial",elementType:"all",stylers:[{hue:"#514526"},{saturation:72.4},{lightness:-32.6},{gamma:1}]},{featureType:"road.local",elementType:"all",stylers:[{hue:"#514526"},{saturation:74.4},{lightness:-18},{gamma:1}]},{featureType:"water",elementType:"all",stylers:[{hue:"#79B0C6"},{saturation:-63.2},{lightness:38},{gamma:1}]},{featureType:"water",elementType:"geometry.fill",stylers:[{visibility:"on"},{color:"#79B0C6"}]}];
	
	// Create a map object and specify the DOM element for display.
	var map = new google.maps.Map(document.getElementById('map'), {
		center: {lat: 24.9045, lng: 91.8611},
		scrollwheel: true,
		// Apply the map style array to the map.
		styles: styleArray,
		zoom: 8
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


	var markers = [];
	// Listen for the event fired when the user selects a prediction and retrieve
	// more details for that place.
	searchBox.addListener('places_changed', function() {
		var places = searchBox.getPlaces();

		if (places.length == 0) {
			return;
		}

		// Clear out the old markers.
		markers.forEach(function(marker) {
			marker.setMap(null);
		});
		markers = [];

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
			markers.push(new google.maps.Marker({
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
}

jQuery(document).ready(function($){

	/* =======================================================
		Initiate countdown timer
	   ======================================================= */
	if(typeof $.fn.countdown !== 'undefined'){
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
	}


});

jQuery(window).on('load resize', function(){
	var sibling_height = 0;
	$('.wrapper').siblings('.navbar, .footer').each(function(){
		sibling_height += $(this).height();
	});
	$('.wrapper').css('height', $(window).height() - sibling_height );

	if( 'undefined' != typeof google )
		initMap();
});

