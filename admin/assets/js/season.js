Joomla.submitbutton = function(pressbutton) {
	var res = true;
	var validator = document.formvalidator;
	var form = $('adminForm');

	if (pressbutton == 'season.cancel') {
		Joomla.submitform(pressbutton);
		return;
	}

	// do field validation
	if (validator.validate(form.name) === false) {
		alert(Joomla.JText._('COM_JOOMLEAGUE_ADMIN_SEASON_CSJS_NO_NAME'));
		form.name.focus();		
		res = false;
	}
	if (res) {
		Joomla.submitform(pressbutton);
	} else {
		return false;
	}	
}