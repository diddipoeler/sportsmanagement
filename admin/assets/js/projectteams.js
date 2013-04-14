function handleMoveLeftToRight() {
	$('teamschanges_check').value = 1;
	move($('teamslist'), $('project_teamslist'));
	selectAll($('project_teamslist'));
}

function handleMoveRightToLeft() {
	$('teamschanges_check').value = 1;
	move($('project_teamslist'), $('teamslist'));
	selectAll($('project_teamslist'));
}