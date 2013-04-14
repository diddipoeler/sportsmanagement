Joomla.submitbutton = function(pressbutton) {
	var res = true;
	var validator = document.formvalidator;
	var form = $('adminForm');

	if (pressbutton == 'playground.cancel') {
		Joomla.submitform(pressbutton);
		return;
	}

	// do field validation
	if (validator.validate(form.name) === false) {
		alert(Joomla.JText._('COM_JOOMLEAGUE_ADMIN_PLAYGROUND_CSJS_NO_NAME'));
		form.name.focus();		
		res = false;
	} else if (validator.validate(form.short_name) === false) {
		alert(Joomla.JText._('COM_JOOMLEAGUE_ADMIN_PLAYGROUND_CSJS_NO_S_NAME'));
		form.short_name.focus();		
		res = false;
	} 
	
	if (res) {
		Joomla.submitform(pressbutton);
	} else {
		return false;
	}	
}

function updateVenuePicture(name, path) {
	var icon = document.getElementById(name);
	icon.src = '<?php echo JURI::root(); ?>' + path;
	icon.alt = path;
	icon.value = path;
	var logovalue = document.getElementById('picture');
	logovalue.value = path;
}
