gcjQuery(document).ready(function() {
	gcjQuery(document).on('click', '#gcalendar_component_date_picker_button', function(e) {
		gcjQuery('#gcalendar_component_date_picker').datepicker('show');
	});
	gcjQuery(document).on('click', '#gcalendar_component_print_button', function(e) {
		var loc=document.location.href.replace(/\?/,"\?layout=print&format=raw\&");
		if (loc==document.location.href)
			loc=document.location.href.replace(/#/,"\?layout=print&format=raw#");
		var printWindow = window.open(loc);
		printWindow.focus();
	});
	gcjQuery('#gcalendar_view_toggle_status').bind('click', function(e) {
		gcjQuery('#gcalendar_view_list').slideToggle('slow', function(){
			var oldImage = gcjQuery('#gcalendar_view_toggle_status').attr('src');
			var gcalImage = oldImage;
			var path = oldImage.substring(0, oldImage.lastIndexOf('/'));
			
			if (gcjQuery('#gcalendar_view_list').is(":hidden"))
				gcalImage = path + '/down.png';
			else
				gcalImage = path + '/up.png';
			
			gcjQuery('#gcalendar_view_toggle_status').attr('src', gcalImage);
		});
	});
});

function updateGCalendarFrame(calendar) {
	if (calendar.checked) {
		gcjQuery('#gcalendar_component').fullCalendar('addEventSource', calendar.value);
	} else {
		gcjQuery('#gcalendar_component').fullCalendar('removeEventSource', calendar.value);
	}
}