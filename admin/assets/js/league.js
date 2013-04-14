Joomla.submitbutton = function(pressbutton) {
	var res = true;
	var validator = document.formvalidator;
	var form = $('adminForm');

	if (pressbutton == 'league.cancel') {
		Joomla.submitform(pressbutton);
		return;
	}

	// do field validation
	if (validator.validate(form.name) === false) {
		alert(Joomla.JText._('COM_JOOMLEAGUE_ADMIN_LEAGUE_CSJS_NO_NAME'));
		form.name.focus();		
		res = false;
	} else if (validator.validate(form.short_name) === false) {
		alert(Joomla.JText._('COM_JOOMLEAGUE_ADMIN_LEAGUE_CSJS_NO_SHORT_NAME'));
		form.short_name.focus();			
		res = false;
	}

	if (res) {
		Joomla.submitform(pressbutton);
	} else {
		return false;
	}
}
