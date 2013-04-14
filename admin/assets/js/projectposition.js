Joomla.submitbutton = function(pressbutton) {
	var form = $('adminForm');
	if (pressbutton == 'projectposition.cancel') {
		Joomla.submitform(pressbutton);
		return;
	}
	if($('project_positionslist')) {
		var mylist = $('project_positionslist');
		for ( var i = 0; i < mylist.length; i++) {
			mylist[i].selected = true;
		}
	}
	Joomla.submitform(pressbutton);
}

function handleLeftToRight() {
	$('positionschanges_check').value = 1;
	move($('positionslist'), $('project_positionslist'));
	selectAll($('project_positionslist'));
}

function handleRightToLeft() {
	$('positionschanges_check').value = 1;
	move($('project_positionslist'), $('positionslist'));
	selectAll($('project_positionslist'));
}
