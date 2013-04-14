Joomla.submitbutton = function(task) {
	if (task == 'position.cancel') {
		Joomla.submitform(task);
		return;
	}
	var form = $('adminForm');
	var validator = document.formvalidator;
	
	if (validator.isValid(form)) {
		var mylist = $('position_eventslist');
		for ( var i = 0; i < mylist.length; i++) {
			mylist[i].selected = true;
		}
		var mylist = $('position_statistic');
		for ( var i = 0; i < mylist.length; i++) {
			mylist[i].selected = true;
		}
		Joomla.submitform(task);
		return true;   
    }
    else {
    	var msg = new Array();
		// do field validation
		if (validator.validate(form.name) === false) {
			msg.push(Joomla.JText._('COM_JOOMLEAGUE_ADMIN_POSITION_CSJS_NEEDS_NAME'));
		}
		if (validator.validate(form['sports_type_id']) === false
				&& form['sports_type_id'].disabled != true) {
			msg.push(Joomla.JText._('COM_JOOMLEAGUE_ADMIN_POSITION_CSJS_NEEDS_SPORTSTYPE'));
		}
        alert (msg.join('\n'));
    }
};

function moveLeftToRightEvents() {
	$('eventschanges_check').value = 1;
	move($('eventslist'), $('position_eventslist'));
	selectAll($('position_eventslist'));
}

function moveRightToLeftEvents() {
	$('eventschanges_check').value = 1;
	move($('position_eventslist'), $('eventslist'));
	selectAll($('position_eventslist'));
}

function moveLeftToRightStats() {
	$('statschanges_check').value = 1;
	move($('statistic'), $('position_statistic'));
	selectAll($('position_statistic'));
}

function moveRightToLeftStats() {
	$('statschanges_check').value = 1;
	move($('position_statistic'), $('statistic'));
	selectAll($('position_statistic'));
}