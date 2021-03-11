/**
 * this function copies the value of the first found form field to all other
 * fields with the same name
 * 
 * @param string
 *            from which field
 */
 
 /*
window
		.addEvent(
				'domready',
				function() {
					$$('tr.row-result')
							.each(
									function(row) {
										var matchid = row.id.substr(7);
										var cb = row
												.getElement('input[id^=cb]');
										if (cb) {
											// item is not checked out
											row.getElements('select').addEvent(
													'change', function() {
														cb.checked = true;
													});

											row.getElements('input').addEvent(
													'change', function() {
														if (this.id != cb.id) {
															cb.checked = true;
														}
													});

											// special for calendar
											// row.getElements('.calendar').addEvent('click',function()
											// { cb.checked=true; });

											// special for roster selection
											row
													.getElements(
															'select[id^=team]')
													.addEvent(
															'change',
															function() {
																// handles the
																// link ref for
																// starting
																// lineup window
																var matchid = this.id
																		.substr(10);
																$$('a.openroster-'
																		+ this.id).href = "index.php?option=com_joomleague&tmpl=component&task=match.editlineup&cid[]="
																		+ matchid
																		+ "&team="
																		+ this.value;
															});
										} else {
											// item is checked out
										}
										// alert(matchid);
										// we should replace all the inline
										// 'onchange' with code here.

									});
				});
*/
function switchMenu(obj) {
	var el = document.getElementById(obj);
	if (el.style.display != "none") {
		el.style.display = 'none';
	} else {
		el.style.display = 'block';
	}
}

function copymatches() {
	document.getElementById('addtype').value = 2;
	document.copyform.submit();
	return true;
}

function addmatches() {
	document.getElementById('addmatchescount').value = document
			.getElementById('tempaddmatchescount').value;
	document.getElementById('addtype').value = 1;
	return true;
}

function displayTypeView() {
	if (document.getElementById('ct').value == 0) {
		document.getElementById('massadd_standard').style.display = 'none';
		document.getElementById('massadd_type2').style.display = 'none';
	} else if (document.getElementById('ct').value == 1) {
		document.getElementById('massadd_standard').style.display = 'block';
		document.getElementById('massadd_type2').style.display = 'none';
	} else if (document.getElementById('ct').value == 2) {
		document.getElementById('massadd_standard').style.display = 'none';
		document.getElementById('massadd_type2').style.display = 'block';
	}
}

function SaveMatch(a, b) {
	var f = document.matrixForm;
	if (f) {
		f.elements['projectteam1_id'].value = a;
		f.elements['projectteam2_id'].value = b;
		f.submit();
	}
}

function copyValue(from) {
	var fields = document.adminForm.elements;
	var default_time = '';
	for ( var i = 0; i < fields.length; i++) {
		var ele = fields[i];
		if (ele.name.indexOf(from) != -1) {
			if (default_time == '') {
				default_time = ele.value;
			} else {
				ele.value = default_time;
			}
			ele.onchange();
		}
	}
}

function handleRosterIconClick(type, ele, alert1, alert2) {
	if(!type) type = 0;
	if (type == '3') {
		if (!askPrefillRosterByLastMatch(alert1)) {
			if (askPrefillRosterByProjectTeamPlayer(alert2)) {
				ele.href = ele.href.substr(0, ele.href.indexOf('prefill'))
						+ 'prefill=2';
				return true;
			}
		} else {
			ele.href = ele.href.substr(0, ele.href.indexOf('prefill'))
					+ 'prefill=1';
			return true;
		}
	}
	ele.href = ele.href.substr(0, ele.href.indexOf('prefill')) + 'prefill=0';
	return true;
}

function askPrefillRosterByLastMatch(alert1) {
	return confirm(alert1);
}

function askPrefillRosterByProjectTeamPlayer(alert2) {
	return confirm(alert2);
}