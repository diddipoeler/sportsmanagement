Joomla.submitbutton = function(pressbutton) {
	var res = true;
	var validator = document.formvalidator;
	var form = $('adminForm');

	if (pressbutton == 'team.cancel') {
		Joomla.submitform(pressbutton);
		return;
	}

	// do field validation
	if (validator.validate(form.name) === false) {
		alert(Joomla.JText._('COM_JOOMLEAGUE_ADMIN_TEAM_CSJS_NO_NAME'));
		form.name.focus();
		res = false;
	} else if (validator.validate(form.short_name) === false) {
		alert(Joomla.JText._('COM_JOOMLEAGUE_ADMIN_TEAM_CSJS_NO_SHORTNAME'));
		form.short_name.focus();		
		res = false;
	} else if($('adminForm').club_id.selectedIndex == 0) {
		alert(Joomla.JText._('COM_JOOMLEAGUE_ADMIN_TEAM_CSJS_NO_CLUB'));
		form.clubs.focus();			
		res = false;
	}
	
	if (res) {
		Joomla.submitform(pressbutton);
	} else {
		return false;
	}
}
