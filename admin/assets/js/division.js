Joomla.submitbutton = function(task) {
	if (task == 'division.cancel') {
		Joomla.submitform(task);
		return;
	}
	var form = $('adminForm');
	var validator = document.formvalidator;
	
	if (validator.isValid(form)) {
		Joomla.submitform(task);
		return true;   
    }
    else {
		// do field validation
		if (validator.validate(form.name) === false) {
			alert(Joomla.JText._('COM_JOOMLEAGUE_ADMIN_DIVISION_CSJS_NO_NAME'));
			form.name.focus();
			res = false;
		} 
	}
}
