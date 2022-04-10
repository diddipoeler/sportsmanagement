//window.addEvent('domready', function() {
jQuery(document).ready(function($){
//	updatePlayerSelect();
	if($('team_id')) {
	//	$('team_id').addEvent('change', updatePlayerSelect);

		//ajax remove event
		$$('input.button-delete').addEvent('click', deleteevent);
        //ajax remove commentary
		$$('input.button-delete-commentary').addEvent('click', deletecommentary);

		// neues ereignis speichern
        $$('save-new-event').addEvent(
				'click',
				function() {
				    // diddipoeler
                    //var comment = 0;
				    var rowid = this.id.substr(5);
					var url = baseajaxurl + '&task=matches.saveevent&';
					var player = $('teamplayer_id').value;
					var event = $('event_type_id').value;
					var team = $('team_id').value;
					//var token = $('token').value;
					var time = $('event_time').value;
					//var doubleevents = $('double_events').value;
                    //var projecttime = $('projecttime').value;
          var notice = encodeURIComponent($('notice').value);
					var querystring = 'teamplayer_id=' + player +
					'&projectteam_id=' + team + 
					'&event_type_id=' + event + 
					'&event_time=' + time + 
					'&match_id=' + matchid +
          '&projecttime=' + projecttime + 
					'&event_sum=' + $('event_sum').value +
					'&notice=' + notice + 
					'&doubleevents=' + doubleevents
					//+ '&'
					//+ token
					;
					console.log('baseajaxurl ' + baseajaxurl);
					console.log('url ' + url);
					console.log('querystring ' + querystring);
					if (team != 0 && event != 0) {
						var myXhr = new Request.JSON( {
							url: url + querystring,
							method : 'post',
							onRequest : reqsent,
							onFailure : reqfailed,
							onSuccess : eventsaved,
                            rowid: rowid
						});
						myXhr.post();
					}
				});
	}
    
    // neuen kommentar speichern
    $$('save-new-comment').addEvent(
			'click',
			function() {
				var url = baseajaxurl + '&task=matches.savecomment';
				var player = 0;
                // diddipoeler
                //var comment = 1;
                var rowid = this.id.substr(5);
				var event = 0;
				var team = 0;
				var ctype = $('ctype').value;
                var comnt = encodeURIComponent($('notes').value)
				var time = $('c_event_time').value;
				var querystring = '&teamplayer_id=' + player
				+ '&projectteam_id=' + team + '&event_type_id='
				+ event + '&event_time=' + time + '&match_id='
				+ matchid + '&event_sum='
				+ ctype + '&notes='
				+ comnt +
          '&projecttime=' + projecttime;
				if (ctype != 0 ) {
					var myXhr = new Request.JSON( {
						url: url + querystring,
						method : 'post',
						onRequest : reqsent,
						onFailure : reqfailed,
						onSuccess : commentsaved,
                        rowid: rowid
                        
					});
					myXhr.post();
				}
			});
	});

function reqsent() {
	$('ajaxresponse').addClass('ajax-loading');
    $('ajaxresponse').className = "";
	$('ajaxresponse').set('text','');
}

function reqfailed() {
	$('ajaxresponse').removeClass('ajax-loading');
	$('ajaxresponse').set('text',response);
}

function eventsaved(response) {
	$('ajaxresponse').removeClass('ajax-loading');
    $('ajaxresponse').className = "";
	// first line contains the status, second line contains the new row.
	var resp = response.split("&");
//    var showdebuginfo = $('showdebuginfo').value;
//    if ( showdebuginfo == 1 )
//    {
//        alert(resp[0]);
//        alert(resp[1]);
//    }
    
	if (resp[0] != '0') {
		// create new row in events table
		var newrow = new Element('tr', {
			id : 'row-' + resp[0]
		});
		new Element('td').set('text', $('team_id').options[$('team_id').selectedIndex].text)
				.injectInside(newrow);
		new Element('td')
				.set('text', $('teamplayer_id').options[$('teamplayer_id').selectedIndex].text)
				.injectInside(newrow);
		new Element('td')
				.set('text', $('event_type_id').options[$('event_type_id').selectedIndex].text)
				.injectInside(newrow);
		new Element('td').set('text',$('event_sum').value).injectInside(newrow);
		new Element('td').set('text',$('event_time').value).injectInside(newrow);
		new Element('td', {
			title : $('notice').value
		}).addClass("hasTip").set('text',trimstr($('notice').value, 20)).injectInside(newrow);
		var deletebutton = new Element('input', {
			id : 'delete-' + resp[0],
			type : 'button',
			value : str_delete
		}).addClass('inputbox button-delete').addEvent('click', deleteevent);
		new Element('td').appendChild(deletebutton).injectInside(newrow);
		newrow.injectBefore($('row-new'));
        $('ajaxresponse').className = "ajaxsuccess";
		$('ajaxresponse').set('text',resp[1]);
	} else {
	   $('ajaxresponse').className = "ajaxerror";
		$('ajaxresponse').set('text',resp[1]);
	}
}

function commentsaved(response) {
	$('ajaxresponse').removeClass('ajax-loading');
	// first line contains the status, second line contains the new row.
	var resp = response.split("&");
	if (resp[0] != '0') {
		// create new row in comments table
		var newrow = new Element('tr', {
			id : 'rowcomment-' + resp[0]
		});
		//new Element('td').injectInside(newrow);
        new Element('td').set('text', $('ctype').options[$('ctype').selectedIndex].text)
				.injectInside(newrow);
		new Element('td').set('text',$('c_event_time').value).injectInside(newrow);
		new Element('td', {
			title : $('notes').value
		}).addClass("hasTip").set('text',$('notes').value).injectInside(newrow);
		var deletebutton = new Element('input', {
			id : 'deletecomment-' + resp[0],
			type : 'button',
			value : str_delete
		}).addClass('inputbox button-delete-commentary').addEvent('click', deleteevent);
		new Element('td').appendChild(deletebutton).injectInside(newrow);
		newrow.injectBefore($('rowcomment-new'));
		
        $('ajaxresponse').className = "ajaxsuccess";
		$('ajaxresponse').set('text',resp[1]);
	} else {
		
        $('ajaxresponse').className = "ajaxerror";
		$('ajaxresponse').set('text',resp[1]);
	}
}

function deleteevent() {
	var eventid = this.id.substr(7);
    
//    alert(eventid);
    
	var url = baseajaxurl + '&task=matches.removeEvent';
	var querystring = '&event_id=' + eventid;
	if (eventid) {
		var myXhr = new Request.JSON( {
			url: url + querystring,
			method : 'post',
			onRequest : reqsent,
			onFailure : reqfailed,
			onSuccess : eventdeleted,
			rowid : eventid
		});
		myXhr.post();
	}
}


function deletecommentary() {
	var eventid = this.id.substr(14);
    
//    alert(eventid);
    
	var url = baseajaxurl + '&task=matches.removeCommentary';
	var querystring = '&event_id=' + eventid;
	if (eventid) {
		var myXhr = new Request.JSON( {
			url: url + querystring,
			method : 'post',
			onRequest : reqsent,
			onFailure : reqfailed,
			onSuccess : commentarydeleted,
			rowid : eventid
		});
		myXhr.post();
	}
}

function eventdeleted(response) {
	var resp = response.split("&");
    
//    alert(resp[0]);
//    alert(resp[1]);
//    alert(this.options.rowid);
    
	if (resp[0] != '0') {
		var currentrow = $('row-' + this.options.rowid);
		currentrow.dispose();
	}

	$('ajaxresponse').removeClass('ajax-loading');
    $('ajaxresponse').className = "ajaxsuccess";
	$('ajaxresponse').set('text',resp[1]);
}

function commentarydeleted(response) {
	var resp = response.split("&");

//    alert(resp[0]);
//    alert(resp[1]);
//    alert(this.options.rowid);

	if (resp[0] != '0') {
		var currentrow = $('rowcomment-' + this.options.rowid);
		currentrow.dispose();
	}

	$('ajaxresponse').removeClass('ajax-loading');
    $('ajaxresponse').className = "ajaxsuccess";
	$('ajaxresponse').set('text',resp[1]);
}

function updatePlayerSelect() {
	if($('cell-player'))
	$('cell-player').empty().appendChild(
			getPlayerSelect($('team_id').selectedIndex));
}
/**
 * return players select for specified team
 *
 * @param int )
 *            for home, 1 for away
 * @return dom element
 */
function getPlayerSelect(index) {
	// homeroster and awayroster must be defined globally (in the view calling
	// the script)
	var roster = rosters[index];
	// build select
	var select = new Element('select', {
		id : "teamplayer_id"
	});
	for ( var i = 0, n = roster.length; i < n; i++) {
		new Element('option', {
			value : roster[i].value
		}).set('text',roster[i].text).injectInside(select);
	}
	return select;
}

function trimstr(str, mylength) {
	return (str.length > mylength) ? str.substr(0, mylength - 3) + '...' : str;
}