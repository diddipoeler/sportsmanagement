Joomla.submitbutton = function(pressbutton) {
	var res = true;
	var validator = document.formvalidator;
	var form = $('adminForm');

	if (pressbutton == 'person.cancel') {
		Joomla.submitform(pressbutton);
		return;
	}

	// do field validation
	if (validator.validate(form.lastname) === false) {
		alert(Joomla.JText._('COM_JOOMLEAGUE_ADMIN_PERSON_CSJS_NO_NAME'));
		res = false;
	}
	if (res) {
		Joomla.submitform(pressbutton);
	} else {
		return false;
	}
}