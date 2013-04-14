Joomla.submitbutton = function(pressbutton) {
	var res = true;
	var form = $('adminForm');

	if (pressbutton == 'teamplayer.cancel') {
		Joomla.submitform(pressbutton);
		return;
	}
	
	if (res) {
		Joomla.submitform(pressbutton);
	} else {
		return false;
	}
}