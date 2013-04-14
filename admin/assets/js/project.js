Joomla.submitbutton = function(task) {
	if (task == 'project.cancel') {
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
    	var msg = new Array();
		// do field validation
		if (validator.validate(form.name) === false) {
			msg.push(Joomla.JText._('COM_JOOMLEAGUE_ADMIN_PROJECT_CSJS_ERROR_NAME'));
		}
		if (validator.validate(form['season_id']) === false
				&& form['season_id'].disabled != true
				|| (form.seasonNew && form.seasonNew.disabled == false && form.seasonNew.value == "")) {
			msg.push(Joomla.JText._('COM_JOOMLEAGUE_ADMIN_PROJECT_CSJS_ERROR_LEAGUE_NAME'));
		}
		if ((validator.validate(form['league_id']) === false && form['league_id'].disabled != true)
				|| (form.leagueNew && form.leagueNew.disabled == false && form.leagueNew.value == "")) {
			msg.push(Joomla.JText._('COM_JOOMLEAGUE_ADMIN_PROJECT_CSJS_ERROR_SEASON_NAME'));
		}
		if (validator.validate(form['sports_type_id']) === false
				&& form['sports_type_id'].disabled != true
				|| (form.seasonNew && form.seasonNew.disabled == false && form.seasonNew.value == "")) {
			msg.push(Joomla.JText._('COM_JOOMLEAGUE_ADMIN_PROJECT_CSJS_ERROR_SPORT_TYPE'));
		}
		if (form['joomleague_admin'] && form['joomleague_admin'].value === 0) {
			msg.push(Joomla.JText._('COM_JOOMLEAGUE_ADMIN_PROJECT_CSJS_ERROR_ADMIN'));
		}
		if (form['joomleague_editor'] && form['joomleague_editor'].value === 0) {
			msg.push(Joomla.JText._('COM_JOOMLEAGUE_ADMIN_PROJECT_CSJS_ERROR_MATCHDAY'));
		}
		if (form['start_time'] && validator.validate(form['start_time']) === false) {
			msg.push(Joomla.JText._('COM_JOOMLEAGUE_ADMIN_PROJECT_CSJS_ERROR_MATCHTIME'));
		}
		if (form['start_date'] && validator.validate(form['start_date']) === false) {
			msg.push(Joomla.JText._('COM_JOOMLEAGUE_ADMIN_PROJECT_CSJS_ERROR_MATCHDATE'));
		}
		if (form['current_round'] && validator.validate(form['current_round']) === false) {
			msg.push(Joomla.JText._('COM_JOOMLEAGUE_ADMIN_PROJECT_CSJS_ERROR_MATCHDAY'));
		}
        alert (msg.join('\n'));
    }
};

function RoundAutoSwitch() {
	var form = $('adminForm');
	if (form['current_round_auto'].value == 0) {
		form['current_round'].readOnly = false;
		form['auto_time'].readOnly = true;
	} else {
		form['current_round'].readOnly = true;
		form['auto_time'].readOnly = false;
	}
};