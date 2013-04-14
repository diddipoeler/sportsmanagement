function stripslashes(str)
{
	str = str.replace(/\\'/g, '\'');
	str = str.replace(/\\"/g, '"');
	str = str.replace(/\\0/g, '\0');
	str = str.replace(/\\\\/g, '\\');
	return str;
}

function trim(stringToTrim)
{
	return stringToTrim.replace(/^\s+|\s+$/g,"");
}

// TODO: When deselecting the checkbox, revert to the previous state or determine automatically which radiobutton to select
function checkAllNewClubTeam(n)
{
	var f=document.adminForm;
	var c=f.toggleTeamsClubs.checked;
	selectionType = 10;
	for (i=0; i < n; i++)
	{
		nct=eval('f.ncnt_' + i);
		if (nct)
		{
			nct.checked=c;
			if (c)
			{
				resetToImportValues(i, selectionType);
			}
		}
	}
}

// In case the user wants to select a value from the database but the database has no entries, 
// an alert is shown and the selection is reset (e.g. if no club in database and user wants to select an existing club,
// the radiobutton can be pressed, but it is set to new club and new team after identiying that there is no club in the db).
function alertAndRestore(key, selectionType, msg)
{
	switch (selectionType)
	{
		case 10:
			alert(msg);
			eval('document.adminForm.ncnt_' + key + '.checked = true;');
			break;
	}
}

function checkAllPlaygrounds(n,fldName)
{
	if (!fldName) { fldName='pl'; }

	var f=document.adminForm;
	var c=f.togglePlaygrounds.checked;
	var n5=0;

	for (i=0; i < n; i++)
	{
		pl=eval('f.' + fldName + '' + i);
		if (pl)
		{
			pl.checked=c;
			testPlaygroundData(pl,pl.value);
			n5++;
		}
	}
}

function testPlaygroundData(box,id)
{
	if (box.checked)
	{
		eval("document.adminForm.choosePlayground_"+id+".disabled=true");
		eval("document.adminForm.choosePlayground_"+id+".checked=false");
		eval("document.adminForm.selectPlayground_"+id+".disabled=true");
		eval("document.adminForm.selectPlayground_"+id+".checked=false");
		eval("document.adminForm.createPlayground_"+id+".disabled=false");
		eval("document.adminForm.createPlayground_"+id+".checked=true");
		eval("document.adminForm.playgroundName_"+id+".disabled=false");
		eval("document.adminForm.playgroundShortname_"+id+".disabled=false");
		eval("document.adminForm.playgroundID_"+id+".disabled=false");
	}
	else
	{
		if (eval("document.adminForm.selectPlayground_"+id+".value!=''"))
		{
			eval("document.adminForm.choosePlayground_"+id+".disabled=false");
		}
		eval("document.adminForm.selectPlayground_"+id+".disabled=false");
		eval("document.adminForm.selectPlayground_"+id+".checked=false");
		eval("document.adminForm.createPlayground_"+id+".checked=false");
		eval("document.adminForm.playgroundName_"+id+".disabled=true");
		eval("document.adminForm.playgroundShortname_"+id+".disabled=true");
		eval("document.adminForm.playgroundID_"+id+".disabled=true");
	}
}

function checkAllEvents(n,fldName)
{
	if (!fldName) { fldName='ev'; }

	var f=document.adminForm;
	var c=f.toggleEvents.checked;
	var n5=0;

	for (i=0; i < n; i++)
	{
		ev=eval('f.' + fldName + '' + i);
		if (ev)
		{
			ev.checked=c;
			testEventsData(ev,ev.value);
			n5++;
		}
	}
}

function testEventsData(box,id)
{
	if (box.checked)
	{
		eval("document.adminForm.chooseEvent_"+id+".disabled=true");
		eval("document.adminForm.chooseEvent_"+id+".checked=false");
		eval("document.adminForm.selectEvent_"+id+".disabled=true");
		eval("document.adminForm.selectEvent_"+id+".checked=false");
		eval("document.adminForm.createEvent_"+id+".disabled=false");
		eval("document.adminForm.createEvent_"+id+".checked=true");
		eval("document.adminForm.eventName_"+id+".disabled=false");
		eval("document.adminForm.eventID_"+id+".disabled=false");
	}
	else
	{
		if (eval("document.adminForm.selectEvent_"+id+".value!=''"))
		{
			eval("document.adminForm.chooseEvent_"+id+".disabled=false");
		}
		eval("document.adminForm.selectEvent_"+id+".disabled=false");
		eval("document.adminForm.selectEvent_"+id+".checked=false");
		eval("document.adminForm.createEvent_"+id+".checked=false");
		eval("document.adminForm.eventName_"+id+".disabled=true");
		eval("document.adminForm.eventID_"+id+".disabled=true");
	}
}

function checkAllParentPositions(n,fldName)
{
	if (!fldName) { fldName='pp'; }

	var f=document.adminForm;
	var c=f.toggleParentPositions.checked;
	var n5=0;

	for (i=0; i < n; i++)
	{
		pp=eval('f.' + fldName + '' + i);
		if (pp) {
			pp.checked=c;
			testParentPositionsData(pp,pp.value);
			n5++;
		}
	}
}

function testParentPositionsData(box,id)
{
	if (box.checked)
	{
		eval("document.adminForm.chooseParentPosition_"+id+".disabled=true");
		eval("document.adminForm.chooseParentPosition_"+id+".checked=false");
		eval("document.adminForm.selectParentPosition_"+id+".disabled=true");
		eval("document.adminForm.selectParentPosition_"+id+".checked=false");
		eval("document.adminForm.createParentPosition_"+id+".disabled=false");
		eval("document.adminForm.createParentPosition_"+id+".checked=true");
		eval("document.adminForm.parentPositionName_"+id+".disabled=false");
		eval("document.adminForm.parentPositionID_"+id+".disabled=false");
	}
	else
	{
		if (eval("document.adminForm.selectParentPosition_"+id+".value!=''"))
		{
			eval("document.adminForm.chooseParentPosition_"+id+".disabled=false");
		}
		eval("document.adminForm.selectParentPosition_"+id+".disabled=false");
		eval("document.adminForm.selectParentPosition_"+id+".checked=false");
		eval("document.adminForm.createParentPosition_"+id+".checked=false");
		eval("document.adminForm.parentPositionName_"+id+".disabled=true");
		eval("document.adminForm.parentPositionID_"+id+".disabled=true");
	}
}

function checkAllPositions(n,fldName)
{
	if (!fldName) { fldName='po'; }

	var f=document.adminForm;
	var c=f.togglePositions.checked;
	var n5=0;

	for (i=0; i < n; i++)
	{
		po=eval('f.' + fldName + '' + i);
		if (po)
		{
			po.checked=c;
			testPositionsData(po,po.value);
			n5++;
		}
	}
}

function testPositionsData(box,id)
{
	if (box.checked)
	{
		eval("document.adminForm.choosePosition_"+id+".disabled=true");
		eval("document.adminForm.choosePosition_"+id+".checked=false");
		eval("document.adminForm.selectPosition_"+id+".disabled=true");
		eval("document.adminForm.selectPosition_"+id+".checked=false");
		eval("document.adminForm.createPosition_"+id+".disabled=false");
		eval("document.adminForm.createPosition_"+id+".checked=true");
		eval("document.adminForm.positionName_"+id+".disabled=false");
		eval("document.adminForm.positionID_"+id+".disabled=false");
	}
	else
	{
		if (eval("document.adminForm.selectPosition_"+id+".value!=''"))
		{
			eval("document.adminForm.choosePosition_"+id+".disabled=false");
		}
		eval("document.adminForm.selectPosition_"+id+".disabled=false");
		eval("document.adminForm.selectPosition_"+id+".checked=false");
		eval("document.adminForm.createPosition_"+id+".checked=false");
		eval("document.adminForm.positionName_"+id+".disabled=true");
		eval("document.adminForm.positionID_"+id+".disabled=true");
	}
}

function checkAllStatistics(n,fldName)
{
	if (!fldName) { fldName='st'; }

	var f=document.adminForm;
	var c=f.toggleStatistics.checked;
	var n5=0;

	for (i=0; i < n; i++)
	{
		st=eval('f.' + fldName + '' + i);
		if (st)
		{
			st.checked=c;
			testStatisticsData(st,st.value);
			n5++;
		}
	}
}

function testStatisticsData(box,id)
{
	if (box.checked)
	{
		eval("document.adminForm.chooseStatistic_"+id+".disabled=true");
		eval("document.adminForm.chooseStatistic_"+id+".checked=false");
		eval("document.adminForm.selectStatistic_"+id+".disabled=true");
		eval("document.adminForm.selectStatistic_"+id+".checked=false");
		eval("document.adminForm.createStatistic_"+id+".disabled=false");
		eval("document.adminForm.createStatistic_"+id+".checked=true");
		eval("document.adminForm.statisticName_"+id+".disabled=false");
		eval("document.adminForm.statisticID_"+id+".disabled=false");
	}
	else
	{
		if (eval("document.adminForm.selectStatistic_"+id+".value!=''"))
		{
			eval("document.adminForm.chooseStatistic_"+id+".disabled=false");
		}
		eval("document.adminForm.selectStatistic_"+id+".disabled=false");
		eval("document.adminForm.selectStatistic_"+id+".checked=false");
		eval("document.adminForm.createStatistic_"+id+".checked=false");
		eval("document.adminForm.statisticName_"+id+".disabled=true");
		eval("document.adminForm.statisticID_"+id+".disabled=true");
	}
}

function checkAllPersons(n,fldName)
{
	if (!fldName) { fldName='pe'; }

	var f=document.adminForm;
	var c=f.togglePersons.checked;
	var n5=0;

	for (i=0; i < n; i++)
	{
		pe=eval('f.' + fldName + '' + i);
		if (pe)
		{
			pe.checked=c;
			testPersonsData(pe,pe.value);
			n5++;
		}
	}
}

function testPersonsData(box,id)
{
	if (box.checked)
	{
		eval("document.adminForm.choosePerson_"+id+".disabled=true");
		eval("document.adminForm.choosePerson_"+id+".checked=false");
		eval("document.adminForm.selectPerson_"+id+".disabled=true");
		eval("document.adminForm.selectPerson_"+id+".checked=false");
		eval("document.adminForm.createPerson_"+id+".disabled=false");
		eval("document.adminForm.createPerson_"+id+".checked=true");

		eval("document.adminForm.personLastname_"+id+".disabled=false");
		eval("document.adminForm.personFirstname_"+id+".disabled=false");
		eval("document.adminForm.personNickname_"+id+".disabled=false");
		eval("document.adminForm.personBirthday_"+id+".disabled=false");
		eval("document.adminForm.personID_"+id+".disabled=false");
	}
	else
	{
		if (eval("document.adminForm.selectPerson_"+id+".value!=''"))
		{
			eval("document.adminForm.choosePerson_"+id+".disabled=false");
		}
		eval("document.adminForm.selectPerson_"+id+".disabled=false");
		eval("document.adminForm.selectPerson_"+id+".checked=false");
		eval("document.adminForm.createPerson_"+id+".checked=false");
		eval("document.adminForm.personLastname_"+id+".disabled=true");
		eval("document.adminForm.personFirstname_"+id+".disabled=true");
		eval("document.adminForm.personNickname_"+id+".disabled=true");
		eval("document.adminForm.personBirthday_"+id+".disabled=true");
		eval("document.adminForm.personID_"+id+".disabled=true");
	}
}

function openSelectWindow(recordid,key,selector,box,datatype)
{
	if (datatype==1){ // Team-Selector
	}
	else if (datatype==2){ // Club-Selector
	}
	else if (datatype==3){ // Person-Selector
		eval("document.adminForm.choosePerson_"+key+".checked=false");
		eval("document.adminForm.selectPerson_"+key+".checked=false");
		eval("document.adminForm.createPerson_"+key+".checked=false");
		eval("document.adminForm.personLastname_"+key+".disabled=true");
		eval("document.adminForm.personFirstname_"+key+".disabled=true");
		eval("document.adminForm.personNickname_"+key+".disabled=true");
		eval("document.adminForm.personBirthday_"+key+".disabled=true");
	}
	else if (datatype==4){ // Playground-Selector
		eval("document.adminForm.choosePlayground_"+key+".checked=false");
		eval("document.adminForm.selectPlayground_"+key+".checked=false");
		eval("document.adminForm.createPlayground_"+key+".checked=false");
		eval("document.adminForm.playgroundName_"+key+".disabled=true");
		eval("document.adminForm.playgroundShortname_"+key+".disabled=true");
	}
	else if (datatype==5){ // Event-Selector
		eval("document.adminForm.chooseEvent_"+key+".checked=false");
		eval("document.adminForm.selectEvent_"+key+".checked=false");
		eval("document.adminForm.createEvent_"+key+".checked=false");
		eval("document.adminForm.eventName_"+key+".disabled=true");
	}
	else if (datatype==6){ // Position-Selector
		eval("document.adminForm.choosePosition_"+key+".checked=false");
		eval("document.adminForm.selectPosition_"+key+".checked=false");
		eval("document.adminForm.createPosition_"+key+".checked=false");
		eval("document.adminForm.positionName_"+key+".disabled=true");
	}
	else if (datatype==7){ // ParentPosition-Selector
		eval("document.adminForm.chooseParentPosition_"+key+".checked=false");
		eval("document.adminForm.selectParentPosition_"+key+".checked=false");
		eval("document.adminForm.createParentPosition_"+key+".checked=false");
		eval("document.adminForm.parentPositionName_"+key+".disabled=true");
	}
	else if (datatype==8){ // Statistic-Selector
		eval("document.adminForm.chooseStatistic_"+key+".checked=false");
		eval("document.adminForm.selectStatistic_"+key+".checked=false");
		eval("document.adminForm.createStatistic_"+key+".checked=false");
		eval("document.adminForm.statisticName_"+key+".disabled=true");
	}
	else if (datatype==9){ // Club&Team-Selector
		// For now do nothing
	}
	else if (datatype==10){ // New Club-Selector
		// For now do nothing
	}
	//alert(datatype + "-" + recordid + "-" + key + "-" + selector + "-" + box);
	query='index.php?option=com_joomleague'
		+ '&tmpl=component'
		+ '&view=jlxmlimports'
		+ '&task=jlxmlimport.select'
		+ '&type=' + datatype
		+ '&id=' + key;

	// Center the popup on the screen
	popupWidth = 400;
	popupHeight = 400;
	screenWidth = screen.availWidth;
	screenHeight = screen.availHeight;
	selectWindow=window.open(query,'teamSelectWindow','width='+popupWidth+',height='+popupHeight+',scrollbars=yes,resizable=yes');
	selectWindow.moveTo((screenWidth-popupWidth)/2, (screenHeight-popupHeight)/2);
	selectWindow.focus();

	return false;
}

// When the radiobutton for club/team or club selection is pressed while 
// there are no clubs/teams, this function will restore to the imported values.
function resetToImportValues(key,datatype)
{
	if (datatype==10)
	{
		var adminform = document.forms['adminForm'];
		adminform['dbClubID_'+key].value = adminform['matching_ClubID_'+key].value;
		adminform['dbTeamID_'+key].value = adminform['matching_TeamID_'+key].value;
		adminform['dbTeamName_'+key].value = adminform['impTeamName_'+key].value;
		adminform['dbClubCountry_'+key].value = adminform['impClubCountry_'+key].value;
		adminform['dbClubName_'+key].value = adminform['impClubName_'+key].value;
	}
}

// Upon selection of a club and team in the popup, the appropriate input fields in the main
// import window are set properly and the popup is closed.
function insertClubAndTeam(anId)
{
	myteamID=this.document.selectorForm.teamID.value;
	var adminform = opener.document.forms['adminForm'];
	adminform['dbTeamID_'+anId].value=myteamID;
	adminform['matching_TeamID_'+anId].value=myteamID;
	adminform['dbTeamName_'+anId].value=stripslashes(clubsteams[myteamID].teamname);
	adminform['dbClubCountry_'+anId].value=stripslashes(clubsteams[myteamID].country);
	adminform['dbClubName_'+anId].value=stripslashes(clubsteams[myteamID].clubname);
	adminform['matching_ClubID_'+anId].value=stripslashes(clubsteams[myteamID].clubid);
	adminform['dbClubID_'+anId].value=stripslashes(clubsteams[myteamID].clubid);
	adminform['teamShortname_'+anId].disabled=true;
	adminform['teamMiddleName_'+anId].disabled=true;
	adminform['teamInfo_'+anId].disabled=true;
	adminform['matching_TeamID_'+anId].disabled=true;
	adminform['teamID_'+anId].disabled=true;
	adminform['dbTeamID_'+anId].disabled=false;
	adminform['teamFileID_'+anId].disabled=true;
	adminform['teamName_'+anId].disabled=true;
	adminform['dbClubID_'+anId].disabled=false;
	adminform['clubID_'+anId].disabled=true;
	adminform['clubFileID_'+anId].disabled=true;
	adminform['clubName_'+anId].disabled=true;
	adminform['clubCountry_'+anId].disabled=true;
	adminform['dbClubPlaygroundID_'+anId].disabled=false;	
	
	opener.focus();
	window.close();
	return false;
}

// Upon selection of a team in the popup, the appropriate input fields in the main 
// import window are set properly and the popup is closed.
function insertNewClub(anId)
{
	myclubID=this.document.selectorForm.clubID.value;
	var adminform = opener.document.forms['adminForm']; 
	
	adminform['matching_ClubID_'+anId].value=myclubID;
	adminform['dbClubID_'+anId].disabled=false;
	adminform['clubID_'+anId].disabled=true;
	adminform['clubFileID_'+anId].disabled=true;
	adminform['clubName_'+anId].disabled=true;
	adminform['clubCountry_'+anId].disabled=true;
	adminform['dbClubPlaygroundID_'+anId].disabled=false;	
	adminform['dbClubID_'+anId].value=myclubID;
	adminform['dbClubCountry_'+anId].value=stripslashes(clubs[myclubID].country);
	adminform['dbClubName_'+anId].value=stripslashes(clubs[myclubID].name);
	adminform['dbTeamName_'+anId].value = adminform['impTeamName_'+anId].value;
	
	opener.focus();
	window.close();
	return false;
}
