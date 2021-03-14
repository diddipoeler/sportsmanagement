jQuery(document).ready(function($){
	if (!$$('input.move-right'))
		return;
	$$('input.move-right').addEvent('click', function() {
		$('changes_check').value = 1;
		var posid = this.id.substr(10);
		move($('roster'), $('position' + posid));
	});

	$$('input.move-left').addEvent('click', function() {
		$('changes_check').value = 1;
		var posid = this.id.substr(9);
		move($('position' + posid), $('roster'));
	});

	$$('input.move-up').addEvent('click', function() {
		$('changes_check').value = 1;
		var posid = this.id.substr(7);
		moveOptionUp('position' + posid);
	});

	$$('input.move-down').addEvent('click', function() {
		$('changes_check').value = 1;
		var posid = this.id.substr(9);
		moveOptionDown('position' + posid);
	});

	// staff
	$$('input.smove-right').addEvent('click', function() {
		$('changes_check').value = 1;
		var posid = this.id.substr(11);
		move($('staff'), $('staffposition' + posid));
	});

	$$('input.smove-left').addEvent('click', function() {
		$('changes_check').value = 1;
		var posid = this.id.substr(10);
		move($('staffposition' + posid), $('staff'));
	});

	$$('input.smove-up').addEvent('click', function() {
		$('changes_check').value = 1;
		var posid = this.id.substr(8);
		moveOptionUp('staffposition' + posid);
	});

	$$('input.smove-down').addEvent('click', function() {
		$('changes_check').value = 1;
		var posid = this.id.substr(10);
		moveOptionDown('staffposition' + posid);
	});

	if (document.startingSquadsForm) {
		// on submit select all elements of select lists
		$('startingSquadsForm').addEvent('submit', function(event) {
			$$('select.position-starters').each(function(element) {
				selectAll(element);
			});

			$$('select.position-staff').each(function(element) {
				selectAll(element);
			});
		});
	}
	// ajax save substitution
	$$('input.button-save').addEvent(
			'click',
			function() {
				var rowid = this.id.substr(5);
				var playerin = $('in').value;
				var playerout = $('out').value;
				var position = $('project_position_id').value;
				var time = $('in_out_time').value;
                //var projecttime = $('projecttime').value;
				var querystring = 'in=' + playerin + '&out=' + playerout
						+ '&project_position_id=' + position + '&in_out_time='
						+ time + '&teamid=' + teamid + '&matchid=' + matchid
						+ '&rowid=' + rowid + '&projecttime=' + projecttime;
				var url = baseajaxurl + '&task=match.savesubst&' + querystring;
				if (playerin != 0 || playerout != 0) {
					var myXhr = new Request.JSON({
						url : url,
						postBody : querystring,
						method : 'post',
						onRequest : substRequest,
						onSuccess : substSaved,
						onFailure : substFailed,
						rowid: rowid
					});
					myXhr.post();
				}
			});
	// ajax remove substitution
	$$('input.button-delete').addEvent('click', deletesubst);

});

function substRequest() {
	$('ajaxresponse').addClass('ajax-loading');
    $('ajaxresponse').className = "";
	$('ajaxresponse').innerHTML = '';
}

function deletesubst() {
	var substid = this.id.substr(7);
	var querystring = '&substid=' + substid;
	var url = baseajaxurl + '&task=match.removeSubst';
	if (substid) {
		var myXhr = new Request.JSON({
			url : url + querystring,
			method : 'post',
			onRequest : substRequest,
			onSuccess : substRemoved,
			onFailure : substFailed,
			substid: substid
		});
		myXhr.post();
	}
}

function substSaved(response) {
	$('ajaxresponse').removeClass('ajax-loading');
    $('ajaxresponse').className = "";
	var currentrow = $('row-' + this.options.rowid);
	// first line contains the status, second line contains the new row.
	var resp = response.split("&");
//    var showdebuginfo = $('showdebuginfo').value;
//    if ( showdebuginfo == 1 )
//    {
//        alert(resp[0]);
//        alert(resp[1]);
//    }
    
	if (resp[0] != '0') {
		// create new row in substitutions table
		var newrow = new Element('tr', {
			id : 'sub-' + resp[0]
		});
		if($('out').selectedIndex > 0) {
			new Element('td').set('text', $('out').options[$('out').selectedIndex].text).inject(newrow);
		} else {
			new Element('td').set('text', '').inject(newrow);
		}
		if($('in').selectedIndex > 0) {
			new Element('td').set('text', $('in').options[$('in').selectedIndex].text).inject(newrow);
		} else {
			new Element('td').set('text', '').inject(newrow);
		}
		new Element('td').set('text', $('project_position_id').options[$('project_position_id').selectedIndex].text).inject(newrow);
		new Element('td').set('text', $('in_out_time').value).inject(newrow);
		var deletebutton = new Element('input', {
			id : 'delete-' + resp[0],
			type : 'button',
			value : str_delete
		}).addClass('inputbox button-delete').addEvent('click', deletesubst);
		var td = new Element('td').inject(newrow).appendChild(deletebutton);
		newrow.inject(currentrow, 'before');
        $('ajaxresponse').className = "ajaxsuccess";
		$('ajaxresponse').set('text', resp[1]);
	} else {
	   $('ajaxresponse').className = "ajaxerror";
		$('ajaxresponse').set('text', resp[1]);
	}
}

function substFailed(response) {
	$('ajaxresponse').removeClass('ajax-loading');
	document.html.innerHTML = response.responseText || "";
}

function substRemoved(response) {
	var resp = response.split("&");
	if (resp[0] != '0') {
		var currentrow = $('sub-' + this.options.substid);
		currentrow.dispose();
	}

	$('ajaxresponse').removeClass('ajax-loading');
	$('ajaxresponse').innerHTML = resp[1];
}