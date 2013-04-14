/**
* @copyright	Copyright (C) 2005-2013 JoomLeague.net. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
	window.addEvent('domready', function() {
		updatePlayerSelect();

		if($('team_id')) {
			$('team_id').addEvent('change', updatePlayerSelect);

			//ajax remove event
			$$('input.button-delete').addEvent('click', deleteevent);

			$('save-new-event').addEvent(
					'click',
					function() {
						var url = baseajaxurl + '&task=editevents.saveevent';
						var player = $('teamplayer_id').value;
						var event = $('event_type_id').value;
						var team = $('team_id').value;
						var time = $('event_time').value;
						if (team != 0 && event != 0) {
							var querystring = '&teamplayer_id=' + player
									+ '&projectteam_id=' + team + '&event_type_id='
									+ event + '&event_time=' + time + '&match_id='
									+ matchid + '&event_sum='
									+ $('event_sum').value + '&notice='
									+ $('notice').value;
							var myXhr = new Request.JSON( {
								url: url + querystring,
								method : 'post',
								onRequest : reqsent,
								onFailure : reqfailed,
								onSuccess : eventsaved
							});
							myXhr.post();
						}
					});
					
			
		}
        $('save-new-comment').addEvent(
					'click',
					function() {
						var url = baseajaxurl + '&task=editevents.saveevent';
						var player = 0;
						var event = 0;
						var team = 0;
						var ctype = $('ctype').value;
						var comnt = $('notes').value;
						var time = $('c_event_time').value;
						if (ctype != 0 && comnt != '') {
							var querystring = '&teamplayer_id=' + player
									+ '&projectteam_id=' + team + '&event_type_id='
									+ event + '&event_time=' + time + '&match_id='
									+ matchid + '&event_sum='
									+ ctype + '&notes='
									+ comnt;
							var myXhr = new Request.JSON( {
								url: url + querystring,
								method : 'post',
								onRequest : reqsent,
								onFailure : reqfailed,
								onSuccess : commentsaved
							});
							myXhr.post();
						}
					});
		});

	function reqsent() {
		$('ajaxresponse').addClass('ajax-loading');
		$('ajaxresponse').set('text', '');
	}

	function reqfailed(response) {
		$('ajaxresponse').removeClass('ajax-loading');
		$('ajaxresponse').set('text', response);
	}

	function eventsaved(response) {
		$('ajaxresponse').removeClass('ajax-loading');
		// first line contains the status, second line contains the new row.
		var resp = response.split("\n");
		if (resp[0] != '0') {
			// create new row in substitutions table
			var newrow = new Element('tr', {
				id : 'row-' + resp[0]
			});
			new Element('td')
					.set('text', $('team_id').options[$('team_id').selectedIndex].text)
					.injectInside(newrow);
			new Element('td')
					.set('text', $('teamplayer_id').options[$('teamplayer_id').selectedIndex].text)
					.injectInside(newrow);
			new Element('td')
					.set('text', $('event_type_id').options[$('event_type_id').selectedIndex].text)
					.injectInside(newrow);
			new Element('td').set('text', $('event_sum').value).injectInside(newrow);
			new Element('td').set('text', $('event_time').value).injectInside(newrow);
			new Element('td', {
				title : $('notice').value
			}).addClass("hasTip").set('text', trimstr($('notice').value, 20)).injectInside(newrow);
			var deletebutton = new Element('input', {
				id : 'delete-' + resp[0],
				type : 'button',
				value : str_delete
			}).addClass('inputbox button-delete').addEvent('click', deleteevent);
			new Element('td').appendChild(deletebutton).injectInside(newrow);
			newrow.injectBefore($('row-new-event'));
			$('ajaxresponse').set('text', 'Event saved').style.color='green';
		} else {
			$('ajaxresponse').set('text', resp[1]).style.color='red';
		}
	}
	
	function commentsaved(response) {
		$('ajaxresponse').removeClass('ajax-loading');
		// first line contains the status, second line contains the new row.
		var resp = response.split("\n");
		if (resp[0] != '0') {
			// create new row in comments table
			var newrow = new Element('tr', {
				id : 'row-' + resp[0]
			});
			new Element('td').injectInside(newrow);
			new Element('td').set('text', $('c_event_time').value).injectInside(newrow);
			new Element('td', {
				title : $('notes').value
			}).addClass("hasTip").set('text', $('notes').value).injectInside(newrow);
			var deletebutton = new Element('input', {
				id : 'delete-' + resp[0],
				type : 'button',
				value : str_delete
			}).addClass('inputbox button-delete').addEvent('click', deleteevent);
			new Element('td').appendChild(deletebutton).injectInside(newrow);
			newrow.injectBefore($('row-new-comment'));
			$('ajaxresponse').set('text', 'Comment saved').style.color='green';
		} else {
			$('ajaxresponse').set('text', resp[1]).style.color='red';
		}
	}

	function deleteevent() {
		var eventid = this.id.substr(7);

		var url = baseajaxurl + '&task=editevents.deleteevent';
		if (eventid) {
			var querystring = '&event_id=' + eventid;
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

	function eventdeleted(response) {
		var resp = response.split("\n");
		if (resp[0] != '0') {
			var currentrow = $('row-' + this.options.rowid);
			currentrow.dispose();
		}

		$('ajaxresponse').removeClass('ajax-loading');
		$('ajaxresponse').set('text', resp[1]);
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
				value : roster[i].value,
				text : roster[i].text
			}).injectInside(select);
		}
		return select;
	}

	function trimstr(str, mylength) {
		return (str.length > mylength) ? str.substr(0, mylength - 3) + '...' : str;
	}
