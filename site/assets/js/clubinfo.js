Joomla.submitbutton = function(pressbutton) {
	var res = true;
	var validator = document.formvalidator;
	var form = $('adminForm');

	if (pressbutton == 'club.cancel') {
		Joomla.submitform(pressbutton);
		return;
	}

	// do field validation
	if (validator.validate(form.name) === false) {
		alert(Joomla.JText._('The club must have a name!'));
		form.name.focus();		
		res = false;
	}
	if (res) {
		Joomla.submitform(pressbutton);
	} else {
		return false;
	}
};
