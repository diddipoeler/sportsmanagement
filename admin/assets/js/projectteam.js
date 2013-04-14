Joomla.submitbutton = function(pressbutton) {
	var res = true;
	var form = $('adminForm');

	if (pressbutton == 'projectteam.cancel') {
		Joomla.submitform(pressbutton);
		return;
	}
	
	if (res) {
		Joomla.submitform(pressbutton);
	} else {
		return false;
	}
}