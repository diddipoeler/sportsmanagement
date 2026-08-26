Joomla.submitbutton = function (pressbutton) {
	let res = true;
	const validator = document.formvalidator;
	const form = document.getElementById('adminForm');

	if (pressbutton === 'club.cancel') {
		Joomla.submitform(pressbutton);
		return;
	}

	// Do field validation.
	if (validator.validate(form.name) === false) {
		alert(Joomla.Text._('The club must have a name!'));
		form.name.focus();
		res = false;
	}

	if (res) {
		Joomla.submitform(pressbutton);
		return;
	}

	return false;
};
