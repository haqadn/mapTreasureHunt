jQuery(document).ready(function($){

	/* =======================================================
		Initiate countdown timer
	   ======================================================= */
	if(typeof $.fn.countdown !== 'undefined'){
		$('.countdown').countdown({
			end_time: ts,
			show_day: false,
			wrapper: function( unit ){
				var wrapper = $('<div class="' + unit.toLowerCase() + '_wrapper col-md-4" />');
				wrapper.append('<span class="counter" />');
				wrapper.append('<span class="title">' + unit + '</span>');

				return wrapper;
			},
			onComplete: function(){
				location.reload();
			}
		});
	}

});

jQuery(window).on('load resize', function(){
	var sibling_height = 0;
	$('.wrapper').siblings('.navbar, .footer').each(function(){
		sibling_height += $(this).height();
	});
	$('.wrapper').css('min-height', $(window).height() - sibling_height );
});


//# sourceMappingURL=app.js.map
