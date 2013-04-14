function handleMoveLeftToRight() {
	$('teamschanges_check').value = 1;
	move($('members'), $('prediction_members'));
	selectAll($('prediction_members'));
}

function handleMoveRightToLeft() {
	$('teamschanges_check').value = 1;
	move($('prediction_members'), $('members'));
	selectAll($('prediction_members'));
}