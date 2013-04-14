Joomla.submitbutton = function(pressbutton) {
	var form = $('adminForm');
	if (pressbutton == 'template.cancel') {
		Joomla.submitform(pressbutton);
		return;
	}

	// do field validation
	if (document.formvalidator.isValid(form)) {
		Joomla.submitform(pressbutton);
		return true;
	} else {
		alert(Joomla.JText._('COM_JOOMLEAGUE_ADMIN_TEMPLATE_CSJS_WRONG_VALUES'));
	}
	return false;
}