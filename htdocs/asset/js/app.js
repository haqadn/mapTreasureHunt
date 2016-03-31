jQuery(document).ready(function(){
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
});