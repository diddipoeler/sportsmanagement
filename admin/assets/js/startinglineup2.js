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
		{
		$$('input.move-right').addEvent('click', function() {
			$('changes_check').value=1;
			var posid = this.id.substr(10);
			move($('roster'), $('position'+posid));
		});

		$$('input.move-left').addEvent('click', function() {
			$('changes_check').value=1;
			var posid = this.id.substr(9);
			move($('position'+posid),$('roster'));
		});

		$$('input.move-up').addEvent('click', function() {
			$('changes_check').value=1;
			var posid = this.id.substr(7);
			moveOptionUp('position'+posid);
		});

		$$('input.move-down').addEvent('click', function() {
			$('changes_check').value=1;
			var posid = this.id.substr(9);
			moveOptionDown('position'+posid);
		});

		// home staff move right
		$$('input.hs-move-right').addEvent('click', function() {
			$('changes_check').value=1;
			var posid = this.id.substr(14);
			move($('hstaff'), $('hstaffposition'+posid));
		});

		// home staff move left
		$$('input.hs-move-left').addEvent('click', function() {
			$('changes_check').value=1;
			var posid = this.id.substr(13);
			move($('hstaffposition'+posid),$('hstaff'));
		});

		// home staff move up
		$$('input.hs-move-up').addEvent('click', function() {
			$('changes_check').value=1;
			var posid = this.id.substr(11);
			moveOptionUp('hstaffposition'+posid);
		});

		// home staff move down
		$$('input.hs-move-down').addEvent('click', function() {
			$('changes_check').value=1;
			var posid = this.id.substr(13);
			moveOptionDown('hstaffposition'+posid);
		});

		// home player move right
		$$('input.hp-move-right').addEvent('click', function() {
			$('changes_check').value=1;
			var posid = this.id.substr(14);
			move($('hplayer'), $('hplayerposition'+posid));
		});

		// home player move left
		$$('input.hp-move-left').addEvent('click', function() {
			$('changes_check').value=1;
			var posid = this.id.substr(13);
			move($('hplayerposition'+posid),$('hplayer'));
		});

		// home player move up
		$$('input.hp-move-up').addEvent('click', function() {
			$('changes_check').value=1;
			var posid = this.id.substr(11);
			moveOptionUp('hplayerposition'+posid);
		});

		// home player move down
		$$('input.hp-move-down').addEvent('click', function() {
			$('changes_check').value=1;
			var posid = this.id.substr(13);
			moveOptionDown('hplayerposition'+posid);
		});

		// away staff move right
		$$('input.as-move-right').addEvent('click', function() {
			$('changes_check').value=1;
			var posid = this.id.substr(14);
			move($('astaff'), $('astaffposition'+posid));
		});

		// away staff move left
		$$('input.as-move-left').addEvent('click', function() {
			$('changes_check').value=1;
			var posid = this.id.substr(13);
			move($('astaffposition'+posid),$('astaff'));
		});

		// away staff move up
		$$('input.as-move-up').addEvent('click', function() {
			$('changes_check').value=1;
			var posid = this.id.substr(11);
			moveOptionUp('astaffposition'+posid);
		});

		// away staff move down
		$$('input.as-move-down').addEvent('click', function() {
			$('changes_check').value=1;
			var posid = this.id.substr(13);
			moveOptionDown('astaffposition'+posid);
		});

		// away player move right
		$$('input.ap-move-right').addEvent('click', function() {
			$('changes_check').value=1;
			var posid = this.id.substr(14);
			move($('aplayer'), $('aplayerposition'+posid));
		});

		// away player move left
		$$('input.ap-move-left').addEvent('click', function() {
			$('changes_check').value=1;
			var posid = this.id.substr(13);
			move($('aplayerposition'+posid),$('aplayer'));
		});

		// away player move up
		$$('input.ap-move-up').addEvent('click', function() {
			$('changes_check').value=1;
			var posid = this.id.substr(11);
			moveOptionUp('aplayerposition'+posid);
		});

		// away player move down
		$$('input.ap-move-down').addEvent('click', function() {
			$('changes_check').value=1;
			var posid = this.id.substr(13);
			moveOptionDown('aplayerposition'+posid);
		});

		// match referee move right
		$$('input.mr-move-right').addEvent('click', function() {
			$('changes_check').value=1;
			var posid = this.id.substr(14);
			move($('referee'), $('refereeposition'+posid));
		});

		// match referee move left
		$$('input.mr-move-left').addEvent('click', function() {
			$('changes_check').value=1;
			var posid = this.id.substr(13);
			move($('refereeposition'+posid),$('referee'));
		});

		// match referee move up
		$$('input.mr-move-up').addEvent('click', function() {
			$('changes_check').value=1;
			var posid = this.id.substr(11);
			moveOptionUp('refereeposition'+posid);
		});

		// match referee move down
		$$('input.mr-move-down').addEvent('click', function() {
			$('changes_check').value=1;
			var posid = this.id.substr(13);
			moveOptionDown('refereeposition'+posid);
		});

		if(document.startingSquadsForm) {
			// on submit select all elements of select lists
			$('startingSquadsForm').addEvent('submit', function(event) {
				$$('select.position-starters').each( function(element) {
					selectAll(element);
				});

				$$('select.position-staff').each( function(element) {
					selectAll(element);
				});
			});
		}
		}

		// ajax save home substitution
		$$('input.button-save-homesubst').addEvent('click', function() {
			var url = baseajaxurl+'&task=savesubst';
			var rowid = this.id.substr(5);
			var playerin = $('home_in').value;
			var playerout = $('home_out').value;
			var position = $('home_project_position_id').value;
			var time = $('home_in_out_time').value;
			//alert('in: '+playerin+' / out: '+playerout+' / pos: '+position+' / time: '+time);
			if (playerin != 0 && playerout != 0 && position != 0) {
				var myXhr = new XHR(
										{
										method: 'post',
										onRequest: substRequest,
										onSuccess: substSavedHome,
										onFailure: substFailed,
										rowid: rowid
										}
				);
				var querystring = 'in=' +playerin
										+'&out='+playerout
										+'&project_position_id='+position
										+'&in_out_time='+time
										+'&matchid='+matchid;
				myXhr.send(url, querystring);
			}
			//alert(querystring);
			//alert('in: '+playerin+' / out: '+playerout);
		});

		// ajax save away substitution
		$$('input.button-save-awaysubst').addEvent('click', function() {
			var url = baseajaxurl+'&task=savesubst';
			var rowid = this.id.substr(5);
			var playerin = $('away_in').value;
			var playerout = $('away_out').value;
			var position = $('away_project_position_id').value;
			var time = $('away_in_out_time').value;
			//alert('in: '+playerin+' / out: '+playerout+' / pos: '+position+' / time: '+time);
			if (playerin != 0 && playerout != 0 && position != 0) {
				var myXhr = new XHR(
										{
										method: 'post',
										onRequest: substRequest,
										onSuccess: substSavedAway,
										onFailure: substFailed,
										rowid: rowid
										}
				);
				var querystring = 'in=' +playerin
										+'&out='+playerout
										+'&project_position_id='+position
										+'&in_out_time='+time
										+'&matchid='+matchid;
				myXhr.send(url, querystring);
			}
			//alert(querystring);
			//alert('in: '+playerin+' / out: '+playerout+' / pos: '+position);
		});

		// ajax remove substitution
		$$('input.button-delete').addEvent('click', deletesubst);

	});

	function substRequest()
	{
		$('ajaxresponse').addClass('ajax-loading');
		$('ajaxresponse').setText('');
	}

	function deletesubst()
	{
		var substid = this.id.substr(7);
		//alert('remove '+substid);
		var url = baseajaxurl+'&task=removeSubst';
		if (substid) {
			var myXhr = new XHR(
									{
									method: 'post',
									onRequest: substRequest,
									onSuccess: substRemoved,
									onFailure: substFailed,
									substid: substid
									}
			);
			var querystring = 'substid='+substid;
			myXhr.send(url, querystring);
		}
	}

	function substSavedHome(response)
	{
		$('ajaxresponse').removeClass('ajax-loading');
		var currentrow = $('row-'+this.options.rowid);
		// first line contains the status, second line contains the new row.
		var resp = response.split("\n");
		if (resp[0] != '0') {
			// create new row in substitutions table
			var newrow = new Element('tr', {id:'sub-'+resp[0]});
			new Element('td').setText($('home_out').options[$('home_out').selectedIndex].text).injectInside(newrow);
			new Element('td').setText($('home_in').options[$('home_in').selectedIndex].text).injectInside(newrow);
			new Element('td').setText($('home_project_position_id').options[$('home_project_position_id').selectedIndex].text).injectInside(newrow);
			new Element('td').setText($('home_in_out_time').value).injectInside(newrow);
			var deletebutton = new Element('input', {id:'delete-'+resp[0], type:'button', value:str_delete}).addClass('inputbox button-delete').addEvent('click', deletesubst);
			var td = new Element('td').appendChild(deletebutton).injectInside(newrow);
			newrow.injectBefore(currentrow);
			$('ajaxresponse').setText('Substitution saved').style.color='green';
		}
		else {
			$('ajaxresponse').setText(resp[1]).style.color='red';
		}
	}

	function substSavedAway(response)
	{
		$('ajaxresponse').removeClass('ajax-loading');
		var currentrow = $('row-'+this.options.rowid);
		// first line contains the status, second line contains the new row.
		var resp = response.split("\n");
		if (resp[0] != '0') {
			// create new row in substitutions table
			var newrow = new Element('tr', {id:'sub-'+resp[0]});
			new Element('td').setText($('away_out').options[$('away_out').selectedIndex].text).injectInside(newrow);
			new Element('td').setText($('away_in').options[$('away_in').selectedIndex].text).injectInside(newrow);
			new Element('td').setText($('away_project_position_id').options[$('away_project_position_id').selectedIndex].text).injectInside(newrow);
			new Element('td').setText($('away_in_out_time').value).injectInside(newrow);
			var deletebutton = new Element('input', {id:'delete-'+resp[0], type:'button', value:str_delete}).addClass('inputbox button-delete').addEvent('click', deletesubst);
			var td = new Element('td').appendChild(deletebutton).injectInside(newrow);
			newrow.injectBefore(currentrow);
			$('ajaxresponse').setText('Substitution saved').style.color='green';
		}
		else {
			$('ajaxresponse').setText(resp[1]).style.color='red';
		}
	}

	function substFailed()
	{
		$('ajaxresponse').removeClass('ajax-loading');
		$('ajaxresponse').setText(response);
	}

	function substRemoved(response)
	{
		var resp = response.split("\n");
		if (resp[0] != '0') {
			var currentrow = $('sub-'+this.options.substid);
			currentrow.dispose();
		}

		$('ajaxresponse').removeClass('ajax-loading');
		$('ajaxresponse').setText(resp[1]);
	}

	function move(fbox, tbox) {
			 var arrFbox = new Array();
			 var arrTbox = new Array();
			 var arrLookup = new Array();
			 var i;
			 for(i=0; i<tbox.options.length; i++) {
						arrLookup[tbox.options[i].text] = tbox.options[i].value;
						arrTbox[i] = tbox.options[i].text;
			 }
			 var fLength = 0;
			 var tLength = arrTbox.length
			 for(i=0; i<fbox.options.length; i++) {
						arrLookup[fbox.options[i].text] = fbox.options[i].value;
						if(fbox.options[i].selected && fbox.options[i].value != "") {
								 arrTbox[tLength] = fbox.options[i].text;
								 tLength++;
						} else {
								 arrFbox[fLength] = fbox.options[i].text;
								 fLength++;
						}
			 }
			 fbox.length = 0;
			 tbox.length = 0;
			 var c;
			 for(c=0; c<arrFbox.length; c++) {
						var no = new Option();
						no.value = arrLookup[arrFbox[c]];
						no.text = arrFbox[c];
						fbox[c] = no;
			 }
			 for(c=0; c<arrTbox.length; c++) {
				var no = new Option();
				no.value = arrLookup[arrTbox[c]];
				no.text = arrTbox[c];
				tbox[c] = no;
			 }
	}

	function selectAll(box) {
		for(var i=0; i<box.options.length; i++) {
			box.options[i].selected = true;
		}
	}

	function moveOptionUp(selectId)
	{
		var selectList = document.getElementById(selectId);
		var selectOptions = selectList.getElementsByTagName('option');
		for (var i = 1; i < selectOptions.length; i++) {
			var opt = selectOptions[i];
			if (opt.selected) {
				selectList.removeChild(opt);
				selectList.insertBefore(opt, selectOptions[i - 1]);
				return true;
			}
		}
	}

	function moveOptionDown(selectId)
	{
		var selectList = document.getElementById(selectId);
		var selectOptions = selectList.getElementsByTagName('option');
		for (var i = 0; i < selectOptions.length-1; i++) {
			var opt = selectOptions[i];
			if (opt.selected) {
				var next = selectOptions[i + 1];
				selectList.removeChild(next);
				selectList.insertBefore(next, selectOptions[i]);
				return true;
			}
		}
	}
