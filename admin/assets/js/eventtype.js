Joomla.submitbutton = function(pressbutton) {
	var res = true;
	var validator = document.formvalidator;
	var form = $('adminForm');
	
	if (pressbutton == 'eventtype.cancel') {
		Joomla.submitform(pressbutton);
		return;
	}

	// do field validation
	if (validator.validate(form.name) === false) {
		alert(Joomla.JText._('COM_JOOMLEAGUE_ADMIN_EVENTTYPE_CSJS_NAME_REQUIRED'));
		form.name.focus();			
		res = false;
	}
	
	if (res) {
		Joomla.submitform(pressbutton);
	} else {
		return false;
	}
}

function updateEventIcon(path) {
	var icon = $('image');
	icon.src = '<?php echo JURI::root(); ?>' + path;
	icon.alt = path;
	icon.value = path;
	var logovalue = $('icon');
	logovalue.value = path;
}
