<?php defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.tooltip');
//$document =& JFactory::getDocument();
//$document->addScript( JURI::base() . 'components/com_joomleague/assets/js/JL_import.js' );

if (isset($this->xml) && is_array($this->xml))
{
	{
		//echo 'this<pre>'.print_r($this,true).'</pre>';
		if (array_key_exists('exportversion',$this->xml))
		{
			$exportversion =& $this->xml['exportversion'];
		}
		if (array_key_exists('project',$this->xml))
		{
			$proj =& $this->xml['project'];
		}
		if (array_key_exists('team',$this->xml))
		{
			$teams =& $this->xml['team'];
		}
		if (array_key_exists('club',$this->xml))
		{
			$clubs =& $this->xml['club'];
		}
		if (array_key_exists('playground',$this->xml))
		{
			$playgrounds =& $this->xml['playground'];
		}
		if (array_key_exists('league',$this->xml))
		{
			$league =& $this->xml['league'];
		}
		if (array_key_exists('season',$this->xml))
		{
			$season =& $this->xml['season'];
		}
		if (array_key_exists('sportstype',$this->xml))
		{
			$sportstype =& $this->xml['sportstype'];
		}
		if (array_key_exists('person',$this->xml))
		{
			$persons =& $this->xml['person'];
		}
		if (array_key_exists('event',$this->xml))
		{
			$events =& $this->xml['event'];
		}
		if (array_key_exists('position',$this->xml))
		{
			$positions =& $this->xml['position'];
		}
		if (array_key_exists('parentposition',$this->xml))
		{
			$parentpositions =& $this->xml['parentposition'];
		}
		if (array_key_exists('statistic',$this->xml))
		{
			$statistics =& $this->xml['statistic'];
		}
	}

	$xmlProjectImport=true;
	$xmlImportType='';
	if (!isset($proj))
	{
		$xmlProjectImport=false;
		if (isset($clubs))
		{
			$xmlImportType='clubs';	// There shouldn't be any problems with import of clubs-xml-export files
			$xmlImportTitle='Standart XML-Import of JoomLeague Clubs';
			$teamsClubs=$clubs;
		}
		elseif (isset($events)) // There shouldn't be any problems with import of events-xml-export files
		{
			$xmlImportType='events';
			$xmlImportTitle='Standart XML-Import of JoomLeague Events';
		}
		elseif (isset($positions))	// There shouldn't be any problems with import of positions-xml-export files
		{							// maybe the positions export routine should also export position_eventtype and events
			$xmlImportType='positions';
			$xmlImportTitle='Standart XML-Import of JoomLeague Positions';
		}
		elseif (isset($parentpositions))	// There shouldn't be any problems with import of positions-xml-export files
		{									// maybe the positions export routine should also export position_eventtype and events
			$xmlImportType='positions';
			$xmlImportTitle='Standart XML-Import of JoomLeague Positions';
		}
		elseif (isset($persons))	// There shouldn't be any problems with import of persons-xml-export files
		{
			$xmlImportType='persons';
			$xmlImportTitle='Standart XML-Import of JoomLeague Persons';
		}
		elseif (isset($playgrounds))	// There shouldn't be any problems with import of statistics-xml-export files
		{
			$xmlImportType='playgrounds';
			$xmlImportTitle='Standart XML-Import of JoomLeague Playgrounds';
		}
		elseif (isset($statistics)) // There shouldn't be any problems with import of statistics-xml-export files
		{							// maybe the statistic export routine should also export position_statistic and positions
			$xmlImportType='statistics';
			$xmlImportTitle='Standart XML-Import of JoomLeague Statistics';
		}
		elseif (isset($teams))	// There shouldn't be any problems with import of teams-xml-export files
		{
			$xmlImportType='teams';
			$xmlImportTitle='Standart XML-Import of JoomLeague Teams';
			$teamsClubs=$teams;
		}
		JError::raiseNotice(500,JText::_($xmlImportTitle));
	}
	else
	{
		$teamsClubs=$teams;
	}
	if (!empty($teamsClubs)){$teamsClubsCount=count($teamsClubs);}
?>
<script language="javascript" type="text/javascript"><!--

	function trim(stringToTrim)
	{
		return stringToTrim.replace(/^\s+|\s+$/g,"");
	}

	function checkAllCustom(n,fldName)
	{
		if (!fldName) { fldName='tc'; }

		var f=document.adminForm;
		var c=f.toggleTeamsClubs.checked;
		var n2=0;

		for (i=0; i < n; i++)
		{
			tc=eval('f.' + fldName + '' + i);
			if (tc)
			{
				tc.checked=c;
				testTeamsClubsData(tc,tc.value);
				n2++;
			}
		}
	}

	function testTeamsClubsData(box,id)
	{
		if (box.checked)
		{
			<?php
			if (!empty($teams))
			{
				?>
				eval("document.adminForm.chooseTeam_"+id+".disabled=true");
				eval("document.adminForm.chooseTeam_"+id+".checked=false");
				eval("document.adminForm.selectTeam_"+id+".disabled=true");
				eval("document.adminForm.selectTeam_"+id+".checked=false");
				eval("document.adminForm.createTeam_"+id+".disabled=false");
				eval("document.adminForm.createTeam_"+id+".checked=true");
				eval("document.adminForm.teamName_"+id+".disabled=false");
				eval("document.adminForm.teamShortname_"+id+".disabled=false");
				eval("document.adminForm.teamInfo_"+id+".disabled=false");
				eval("document.adminForm.teamMiddleName_"+id+".disabled=false");
				<?php
			}
			?>
			<?php
			if (!empty($clubs))
			{
				?>
				eval("document.adminForm.chooseClub_"+id+".disabled=true");
				eval("document.adminForm.chooseClub_"+id+".checked=false");
				eval("document.adminForm.selectClub_"+id+".disabled=true");
				eval("document.adminForm.selectClub_"+id+".checked=false");
				eval("document.adminForm.createClub_"+id+".disabled=false");
				eval("document.adminForm.createClub_"+id+".checked=true");
				eval("document.adminForm.clubName_"+id+".disabled=false");
				eval("document.adminForm.clubCountry_"+id+".disabled=false");
				eval("document.adminForm.clubID_"+id+".disabled=false");
				<?php
			}
			?>
		}
		else
		{
			<?php
			if (!empty($teams))
			{
				?>
				if (eval("document.adminForm.selectTeam_"+id+".value!=''")){
					eval("document.adminForm.chooseTeam_"+id+".disabled=false");
				}
				eval("document.adminForm.selectTeam_"+id+".disabled=false");
				eval("document.adminForm.selectTeam_"+id+".checked=false");
				eval("document.adminForm.createTeam_"+id+".checked=false");
				eval("document.adminForm.teamName_"+id+".disabled=true");
				eval("document.adminForm.teamShortname_"+id+".disabled=true");
				eval("document.adminForm.teamInfo_"+id+".disabled=true");
				eval("document.adminForm.teamMiddleName_"+id+".disabled=true");
				<?php
			}
			?>
			<?php
			if (!empty($clubs))
			{
				?>
				if (eval("document.adminForm.selectClub_"+id+".value!=''")){
					eval("document.adminForm.chooseClub_"+id+".disabled=false");
				}
				eval("document.adminForm.selectClub_"+id+".disabled=false");
				eval("document.adminForm.selectClub_"+id+".checked=false");
				eval("document.adminForm.createClub_"+id+".checked=false");
				eval("document.adminForm.clubName_"+id+".disabled=true");
				eval("document.adminForm.clubCountry_"+id+".disabled=true");
				eval("document.adminForm.clubID_"+id+".disabled=true");
				<?php
			}
			?>
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
			if (eval("document.adminForm.selectPlayground_"+id+".value!=''")){
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
			if (eval("document.adminForm.selectEvent_"+id+".value!=''")){
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
			if (eval("document.adminForm.selectParentPosition_"+id+".value!=''")){
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
			if (eval("document.adminForm.selectPosition_"+id+".value!=''")){
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
			if (eval("document.adminForm.selectStatistic_"+id+".value!=''")){
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
			if (eval("document.adminForm.selectPerson_"+id+".value!=''")){
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

	function chkFormular()
	{
		return true;
		var message='';

		<?php
		if (($xmlProjectImport) || ($xmlImportType=='events') || ($xmlImportType=='positions'))
		{
			?>
			if (((document.adminForm.sportstype.selectedIndex=='0') && (document.adminForm.sportstypeNew.disabled) &&
				(!document.adminForm.newSportsTypeCheck.checked)) ||
				((document.adminForm.sportstypeNew.disabled==false) && (trim(document.adminForm.sportstypeNew.value)=='')))
			{
				message+="<?php echo JText::_('Sports type is missing!'); ?>\n";
			}
			<?php
			if ($xmlProjectImport)
			{
				?>
				if (trim(document.adminForm.name.value)=='')
				{
					message+="<?php echo JText::_('Please select name of this project!'); ?>\n";
				}
				if (((document.adminForm.league.selectedIndex=='0') && (document.adminForm.leagueNew.disabled)) ||
					((document.adminForm.leagueNew.disabled==false) && (trim(document.adminForm.leagueNew.value)=='')))
				{
					message+="<?php echo JText::_('League is missing!'); ?>\n";
				}
				if (((document.adminForm.season.selectedIndex=='0') && (document.adminForm.seasonNew.disabled)) ||
					((document.adminForm.seasonNew.disabled==false) && (trim(document.adminForm.seasonNew.value)=='')))
				{
					message+="<?php echo JText::_('Season is missing!'); ?>\n";
				}
				<?php
			}
		}
		?>
		<?php
		if (isset($teams) && count($teams) > 0)
		{
			for ($counter=0; $counter < count($teams); $counter++)
			{
				?>
				if (((document.adminForm.chooseTeam_<?php echo $counter; ?>.checked==false) &&
					(document.adminForm.createTeam_<?php echo $counter; ?>.checked==false)) ||
					((trim(document.adminForm.teamName_<?php echo $counter; ?>.value)=='') ||
					(trim(document.adminForm.teamShortname_<?php echo $counter; ?>.value)=='') ||
					(trim(document.adminForm.teamMiddleName_<?php echo $counter; ?>.value)=='')))
				{
					message+='<?php echo JText::sprintf('No data selected for team [%1$s]',addslashes($teams[$counter]->name)); ?>\n';
				}
				<?php
			}
		}
		?>
		<?php
		if (isset($clubs) && count($clubs) > 0)
		{
			//$maxCounter=(!empty($teams)) ? count($teams) : count($teamsClubs);
			$maxCounter=((isset($clubs) && count($clubs) > 0)) ? count($clubs) : count($teams);
			//echo $maxCounter;
			for ($counter=0; $counter < $maxCounter; $counter++)
			{
				?>
				if (((document.adminForm.chooseClub_<?php echo $counter; ?>.checked==false) &&
					(document.adminForm.createClub_<?php echo $counter; ?>.checked==false)) ||
					((trim(document.adminForm.clubName_<?php echo $counter; ?>.value)=='') ||
					(trim(document.adminForm.clubCountry_<?php echo $counter; ?>.value)=='')))
				{
					message+='<?php echo JText::sprintf('No data selected for club [%1$s]',addslashes($clubs[$counter]->name)); ?>\n';
				}
				<?php
			}
		}
		?>
		<?php
		if ((isset($playgrounds)) && (count($playgrounds) > 0))
		{
			for ($counter=0; $counter < count($playgrounds); $counter++)
			{
				?>
				if (((document.adminForm.choosePlayground_<?php echo $counter; ?>.checked==false) &&
					(document.adminForm.createPlayground_<?php echo $counter; ?>.checked==false)) ||
					((trim(document.adminForm.playgroundName_<?php echo $counter; ?>.value)=='') ||
					(trim(document.adminForm.playgroundShortname_<?php echo $counter; ?>.value)=='')))
				{
					message+='<?php echo JText::sprintf('No data selected for playground [%1$s]',addslashes($playgrounds[$counter]->name)); ?>\n';
				}
				<?php
			}
		}
		?>
		<?php
		if ((isset($events)) && (count($events) > 0))
		{
			for ($counter=0; $counter < count($events); $counter++)
			{
				?>
				if (((document.adminForm.chooseEvent_<?php echo $counter; ?>.checked==false) &&
					(document.adminForm.createEvent_<?php echo $counter; ?>.checked==false)) ||
					(trim(document.adminForm.eventName_<?php echo $counter; ?>.value)==''))
				{
					message+='<?php echo JText::sprintf('No data selected for event [%1$s]',addslashes($events[$counter]->name)); ?>\n';
				}
				<?php
			}
		}
		?>
		<?php
		if ((isset($parentpositions)) && (count($parentpositions) > 0))
		{
			for ($counter=0; $counter < count($parentpositions); $counter++)
			{
				?>
				if (((document.adminForm.chooseParentPosition_<?php echo $counter; ?>.checked==false) &&
					(document.adminForm.createParentPosition_<?php echo $counter; ?>.checked==false)) ||
					(trim(document.adminForm.parentPositionName_<?php echo $counter; ?>.value)==''))
				{
					message+='<?php echo JText::sprintf('No data selected for parentposition [%1$s]',addslashes($parentpositions[$counter]->name)); ?>\n';
				}
				<?php
			}
		}
		?>
		<?php
		if ((isset($positions)) && (count($positions) > 0))
		{
			for ($counter=0; $counter < count($positions); $counter++)
			{
				?>
				if (((document.adminForm.choosePosition_<?php echo $counter; ?>.checked==false) &&
					(document.adminForm.createPosition_<?php echo $counter; ?>.checked==false)) ||
					(trim(document.adminForm.positionName_<?php echo $counter; ?>.value)==''))
				{
					message+='<?php echo JText::sprintf('No data selected for position [%1$s]',addslashes($positions[$counter]->name)); ?>\n';
				}
				<?php
			}
		}
		?>
		<?php
		if ((isset($statistics)) && (count($statistics) > 0))
		{
			for ($counter=0; $counter < count($statistics); $counter++)
			{
				?>
				if (((document.adminForm.chooseStatistic_<?php echo $counter; ?>.checked==false) &&
					(document.adminForm.createStatistic_<?php echo $counter; ?>.checked==false)) ||
					(trim(document.adminForm.statisticName_<?php echo $counter; ?>.value)==''))
				{
					message+='<?php echo JText::sprintf('No data selected for statistic [%1$s]',addslashes($statistics[$counter]->name)); ?>\n';
				}
				<?php
			}
		}
		?>
		<?php
		if ((isset($persons)) && (count($persons) > 0))
		{
			for ($counter=0; $counter < count($persons); $counter++)
			{
				?>
				if(document.adminForm.choosePerson_<?php echo $counter; ?>.checked==false) {
					if ((document.adminForm.createPerson_<?php echo $counter; ?>.checked==false)||
						((trim(document.adminForm.personLastname_<?php echo $counter; ?>.value)=='') ||
						(trim(document.adminForm.personFirstname_<?php echo $counter; ?>.value)=='')))
					{
						message+='<?php echo JText::sprintf('No data selected for person [%1$s,%2$s]',addslashes($persons[$counter]->lastname),addslashes($persons[$counter]->firstname)); ?>\n';
					}
				}
				<?php
			}
		}
		?>
		if (message=='')
		{
			return true;
		}
		else
		{
		  alert("<?php echo JText::_('JL_ADMIN_XML_IMPORT_ERROR'); ?>\n\n"+message);
		  return false;
		}
	}

	function openSelectWindow(recordid,key,selector,box,datatype)
	{
		if (datatype==1){ // Team-Selector
		eval("document.adminForm.chooseTeam_"+key+".checked=false");
		eval("document.adminForm.selectTeam_"+key+".checked=false");
		eval("document.adminForm.createTeam_"+key+".checked=false");
		eval("document.adminForm.teamName_"+key+".disabled=true");
		eval("document.adminForm.teamShortname_"+key+".disabled=true");
		eval("document.adminForm.teamInfo_"+key+".disabled=true");
		eval("document.adminForm.teamMiddleName_"+key+".disabled=true");
		}
		else if (datatype==2){ // Club-Selector
		eval("document.adminForm.chooseClub_"+key+".checked=false");
		eval("document.adminForm.selectClub_"+key+".checked=false");
		eval("document.adminForm.createClub_"+key+".checked=false");
		eval("document.adminForm.clubName_"+key+".disabled=true");
		eval("document.adminForm.clubCountry_"+key+".disabled=true");
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
		//alert(datatype + "-" + recordid + "-" + key + "-" + selector + "-" + box);
		query='index.php?option=com_joomleague&tmpl=component&view=jlxmlimports&controller=jlxmlimport'
				+ '&task=select'
				+ '&type=' + datatype
				+ '&id=' + key;
		//alert(query);
		selectWindow=window.open(query,'teamSelectWindow','width=600,height=100,scrollbars=yes,resizable=yes');
		selectWindow.focus();

		return false;
	}

//--></script>
	<div id='editcell'>
		<a name='page_top'></a>
		<table class='adminlist'>
			<thead><tr><th><?php echo JText::_('JL_ADMIN_XML_IMPORT_TABLE_TITLE_2'); ?></th></tr></thead>
			<tbody>
				<tr>
					<td style='text-align:center; '>
						<p style='text-align:center;'><b style='color:green; '><?php echo JText::sprintf('JL_ADMIN_XML_IMPORT_UPLOAD_SUCCESS','<i>'.$this->uploadArray['name'].'</i>'); ?></b></p>
						<?php
						if ($this->import_version!='OLD')
						{
							if (isset($exportversion->exportRoutine) &&
								strtotime($exportversion->exportRoutine) >= strtotime('2010-09-19 23:00:00'))
							{
								?>
								<p><?php
									echo JText::sprintf('This file was created using JoomLeague-Export-Routine dated: %1$s',$exportversion->exportRoutine).'<br />';
									echo JText::sprintf('Date and time of this file is: %1$s - %2$s',$exportversion->exportDate,$exportversion->exportTime).'<br />';
									echo JText::sprintf('The name of the Joomla-System where this file was created is: %1$s',$exportversion->exportSystem).'<br />';
									?></p><?php
							}
							else
							{
								?>
								<p><?php
									echo JText::_('This file was created by an older revision of JoomLeague 1.5.0a!').'<br />';
									echo JText::_('As we can not guarantee a correct processing the import routine will STOP here!!!');
									?></p></td></tr></tbody></table></div><?php
									return;
							}
						}
						?><p><?php echo JText::sprintf('JL_ADMIN_XML_IMPORT_HINT2',$this->revisionDate); ?></p>
						<p><?php echo JText::_('JL_ADMIN_XML_IMPORT_CREATE_CLUBS_HINT'); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
		<form name='adminForm' action='<?php echo $this->request_url; ?>' method='post' onsubmit='return chkFormular();' >
			<input type='hidden' name='importProject' value="<?php echo $xmlProjectImport; ?>" />
			<input type='hidden' name='importType' value="<?php echo $xmlImportType; ?>" />
			<input type='hidden' name='sent' value="2" id='sent' />
			<input type='hidden' name='controller' value="jlxmlimport" />
			<input type='hidden' name='task' value="insert" />
			<?php echo JHTML::_('form.token')."\n"; ?>
			<?php
			if (($xmlProjectImport) || ($xmlImportType=='events') || ($xmlImportType=='positions'))
			{
				?>
				<fieldset>
					<legend><strong><?php echo JText::_('JL_ADMIN_XML_IMPORT_GENERAL_DATA_LEGEND'); ?></strong></legend>
					<table class='adminlist'>
						<?php
						if (($xmlImportType!='events') && ($xmlImportType!='positions'))
						{
							?>
							<tr>
								<td style='background-color:#EEEEEE'><?php echo JText::_('JL_ADMIN_XML_IMPORT_PROJECT_NAME'); ?></td>
								<td style='background-color:#EEEEEE'>
									<input type='text' name='name' id='name' size='110' maxlength='100' value="<?php echo stripslashes(htmlspecialchars($proj->name)); ?>" />
								</td>
							</tr>
							<?php
						}
						?>
						<tr>
							<td style='background-color:#DDDDDD'><?php echo JText::_('JL_ADMIN_XML_IMPORT_SPORTSTYPE'); ?></td>
							<td style='background-color:#DDDDDD'>
								<?php
								if (isset($sportstype->name))
								{
									$dSportsTypeName=$sportstype->name;
								}
								else
								{
									$dSportsTypeName=$proj->name;
								}
								if (count($this->sportstypes) > 0)
								{
									?>
									<select name='sportstype' id='sportstype'>
										<option selected value="0"><?php echo JText::_('JL_ADMIN_XML_IMPORT_SPORTSTYPE_SELECT'); ?></option>
										<?php
										foreach ($this->sportstypes AS $row)
										{
											echo '<option ';
											if (($row->name==$dSportsTypeName) ||
												($row->name==JText::_($dSportsTypeName)) ||
												(count($this->sportstypes)==1))
											{
												echo "selected='selected' ";
											}
											echo "value='$row->id;'>";
											echo JText::_($row->name);
											echo '</option>';
										}
										?>
									</select>
									<br />
									<input	type='checkbox' name='newSportsTypeCheck' value="1"
											onclick="
											if (this.checked) {
												document.adminForm.sportstype.disabled=true;
												document.adminForm.sportstypeNew.disabled=false;
												document.adminForm.sportstypeNew.value=document.adminForm.sportstypeNew.value;
											} else {
												document.adminForm.sportstype.disabled=false;
												document.adminForm.sportstypeNew.disabled=true;
											}" /><?php echo JText::_('JL_ADMIN_XML_IMPORT_CREATE_NEW'); ?>
									<input type='text' name='sportstypeNew' size='30' maxlength='25' id='sportstypeNew' value="<?php echo stripslashes(htmlspecialchars(JText::_($dSportsTypeName))); ?>" disabled='disabled' />
								<?php
								}
								else
								{
									?>
									<input type="hidden" name="newSportsTypeCheck" value="1" />
									<?php echo JText::_('JL_ADMIN_XML_IMPORT_CREATE_NEW'); ?>
									<input type='text' name='sportstypeNew' size='30' maxlength='25' id='sportstypeNew' value="<?php echo stripslashes(htmlspecialchars(JText::_($dSportsTypeName))); ?>" />
									<?php
								}
								?>
							</td>
						</tr>
						<?php
						if (($xmlImportType!='events') && ($xmlImportType!='positions'))
						{
							?>
							<tr>
								<td style='background-color:#EEEEEE'><?php echo JText::_('JL_ADMIN_XML_IMPORT_LEAGUE'); ?></td>
								<td style='background-color:#EEEEEE'>
									<?php
									if (isset($league->name))
									{
										$dLeagueName=$league->name;
										$leagueCountry=$league->country;
									}
									else
									{
										$dLeagueName=$proj->name;
										$leagueCountry='';
									}
									$dCountry=$leagueCountry;
									if (preg_match('=^[0-9]+$=',$dCountry))
									{
										$dCountry=$this->OldCountries[(int)$dCountry];
									}
									?>
									<select name='league' id='league'>
										<option selected value="0"><?php echo JText::_('JL_ADMIN_XML_IMPORT_LEAGUE_SELECT'); ?></option>
										<?php
										if (count($this->leagues) > 0)
										{
											foreach ($this->leagues AS $row)
											{
												echo '<option ';
												//if ($row->name==$dLeagueName){echo "selected='selected' ";}
												if (($row->name==$dLeagueName)||(count($this->leagues)==1)){echo "selected='selected' ";}
												echo "'value='$row->id;'>";
												echo $row->name;
												echo '</option>';
											}
										}
										?>
									</select>
									<br />
									<?php
									if (count($this->leagues) < 1)
									{
										?>
										<input	checked='checked' type='checkbox' name='newLeagueCheck' value="1"
												onclick='this.checked=true;' /><?php echo JText::_('JL_ADMIN_XML_IMPORT_CREATE_NEW'); ?>
										<input	type='text' name='leagueNew' size='90' maxlength='75' id='leagueNew' value="<?php echo stripslashes(htmlspecialchars($dLeagueName)); ?>" disabled='disabled' />
										<script type="text/javascript">
											document.adminForm.newLeagueCheck.value=1;
											document.adminForm.league.disabled=true;
											document.adminForm.leagueNew.disabled=false;
											document.adminForm.leagueNew.value=document.adminForm.leagueNew.value;
										</script>
										<?php
									}
									else
									{
										?>
										<input	type='checkbox' name='newLeagueCheck' value="1"
												onclick="
												if (this.checked) {
													document.adminForm.league.disabled=true;
													document.adminForm.leagueNew.disabled=false;
													document.adminForm.leagueNew.value=document.adminForm.leagueNew.value;
												} else {
													document.adminForm.league.disabled=false;
													document.adminForm.leagueNew.disabled=true;
												}" /><?php echo JText::_('JL_ADMIN_XML_IMPORT_CREATE_NEW'); ?>
										<input type='text' name='leagueNew' size='90' maxlength='75' id='leagueNew' value="<?php echo stripslashes(htmlspecialchars($dLeagueName)); ?>" disabled='disabled' />
										<?php
									}
									?>
								</td>
							</tr>
							<tr>
								<td style='background-color:#DDDDDD'><?php echo JText::_('JL_ADMIN_XML_IMPORT_SEASON'); ?></td>
								<td style='background-color:#DDDDDD'>
									<?php
									if (isset($season->name))
									{
										$dSeasonName=$season->name;
									}
									else
									{
										$dSeasonName=$proj->name;
									}
									?>
									<select name='season' id='season'>
										<option selected value="0"><?php echo JText::_('JL_ADMIN_XML_IMPORT_SEASON_SELECT'); ?></option>
										<?php
										if (count($this->seasons) > 0)
										{
											foreach ($this->seasons AS $row)
											{
												echo '<option ';
												//if ($row->name==$dSeasonName){echo "selected='selected' ";}
												if (($row->name==$dSeasonName)||(count($this->seasons)==1)){echo "selected='selected' ";}
												echo "value='$row->id;'>";
												echo $row->name;
												echo '</option>';
											}
										}
										?>
									</select>
									<br />
									<?php
									if (count($this->leagues) < 1)
									{
										?>
										<input	checked='checked' type='checkbox' name='newSeasonCheck' value="1"
												onclick='this.checked=true;' /><?php echo JText::_('JL_ADMIN_XML_IMPORT_CREATE_NEW'); ?>
										<input	type='text' name='seasonNew' size='90' maxlength='75' id='seasonNew' value="<?php echo stripslashes(htmlspecialchars($dSeasonName)); ?>" disabled='disabled' />
										<script type="text/javascript">
											document.adminForm.newSeasonCheck.value=1;
											document.adminForm.season.disabled=true;
											document.adminForm.seasonNew.disabled=false;
											document.adminForm.seasonNew.value=document.adminForm.seasonNew.value;
										</script>
										<?php
									}
									else
									{
										?>
										<input	type='checkbox' name='newSeasonCheck' value="1"
												onclick="
												if (this.checked) {
													document.adminForm.season.disabled=true;
													document.adminForm.seasonNew.disabled=false;
													document.adminForm.seasonNew.value=document.adminForm.seasonNew.value;
												} else {
													document.adminForm.season.disabled=false;
													document.adminForm.seasonNew.disabled=true;
												}" /><?php echo JText::_('JL_ADMIN_XML_IMPORT_CREATE_NEW'); ?>
										<input type='text' name='seasonNew' size='90' maxlength='75' id='seasonNew' value="<?php echo stripslashes(htmlspecialchars($dSeasonName)); ?>" disabled='disabled' />
										<?php
									}
									?>
								</td>
							</tr>
							<tr>
								<td style='background-color:#EEEEEE'><?php echo JText::_('JL_ADMIN_XML_IMPORT_ADMIN'); ?></td>
								<td style='background-color:#EEEEEE'>
									<select name='admin' id='admin'>
										<?php
										foreach ($this->admins AS $row)
										{
											echo '<option ';
												if ($row->id==62){echo "selected='selected' ";}
												echo "value='$row->id;'>";
												echo $row->username;
											echo '</option>';
										}
										?>
									</select>
								</td>
							</tr>
							<tr>
								<td style='background-color:#DDDDDD'><?php echo JText::_('JL_ADMIN_XML_IMPORT_EDITOR'); ?></td>
								<td style='background-color:#DDDDDD'>
									<select name='editor' id='editor'>
										<?php
										foreach ($this->editors AS $row)
										{
											echo '<option ';
												if ($row->id==62){echo "selected='selected' ";}
												echo "value='$row->id;'>";
												echo $row->username;
											echo '</option>';
										}
										?>
									</select>
								</td>
							</tr>
							<tr>
								<td style='background-color:#EEEEEE'><?php echo JText::_('JL_ADMIN_XML_IMPORT_TEMPLATES'); ?></td>
								<td style='background-color:#EEEEEE'>
									<select name='copyTemplate' id='copyTemplate'>
										<option value="0" selected><?php echo JText::_('JL_ADMIN_XML_IMPORT_TEMPLATES_USEOWN'); ?></option>
										<?php
										foreach ($this->templates AS $row)
										{
											echo "<option value=\"$row->id\">$row->name</option>\n";
										}
										?>
									</select>
								</td>
							</tr>
							<tr>
								<td style='background-color:#DDDDDD'><?php echo JText::_('TimeOffset of this project'); ?></td>
								<td style='background-color:#DDDDDD'>
									<?php
									echo $this->lists['serveroffset'].'&nbsp;';
									$output1="<input type='text' name='acttime' id='acttime' size='4' value=\"".JHTML::date(time(),'%H:%M')."\" style='font-weight: bold;' disabled='disabled' />";
									echo sprintf(JText::_('JL_ADMIN_PROJECT_SERVER_ACTTIME'),$output1);
									?>
								</td>
							</tr>
							<tr>
								<td style='background-color:#EEEEEE'><?php echo JText::_('JL_ADMIN_XML_IMPORT_PUBLISH'); ?></td>
								<td style='background-color:#EEEEEE'>
									<input type='radio' name='publish' value="0" /><?php echo JText::_('JL_GLOBAL_NO'); ?>
									<input type='radio' name='publish' value="1" checked='checked' /><?php echo JText::_('JL_GLOBAL_YES'); ?>
								</td>
							</tr>
							<?php
						}
						?>
					</table>
				</fieldset>
				<p style='text-align:right;'><a href='#page_bottom'><?php echo JText::_('JL_ADMIN_XML_IMPORT_BOTTOM'); ?></a></p>
				<?php
			}
			?>
			<?php
			if ((isset($clubs) && count($clubs) > 0) || (isset($teams) && count($teams) > 0))
			{
				?>
				<fieldset>
					<legend><strong><?php
						if (!empty($clubs) && !empty($teams))
						{
							echo JText::_('JL_ADMIN_XML_IMPORT_CLUBS_TEAMS_LEGEND'); //JL_XML_IMPORT_TEAM_CLUB_LEGEND
						}
						elseif (!empty($clubs))
						{
							echo JText::_('JL_ADMIN_XML_IMPORT_CLUBS_LEGEND');
						}
						else
						{
							echo JText::_('JL_ADMIN_XML_IMPORT_TEAMS_LEGEND');
						}
						?></strong></legend>
					<table class='adminlist'>
						<thead>
							<tr>
								<th width='5%' nowrap='nowrap'><?php
									$checkCount=((isset($clubs) && count($clubs) > 0)) ? count($clubs) : count($teams);
									echo JText::_('JL_ADMIN_XML_IMPORT_ALL_NEW').'<br />';
									echo '<input type="checkbox" name="toggleTeamsClubs" value="" onclick="checkAllCustom('.$checkCount.')" />';
								?></th>
								<?php if (!empty($clubs)){ ?><th><?php echo JText::_('JL_ADMIN_XML_IMPORT_TEAM_DATA'); ?></th><?php } ?>
								<?php if (!empty($teams)){ ?><th><?php echo JText::_('JL_ADMIN_XML_IMPORT_CLUB_DATA'); ?></th><?php } ?>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$color1="#DDDDDD";
							$color2="#EEEEEE";
							//foreach ($teams AS $key=> $team)
							foreach ($teamsClubs AS $key=> $teamClub)
							{
								if ($key%2==1){$color=$color1;}else{$color=$color2;}
								?>
								<tr>
									<td width='10' nowrap='nowrap' style='text-align:center; vertical-align:middle; background-color:<?php echo $color; ?>'>
										<?php
										if (count($teamsClubs) > 0)
										{
											?>
											<input type='checkbox' value="<?php echo $key; ?>" name='cid[]' id='tc<?php echo $i; ?>' onchange='testTeamsClubsData(this,<?php echo $key; ?>)' />
											<?php
										}
										?>
									</td>
									<?php
									if (!empty($teams))
									{
										// Team column starts here
										$color='orange';
										$foundMatchingTeam=0;
										$foundMatchingTeamName='';
										$matchingTeam_ClubID=0;
										$matchingClubName='';

										if (count($this->teams) > 0)
										{
											foreach ($this->teams AS $row1)
											{
												if ($this->import_version=='OLD')
												{
													$teamInfo=$teamClub->description;
												}
												else
												{
													$teamInfo=$teamClub->info;
												}
												if (strtolower($teamClub->name)==strtolower($row1->name) &&
													strtolower($teamClub->short_name)==strtolower($row1->short_name) &&
													strtolower($teamClub->middle_name)==strtolower($row1->middle_name) &&
													strtolower($teamInfo)==strtolower($row1->info)
													)
												{
													$foundMatchingTeam=$row1->id;
													$foundMatchingTeamName=$teamClub->name;
													$matchingTeam_ClubID=$row1->club_id;
													if (!empty($clubs))
													{
														foreach ($this->clubs AS $row2)
														{
															if ($row2->id==$matchingTeam_ClubID)
															{
																$matchingClubName=$row2->name;
																break;
															}
														}
													}
													break;
												}
											}
										}
										if ($foundMatchingTeam){$color='lightgreen';}
										?>
										<td width='45%' style='text-align:left; vertical-align:top; background-color:<?php echo $color; ?>' id='tetd<?php echo $key; ?>'>
											<?php
											if ($foundMatchingTeam)
											{
												$checked="checked='checked' ";
												$disabled='';
											}
											else
											{
												$checked='';
												$disabled="disabled='disabled' ";
											}
											echo "<input type='checkbox' name='chooseTeam_$key' $checked";
											echo "onclick='if(this.checked)
													{
														document.adminForm.selectTeam_$key.checked=false;
														document.adminForm.createTeam_$key.checked=false;
														document.adminForm.teamName_$key.disabled=true;
														document.adminForm.teamShortname_$key.disabled=true;
														document.adminForm.teamInfo_$key.disabled=true;
														document.adminForm.teamMiddleName_$key.disabled=true;
													}
													else
													{
													}' $disabled ";
											echo "/>&nbsp;";
											$output="<input type='text' name='dbTeamName_$key' size='40' maxlength='60' value=\"".stripslashes(htmlspecialchars($foundMatchingTeamName))."\" style='font-weight: bold;' disabled='disabled' />";
											echo JText::sprintf('JL_ADMIN_XML_IMPORT_USE_TEAM',$output);
											echo "<input type='hidden' name='dbTeamID_$key' value=\"".stripslashes(htmlspecialchars($foundMatchingTeam))."\" $disabled />";
											echo '<br />';

											if (count($this->teams) > 0)
											{
												echo "<input type='checkbox' name='selectTeam_$key' ";
												echo "onclick='javascript:openSelectWindow(";
												echo $foundMatchingTeam;
												echo ",".$key;
												echo ',"selector"';
												echo ",this";
												echo ",1";
												echo ")' ";
												echo "/>&nbsp;";
												echo JText::_('JL_ADMIN_XML_IMPORT_ASSIGN_TEAM');
												echo '<br />';
											}
											else
											{
												echo "<input type='hidden' name='selectTeam_$key' />";
											}

											if ($foundMatchingTeam)
											{
												$checked='';
												$disabled="disabled'disabled' ";
											}
											else
											{
												$checked="checked='checked' ";
												$disabled='';
											}
											echo "<input type='checkbox' name='createTeam_$key' $checked ";
											echo "onclick='if(this.checked)
													{
														document.adminForm.chooseTeam_$key.checked=false;
														document.adminForm.selectTeam_$key.checked=false;
														document.adminForm.teamName_$key.disabled=false;
														document.adminForm.teamShortname_$key.disabled=false;
														document.adminForm.teamInfo_$key.disabled=false;
														document.adminForm.teamMiddleName_$key.disabled=false;
													}
													else
													{
														document.adminForm.teamName_$key.disabled=true;
														document.adminForm.teamShortname_$key.disabled=true;
														document.adminForm.teamInfo_$key.disabled=true;
														document.adminForm.teamMiddleName_$key.disabled=true;
													}' ";
											echo "/>&nbsp;";
											echo JText::_('JL_ADMIN_XML_IMPORT_CREATE_TEAM');
											if (JComponentHelper::getParams('com_joomleague')->get('show_debug_info',0)){echo ' ('.$teamClub->club_id.')';}
											?>
											<br />
											<table cellspacing='0' cellpadding='0'>
												<tr>
													<td>
														<?php
														echo '<b>'.JText::_('JL_ADMIN_XML_IMPORT_TEAMNAME').'</b>';
														?><br /><input type='hidden' name='teamID_<?php echo $key; ?>' value="<?php echo $key; ?>" <?php echo $disabled; ?> />
														<input type='text' name='teamName_<?php echo $key; ?>' size='45' maxlength='60' value="<?php echo stripslashes(htmlspecialchars($teamClub->name)); ?>" <?php echo $disabled; ?> />
													</td>
													<td>
														<?php
														echo '<b>'.JText::_('JL_ADMIN_XML_IMPORT_TEAMSHORT').'</b>';
														?><br /><input type='text' name='teamShortname_<?php echo $key; ?>' size='20' maxlength='15' value="<?php echo stripslashes(htmlspecialchars($teamClub->short_name)); ?>" <?php echo $disabled; ?> />
													</td>
												</tr>
												<tr>
													<td>
														<?php
														if ($this->import_version=='OLD')
														{
															$teamInfo=$teamClub->description;
														}
														else
														{
															$teamInfo=$teamClub->info;
														}
														echo '<b>'.JText::_('Info').'</b>';
														?><br /><input type='text' name='teamInfo_<?php echo $key; ?>' size='45' maxlength='255' value="<?php echo stripslashes(htmlspecialchars($teamInfo)); ?>" <?php echo $disabled; ?> />
													</td>
													<td>
														<?php
														echo '<b>'.JText::_('Middle Name').'</b>';
														?><br /><input type='text' name='teamMiddleName_<?php echo $key; ?>' size='20' maxlength='25' value="<?php echo stripslashes(htmlspecialchars($teamClub->middle_name)); ?>" <?php echo $disabled; ?> />
													</td>
												</tr>
											</table>
										</td>
										<?php
									}

									if (!empty($clubs))
									{
										// Club column starts here
										$color='orange';
										$clubname='';
										$clubid=0;
										$clubPlaygroundID=0;
										$clubCountry=0;

										if (!empty($teams))
										{
											foreach ($clubs as $club)
											{
												if ((int)$club->id==(int)$teamClub->club_id)
												{
													$clubid=$club->id;
													$clubname=$club->name;
													$clubCountry=$club->country;
													//echo $clubname.":".$clubCountry.",";
													
													if (preg_match('=^[0-9]+$=',$clubCountry)){$clubCountry=$this->OldCountries[(int)$clubCountry];}
													$clubPlaygroundID=$club->standard_playground;
													break; //only one club possible...
												}
											}
											if (count($this->clubs) > 0)
											{
												foreach ($this->clubs AS $row1)
												{
													$clubCountry=$row1->country;
													if (strtolower($clubname)==strtolower($row1->name) )
													{
														$color='lightgreen';
														$matchingTeam_ClubID=$row1->id;
														$matchingClubName=$row1->name;
														$clubCountry=$row1->country;
														$clubid=$club->id;
														//maybe also here row1->standard_playground???
														$clubPlaygroundID=$club->standard_playground;
														break;
													}
												}
											}
											/**/
										}
										else
										/**/
										{
											$matchingTeam_ClubID=0;
											$matchingClubName='';
											$club=$teamClub;
											$clubid=$club->id;
											$clubCountry=$club->country;
											if (count($this->clubs) > 0)
											{
												foreach ($this->clubs AS $row1)
												{
													if (strtolower($club->name)==strtolower($row1->name) &&
														strtolower($club->country)==strtolower($row1->country))
													{
														$color='lightgreen';
														$matchingTeam_ClubID=$row1->id;
														$matchingClubName=$teamClub->name;

														$clubid=$club->id;
														$clubCountry=$club->country;
														$clubPlaygroundID=$club->standard_playground;
														break;
													}
												}
											}
										}
										if ($matchingTeam_ClubID){$color='lightgreen';}
										?>
										<td width='45%' style='text-align:left; vertical-align:top; background-color:<?php echo $color; ?>' id='cltd<?php echo $key; ?>'>
											<?php
											if ($matchingTeam_ClubID)
											{
												$checked="checked='checked' ";
												$disabled='';
											}
											else
											{
												$checked='';
												$disabled="disabled='disabled' ";
											}
											echo "<input type='checkbox' name='chooseClub_$key' $checked";
											echo "onclick='if(this.checked)
													{
														document.adminForm.selectClub_$key.checked=false;
														document.adminForm.createClub_$key.checked=false;
														document.adminForm.clubName_$key.disabled=true;
														document.adminForm.clubCountry_$key.disabled=true;
													}
													else
													{
													}' $disabled ";
											echo "/>&nbsp;";
											$output="<input type='text' name='dbClubName_$key' size='45' maxlength='100' value=\"".stripslashes(htmlspecialchars($matchingClubName))."\" style='font-weight: bold; ' disabled='disabled' />";
											echo JText::sprintf('JL_ADMIN_XML_IMPORT_USE_CLUB',$output);
											echo "<input type='hidden' name='dbClubPlaygroundID_$key' value=\"$clubPlaygroundID\" $disabled />";
											echo "<input type='hidden' name='dbClubID_$key' value=\"$matchingTeam_ClubID\" $disabled $disabled />";
											echo '<br />';

											if (count($this->clubs) > 0)
											{
												echo "<input type='checkbox' name='selectClub_$key' ";
												echo "onclick='javascript:openSelectWindow(";
												echo $matchingTeam_ClubID;
												echo ",".$key;
												echo ',"selector"';
												echo ",this";
												echo ",2";
												echo ")' ";
												echo "/>&nbsp;";
												echo JText::_('JL_ADMIN_XML_IMPORT_ASSIGN_CLUB');
												echo '<br />';
											}
											else
											{
												echo "<input type='hidden' name='selectClub_$key' />";
											}
											if ($matchingTeam_ClubID)
											{
												$checked='';
												$disabled="disabled'disabled' ";
											}
											else
											{
												$checked="checked='checked' ";
												$disabled='';
											}
											echo "<input type='checkbox' name='createClub_$key' $checked ";
											echo "onclick='if(this.checked)
																{
																	document.adminForm.chooseClub_$key.checked=false;
																	document.adminForm.selectClub_$key.checked=false;
																	document.adminForm.clubName_$key.disabled=false;
																	document.adminForm.clubCountry_$key.disabled=false;
																	document.adminForm.clubID_$key.disabled=false;
																}
																else
																{
																	document.adminForm.clubName_$key.disabled=true;
																	document.adminForm.clubCountry_$key.disabled=true;
																	document.adminForm.clubID_$key.disabled=true;
														}' ";
											echo "/>&nbsp;";
											echo JText::_('JL_ADMIN_XML_IMPORT_CREATE_CLUB');
											if (JComponentHelper::getParams('com_joomleague')->get('show_debug_info',0)){echo ' ('.$club->id.')';}
											?>
											<br />
											<table cellspacing='0' cellpadding='0'>
												<tr>
													<td>
														<?php
														echo '<b>'.JText::_('JL_ADMIN_XML_IMPORT_CLUBNAME').'</b>';
														?><br />
														<input type='hidden' name='clubID_<?php echo $key; ?>' value="<?php echo $clubid; ?>" <?php echo $disabled; ?> />
														<input type='text' name='clubName_<?php echo $key; ?>' size='60' maxlength='100' value="<?php echo stripslashes(htmlspecialchars($club->name)); ?>" <?php echo $disabled; ?> />
													</td>
													<td>
														<?php
														echo '<b>'.JText::_('JL_ADMIN_XML_IMPORT_CLUBCOUNTRY').'</b>';
														$dCountry=$clubCountry; echo ": ".$dCountry;
														if (preg_match('=^[0-9]+$=',$dCountry)){$dCountry=$this->OldCountries[(int)$dCountry];}
														?><br />
														<?php 
															//build the html select list for countries
															$countries[] = JHTML::_( 'select.option', '', '- ' . JText::_( 'Select country' ) . ' -' );
															if ( $res =& Countries::getCountryOptions() )
															{
																$countries = array_merge( $countries, $res );
															}
															$countrieslist = JHTML::_(	'select.genericlist',
																						$countries,
																						'clubCountry_'.$key,
																						'class="inputbox" size="1" '.$disabled,
																						'value',
																						'text',
																						$dCountry);
															unset($countries);
															echo $countrieslist;
														?>
													</td>
												</tr>
											</table>
										</td>
										<?php
									}
									?>
								</tr>
								<?php
								$i++;
							}
							?>
						</tbody>
					</table>
				</fieldset>
				<p style='text-align:right;'><a href='#page_top'><?php echo JText::_('JL_ADMIN_XML_IMPORT_TOP'); ?></a></p>
				<?php
			}
			?>

			<?php
			if (isset($playgrounds) && (is_array($playgrounds) && count($playgrounds) > 0))
			{
				?>
				<fieldset>
					<legend><strong><?php echo JText::_('Playground Assignment'); ?></strong></legend>
					<table class='adminlist'>
						<thead>
							<tr>
								<th width='5%' nowrap='nowrap'><?php
									echo JText::_('JL_ADMIN_XML_IMPORT_ALL_NEW');
									echo '<br />';
									echo '<input type="checkbox" name="togglePlaygrounds" value="" onclick="checkAllPlaygrounds('.count($playgrounds).')" />';
								?></th>
								<th><?php echo JText::_('Playground'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$color1="#DDDDDD";
							$color2="#EEEEEE";
							foreach ($playgrounds AS $key=> $playground)
							{
								if ($key%2==1){$color=$color1;}else{$color=$color2;}
								?>
								<tr>
									<td style='text-align:center; vertical-align:middle; background-color:<?php echo $color; ?>'>
										<input type='checkbox' value="<?php echo $key; ?>" name='plid[]' id='pl<?php echo $i; ?>' onchange='testPlaygroundData(this,<?php echo $key; ?>)' />
									</td>
									<?php
									// Playground column starts here
									$color='orange';
									$foundMatchingPlayground=0;
									$foundMatchingPlaygroundName='';
									$foundMatchingPlaygroundShortname='';
									$playgroundClubID=0;

									if (count($this->playgrounds) > 0)
									{
										foreach ($this->playgrounds AS $row1)
										{
											if	(strtolower($playground->name)==strtolower($row1->name))
											{
												$color='lightgreen';
												$foundMatchingPlayground=$row1->id;
												$foundMatchingPlaygroundName=$row1->name;
												$foundMatchingPlaygroundShortname=$row1->short_name;
												$playgroundClubID=$row1->club_id;
												break;
											}
										}
									}
									?>
									<td width='45%' style='text-align:left; background-color:<?php echo $color; ?>' id='pltd<?php echo $key; ?>'>
										<?php
										if ($foundMatchingPlayground)
										{
											$checked="checked='checked' ";
											$disabled='';
										}
										else
										{
											$checked='';
											$disabled="disabled='disabled' ";
										}
										echo "<input type='checkbox' name='choosePlayground_$key' $checked";
										echo "onclick='if(this.checked)
												{
													document.adminForm.selectPlayground_$key.checked=false;
													document.adminForm.createPlayground_$key.checked=false;
													document.adminForm.playgroundName_$key.disabled=true;
													document.adminForm.playgroundShortname_$key.disabled=true;
												}
												else
												{
												}' $disabled ";
										echo "/>&nbsp;";
										$output1="<input type='text' name='dbPlaygroundName_$key' size='45' maxlength='45' value=\"".stripslashes(htmlspecialchars($foundMatchingPlaygroundName))."\" style='font-weight: bold;' disabled='disabled' />";
										$output2="<input type='text' name='dbPaygroundShortname_$key' size='20' maxlength='15' value=\"".stripslashes(htmlspecialchars($foundMatchingPlaygroundShortname))."\" style='font-weight: bold;' disabled='disabled' />";
										echo JText::sprintf('Use existing %1$s - %2$s from Database',$output1,$output2);
										echo "<input type='hidden' name='dbPlaygroundClubID_$key' value=\"$playgroundClubID\" $disabled />";
										echo "<input type='hidden' name='dbPlaygroundID_$key' value=\"$foundMatchingPlayground\" $disabled />";
										echo '<br />';

										if (count($this->playgrounds) > 0)
										{
											echo "<input type='checkbox' name='selectPlayground_$key' ";
											echo "onclick='javascript:openSelectWindow(";
											echo $foundMatchingPlayground;
											echo ",".$key;
											echo ',"selector"';
											echo ",this";
											echo ",4";
											echo ")' ";
											echo "/>&nbsp;";
											echo JText::_('Assign other Playground from Database');
											echo '<br />';
										}
										else
										{
											echo "<input type='hidden' name='selectPlayground_$key' />";
										}

										if ($foundMatchingPlayground)
										{
											$checked='';
											$disabled="disabled'disabled' ";
										}
										else
										{
											$checked="checked='checked' ";
											$disabled='';
										}
										echo "<input type='checkbox' name='createPlayground_$key' $checked ";
										echo "onclick='if(this.checked)
															{
																document.adminForm.choosePlayground_$key.checked=false;
																document.adminForm.selectPlayground_$key.checked=false;
																document.adminForm.playgroundName_$key.disabled=false;
																document.adminForm.playgroundShortname_$key.disabled=false;
															}
															else
															{
																document.adminForm.playgroundName_$key.disabled=true;
																document.adminForm.playgroundShortname_$key.disabled=true;
													}' ";
										echo "/>&nbsp;";
										echo JText::_('Create new Playground');
										?>
										<br />
										<table cellspacing='0' cellpadding='0'>
											<tr>
												<td>
													<?php echo '<b>'.JText::_('Playgroundname').'</b>'; ?><br >
													<input type='hidden' name='playgroundID_<?php echo $key; ?>' value="<?php echo $key; ?>" <?php echo $disabled; ?> />
													<input type='text' name='playgroundName_<?php echo $key; ?>' size='45' maxlength='45' value="<?php echo stripslashes(htmlspecialchars($playground->name)); ?>" <?php echo $disabled; ?> />
												</td>
												<td>
													<?php echo '<b>'.JText::_('Shortname').'</b>'; ?><br />
													<input type='text' name='playgroundShortname_<?php echo $key; ?>' size='20' maxlength='15' value="<?php echo stripslashes(htmlspecialchars($playground->short_name)); ?>" <?php echo $disabled; ?> />
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<?php
								$i++;
							}
							?>
						</tbody>
					</table>
				</fieldset>
				<p style='text-align:right;'><a href='#page_top'><?php echo JText::_('JL_ADMIN_XML_IMPORT_TOP'); ?></a></p>
				<?php
			}
			?>
			<?php
			if (isset($events) && (is_array($events) && count($events) > 0))
			{
				?>
				<fieldset>
					<legend><strong><?php echo JText::_('Event Assignment'); ?></strong></legend>
					<table class='adminlist'>
						<thead>
							<tr>
								<th width='5%' nowrap='nowrap'><?php
									echo JText::_('JL_ADMIN_XML_IMPORT_ALL_NEW');
									echo '<br />';
									echo '<input type="checkbox" name="toggleEvents" value="" onclick="checkAllEvents('.count($events).')" />';
								?></th>
								<th><?php echo JText::_('Event'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$color1="#DDDDDD";
							$color2="#EEEEEE";
							foreach ($events AS $key=> $event)
							{
								if ($key%2==1){$color=$color1;}else{$color=$color2;}
								?>
								<tr>
									<td style='text-align:center; vertical-align:middle; background-color:<?php echo $color; ?>'>
										<input type='checkbox' value="<?php echo $key; ?>" name='evid[]' id='ev<?php echo $i; ?>' onchange='testEventsData(this,<?php echo $key; ?>)' />
									</td>
									<?php
									// Event column starts here
									$color='orange';
									$foundMatchingEvent=0;
									$foundMatchingEventName='';

									if (count($this->events) > 0)
									{
										foreach ($this->events AS $row1)
										{
											if ((strtolower($event->name)==strtolower($row1->name)) ||
												(strtolower(JText::_($event->name))==strtolower(JText::_($row1->name))))
											{
												$color='lightgreen';
												$foundMatchingEvent=$row1->id;
												$foundMatchingEventName=$row1->name;
												break;
											}
										}
									}
									?>
									<td width='45%' style='text-align:left; background-color:<?php echo $color; ?>' id='evtd<?php echo $key; ?>'>
										<?php
										if ($foundMatchingEvent)
										{
											$checked="checked='checked' ";
											$disabled='';
										}
										else
										{
											$checked='';
											$disabled="disabled='disabled' ";
										}
										echo "<input type='checkbox' name='chooseEvent_$key' $checked";
										echo "onclick='if(this.checked)
												{
													document.adminForm.selectEvent_$key.checked=false;
													document.adminForm.createEvent_$key.checked=false;
													document.adminForm.eventName_$key.disabled=true;
												}
												else
												{
												}' $disabled ";
										echo "/>&nbsp;";
										$output1="<input type='text' name='dbEventName_$key' size='45' maxlength='75' value=\"".stripslashes(htmlspecialchars(JText::_($foundMatchingEventName)))."\" style='font-weight: bold;' disabled='disabled' />";
										echo JText::sprintf('Use existing %1$s from Database',$output1);
										echo "<input type='hidden' name='dbEventID_$key' value=\"$foundMatchingEvent\" $disabled />";
										echo '<br />';

										if (count($this->events) > 0)
										{
											echo "<input type='checkbox' name='selectEvent_$key' ";
											echo "onclick='javascript:openSelectWindow(";
											echo $foundMatchingEvent;
											echo ",".$key;
											echo ',"selector"';
											echo ",this";
											echo ",5";
											echo ")' ";
											echo "/>&nbsp;";
											echo JText::_('Assign other Event from Database');
											echo '<br />';
										}
										else
										{
											echo "<input type='hidden' name='selectEvent_$key' />";
										}

										if ($foundMatchingEvent)
										{
											$checked='';
											$disabled="disabled'disabled' ";
										}
										else
										{
											$checked="checked='checked' ";
											$disabled='';
										}
										echo "<input type='checkbox' name='createEvent_$key' $checked ";
										echo "onclick='if(this.checked)
												{
													document.adminForm.chooseEvent_$key.checked=false;
													document.adminForm.selectEvent_$key.checked=false;
													document.adminForm.eventName_$key.disabled=false;
												}
												else
												{
													document.adminForm.eventName_$key.disabled=true;
												}' ";
										echo "/>&nbsp;";
										echo JText::_('Create new Event');
										?>
										<br />
										<table cellspacing='0' cellpadding='0'>
											<tr>
												<td>
													<?php echo '<b>'.JText::_('Eventname').'</b>'; ?><br />
													<input type='hidden' name='eventID_<?php echo $key; ?>' value="<?php echo $key; ?>" <?php echo $disabled; ?> />
													<input type='text' name='eventName_<?php echo $key; ?>' size='75' maxlength='75' value="<?php echo stripslashes(htmlspecialchars($event->name)); ?>" <?php echo $disabled; ?> />
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<?php
								$i++;
							}
							?>
						</tbody>
					</table>
				</fieldset>
				<p style='text-align:right;'><a href='#page_top'><?php echo JText::_('JL_ADMIN_XML_IMPORT_TOP'); ?></a></p>
				<?php
			}
			?>
			<?php
			if (isset($parentpositions) && (is_array($parentpositions) && count($parentpositions) > 0))
			{
				?>
				<fieldset>
					<legend><strong><?php echo JText::_('Parent-Position Assignment'); ?></strong></legend>
					<table class='adminlist'>
						<thead>
							<tr>
								<th width='5%' nowrap='nowrap'><?php
									echo JText::_('JL_ADMIN_XML_IMPORT_ALL_NEW');
									echo '<br />';
									echo '<input type="checkbox" name="toggleParentPositions" value="" onclick="checkAllParentPositions('.count($parentpositions).')" />';
								?></th>
								<th><?php echo JText::_('Parent-Position'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$color1="#DDDDDD";
							$color2="#EEEEEE";
							foreach ($parentpositions AS $key=> $parentposition)
							{
								if ($key%2==1){$color=$color1;}else{$color=$color2;}
								?>
								<tr>
									<td style='text-align:center; vertical-align:middle; background-color:<?php echo $color; ?>'>
										<input type='checkbox' value="<?php echo $key; ?>" name='ppid[]' id='pp<?php echo $i; ?>' onchange='testParentPositionsData(this,<?php echo $key; ?>)' />
									</td>
									<?php
									// ParentPosition column starts here
									$color='orange';
									$foundMatchingParentPosition=0;
									$foundMatchingParentPositionName='';

									if (count($this->parentpositions) > 0)
									{
										foreach ($this->parentpositions AS $row1)
										{
											if ((strtolower($parentposition->name)==strtolower($row1->name)))
											{
												$color='lightgreen';
												$foundMatchingParentPosition=$row1->id;
												$foundMatchingParentPositionName=$row1->name;
												break;
											}
										}
									}
									?>
									<td width='45%' style='text-align:left; background-color:<?php echo $color; ?>' id='potd<?php echo $key; ?>'>
										<?php
										if ($foundMatchingParentPosition)
										{
											$checked="checked='checked' ";
											$disabled='';
										}
										else
										{
											$checked='';
											$disabled="disabled='disabled' ";
										}
										echo "<input type='checkbox' name='chooseParentPosition_$key' $checked";
										echo "onclick='if(this.checked)
												{
													document.adminForm.selectParentPosition_$key.checked=false;
													document.adminForm.createParentPosition_$key.checked=false;
													document.adminForm.parentPositionName_$key.disabled=true;
												}
												else
												{
												}' $disabled ";
										echo "/>&nbsp;";
										$output1="<input type='text' name='dbParentPositionName_$key' size='45' maxlength='75' value=\"".stripslashes(htmlspecialchars(JText::_($foundMatchingParentPositionName)))."\" style='font-weight: bold;' disabled='disabled' />";
										echo JText::sprintf('Use existing %1$s from Database',$output1);
										echo "<input type='hidden' name='dbParentPositionID_$key' value=\"$foundMatchingParentPosition\" $disabled />";
										echo '<br />';

										if (count($this->parentpositions) > 0)
										{
											echo "<input type='checkbox' name='selectParentPosition_$key' ";
											echo "onclick='javascript:openSelectWindow(";
											echo $foundMatchingParentPosition;
											echo ",".$key;
											echo ',"selector"';
											echo ",this";
											echo ",7";
											echo ")' ";
											echo "/>&nbsp;";
											echo JText::_('Assign other Parent-Position from Database');
											echo '<br />';
										}
										else
										{
											echo "<input type='hidden' name='selectParentPosition_$key' />";
										}

										if ($foundMatchingParentPosition)
										{
											$checked='';
											$disabled="disabled'disabled' ";
										}
										else
										{
											$checked="checked='checked' ";
											$disabled='';
										}
										echo "<input type='checkbox' name='createParentPosition_$key' $checked ";
										echo "onclick='if(this.checked)
												{
													document.adminForm.chooseParentPosition_$key.checked=false;
													document.adminForm.selectParentPosition_$key.checked=false;
													document.adminForm.parentPositionName_$key.disabled=false;
												}
												else
												{
													document.adminForm.parentPositionName_$key.disabled=true;
												}' ";
										echo "/>&nbsp;";
										echo JText::_('Create new Parent-Position');
										?>
										<br />
										<table cellspacing='0' cellpadding='0'>
											<tr>
												<td>
													<?php echo '<b>'.JText::_('Parent-Positionname').'</b>'; ?><br />
													<input type='hidden' name='parentPositionID_<?php echo $key; ?>' value="<?php echo $key; ?>" <?php echo $disabled; ?> />
													<input type='text' name='parentPositionName_<?php echo $key; ?>' size='75' maxlength='75' value="<?php echo stripslashes(htmlspecialchars($parentposition->name)); ?>" <?php echo $disabled; ?> />
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<?php
								$i++;
							}
							?>
						</tbody>
					</table>
				</fieldset>
				<p style='text-align:right;'><a href='#page_top'><?php echo JText::_('JL_ADMIN_XML_IMPORT_TOP'); ?></a></p>
				<?php
			}
			?>
			<?php
			if (isset($positions) && (is_array($positions) && count($positions) > 0))
			{
				?>
				<fieldset>
					<legend><strong><?php echo JText::_('Position Assignment'); ?></strong></legend>
					<table class='adminlist'>
						<thead>
							<tr>
								<th width='5%' nowrap='nowrap'><?php
									echo JText::_('JL_ADMIN_XML_IMPORT_ALL_NEW');
									echo '<br />';
									echo '<input type="checkbox" name="togglePositions" value="" onclick="checkAllPositions('.count($positions).')" />';
								?></th>
								<th><?php echo JText::_('Position'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$color1="#DDDDDD";
							$color2="#EEEEEE";
							foreach ($positions AS $key=> $position)
							{
								if ($key%2==1){$color=$color1;}else{$color=$color2;}
								?>
								<tr>
									<td style='text-align:center; vertical-align:middle; background-color:<?php echo $color; ?>'>
										<input type='checkbox' value="<?php echo $key; ?>" name='poid[]' id='po<?php echo $i; ?>' onchange='testPositionsData(this,<?php echo $key; ?>)' />
									</td>
									<?php
									// Position column starts here
									$color='orange';
									$foundMatchingPosition=0;
									$foundMatchingPositionName='';

									if (count($this->positions) > 0)
									{
										foreach ($this->positions AS $row1)
										{
											if ((strtolower($position->name)==strtolower($row1->name)))
											{
												$color='lightgreen';
												$foundMatchingPosition=$row1->id;
												$foundMatchingPositionName=$row1->name;
												break;
											}
										}
									}
									?>
									<td width='45%' style='text-align:left; background-color:<?php echo $color; ?>' id='potd<?php echo $key; ?>'>
										<?php
										if ($foundMatchingPosition)
										{
											$checked="checked='checked' ";
											$disabled='';
										}
										else
										{
											$checked='';
											$disabled="disabled='disabled' ";
										}
										echo "<input type='checkbox' name='choosePosition_$key' $checked";
										echo "onclick='if(this.checked)
												{
													document.adminForm.selectPosition_$key.checked=false;
													document.adminForm.createPosition_$key.checked=false;
													document.adminForm.positionName_$key.disabled=true;
												}
												else
												{
												}' $disabled ";
										echo "/>&nbsp;";
										$output1="<input type='text' name='dbPositionName_$key' size='45' maxlength='75' value=\"".stripslashes(htmlspecialchars(JText::_($foundMatchingPositionName)))."\" style='font-weight: bold;' disabled='disabled' />";
										echo JText::sprintf('Use existing %1$s from Database',$output1);
										echo "<input type='hidden' name='dbPositionID_$key' value=\"$foundMatchingPosition\" $disabled />";
										echo '<br />';

										if (count($this->positions) > 0)
										{
											echo "<input type='checkbox' name='selectPosition_$key' ";
											echo "onclick='javascript:openSelectWindow(";
											echo $foundMatchingPosition;
											echo ",".$key;
											echo ',"selector"';
											echo ",this";
											echo ",6";
											echo ")' ";
											echo "/>&nbsp;";
											echo JText::_('Assign other Position from Database');
											echo '<br />';
										}
										else
										{
											echo "<input type='hidden' name='selectPosition_$key' />";
										}

										if ($foundMatchingPosition)
										{
											$checked='';
											$disabled="disabled'disabled' ";
										}
										else
										{
											$checked="checked='checked' ";
											$disabled='';
										}
										echo "<input type='checkbox' name='createPosition_$key' $checked ";
										echo "onclick='if(this.checked)
												{
													document.adminForm.choosePosition_$key.checked=false;
													document.adminForm.selectPosition_$key.checked=false;
													document.adminForm.positionName_$key.disabled=false;
												}
												else
												{
													document.adminForm.positionName_$key.disabled=true;
												}' ";
										echo "/>&nbsp;";
										echo JText::_('Create new Position');
										?>
										<br />
										<table cellspacing='0' cellpadding='0'>
											<tr>
												<td>
													<?php echo '<b>'.JText::_('Positionname').'</b>'; ?><br />
													<input type='hidden' name='positionID_<?php echo $key; ?>' value="<?php echo $key; ?>" <?php echo $disabled; ?> />
													<input type='text' name='positionName_<?php echo $key; ?>' size='75' maxlength='75' value="<?php echo stripslashes(htmlspecialchars($position->name)); ?>" <?php echo $disabled; ?> />
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<?php
								$i++;
							}
							?>
						</tbody>
					</table>
				</fieldset>
				<p style='text-align:right;'><a href='#page_top'><?php echo JText::_('JL_ADMIN_XML_IMPORT_TOP'); ?></a></p>
				<?php
			}
			?>
			<?php
			if (isset($statistics) && (is_array($statistics) && count($statistics) > 0))
			{
				?>
				<fieldset>
					<legend><strong><?php echo JText::_('Statistics Assignment'); ?></strong></legend>
					<table class='adminlist'>
						<thead>
							<tr>
								<th width='5%' nowrap='nowrap'><?php
									echo JText::_('JL_ADMIN_XML_IMPORT_ALL_NEW');
									echo '<br />';
									echo '<input type="checkbox" name="toggleStatistics" value="" onclick="checkAllStatistics('.count($statistics).')" />';
								?></th>
								<th><?php echo JText::_('Statistic'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$color1="#DDDDDD";
							$color2="#EEEEEE";
							foreach ($statistics AS $key=> $statistic)
							{
								if ($key%2==1){$color=$color1;}else{$color=$color2;}
								?>
								<tr>
									<td style='text-align:center; vertical-align:middle; background-color:<?php echo $color; ?>'>
										<input type='checkbox' value="<?php echo $key; ?>" name='stid[]' id='st<?php echo $i; ?>' onchange='testStatisticsData(this,<?php echo $key; ?>)' />
									</td>
									<?php
									// Statistic column starts here
									$color='orange';
									$foundMatchingStatistic=0;
									$foundMatchingStatisticName='';
									$foundMatchingStatisticShort='';
									$foundMatchingStatisticClass='';
									$foundMatchingStatisticNote='';

									if (count($this->statistics) > 0)
									{
										foreach ($this->statistics AS $row1)
										{
											if ((strtolower($statistic->name)==strtolower($row1->name))
												&& (strtolower($statistic->class)==strtolower($row1->class))
												// && (strtolower($statistic->params)==strtolower($row1->params))
												// && (strtolower($statistic->baseparams)==strtolower($row1->baseparams))
												)
											{
												$color='lightgreen';
												$foundMatchingStatistic=$row1->id;
												$foundMatchingStatisticName=$row1->name;
												$foundMatchingStatisticShort=$row1->short;
												$foundMatchingStatisticClass=$row1->class;
												$foundMatchingStatisticNote=$row1->note;
												break;
											}
										}
									}
									?>
									<td width='45%' style='text-align:left; background-color:<?php echo $color; ?>' id='potd<?php echo $key; ?>'>
										<?php
										if ($foundMatchingStatistic)
										{
											$checked="checked='checked' ";
											$disabled='';
										}
										else
										{
											$checked='';
											$disabled="disabled='disabled' ";
										}
										echo "<input type='checkbox' name='chooseStatistic_$key' $checked";
										echo "onclick='if(this.checked)
												{
													document.adminForm.selectStatistic_$key.checked=false;
													document.adminForm.createStatistic_$key.checked=false;
													document.adminForm.statisticName_$key.disabled=true;
												}
												else
												{
												}' $disabled ";
										echo "/>&nbsp;";
										$output1="<input type='text' name='dbStatisticName_$key' size='45' maxlength='75' value=\"".stripslashes(htmlspecialchars($foundMatchingStatisticName))."\" style='font-weight: bold;' disabled='disabled' />";
										$output2="<input type='text' name='dbStatisticShort_$key' size='15' maxlength='10' value=\"".stripslashes(htmlspecialchars($foundMatchingStatisticShort))."\" style='font-weight: bold;' disabled='disabled' />";
										$output3="<input type='text' name='dbStatisticClass_$key' size='15' maxlength='50' value=\"".stripslashes(htmlspecialchars($foundMatchingStatisticClass))."\" style='font-weight: bold;' disabled='disabled' />";
										$output4="<input type='text' name='dbStatisticNote_$key' size='15' maxlength='100' value=\"".stripslashes(htmlspecialchars($foundMatchingStatisticNote))."\" style='font-weight: bold;' disabled='disabled' />";
										echo JText::sprintf('Use existing %1$s - %2$s - %3$s - %4$s from Database',$output1,$output2,$output3,$output4);
										echo "<input type='hidden' name='dbStatisticID_$key' value=\"$foundMatchingStatistic\" $disabled />";
										echo '<br />';

										if (count($this->statistics) > 0)
										{
											echo "<input type='checkbox' name='selectStatistic_$key' ";
											echo "onclick='javascript:openSelectWindow(";
											echo $foundMatchingStatistic;
											echo ",".$key;
											echo ',"selector"';
											echo ",this";
											echo ",8";
											echo ")' ";
											echo "/>&nbsp;";
											echo JText::_('Assign other Statistic from Database');
											echo '<br />';
										}
										else
										{
											echo "<input type='hidden' name='selectStatistic_$key' />";
										}

										if ($foundMatchingStatistic)
										{
											$checked='';
											$disabled="disabled'disabled' ";
										}
										else
										{
											$checked="checked='checked' ";
											$disabled='';
										}
										echo "<input type='checkbox' name='createStatistic_$key' $checked ";
										echo "onclick='if(this.checked)
												{
													document.adminForm.chooseStatistic_$key.checked=false;
													document.adminForm.selectStatistic_$key.checked=false;
													document.adminForm.statisticName_$key.disabled=false;
												}
												else
												{
													document.adminForm.statisticName_$key.disabled=true;
												}' ";
										echo "/>&nbsp;";
										echo JText::_('Create new Statistic');
										?>
										<br />
										<table cellspacing='0' cellpadding='0'>
											<tr>
												<td>
													<?php echo '<b>'.JText::_('Statisticname').'</b>'; ?><br />
													<input type='hidden' name='statisticID_<?php echo $key; ?>' value="<?php echo $key; ?>" <?php echo $disabled; ?> />
													<input type='text' name='statisticName_<?php echo $key; ?>' size='100' maxlength='75' value="<?php echo stripslashes(htmlspecialchars($statistic->name)); ?>" <?php echo $disabled; ?> />
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<?php
								$i++;
							}
							?>
						</tbody>
					</table>
				</fieldset>
				<p style='text-align:right;'><a href='#page_top'><?php echo JText::_('JL_ADMIN_XML_IMPORT_TOP'); ?></a></p>
				<?php
			}
			?>
			<?php
			if (isset($persons) && (is_array($persons) && count($persons) > 0))
			{
				?>
				<fieldset>
					<legend><strong><?php echo JText::_('JL_ADMIN_XML_IMPORT_PERSON_LEGEND'); ?></strong></legend>
					<table class='adminlist'>
						<thead>
							<tr>
								<th width='5%' nowrap='nowrap'><?php
									echo JText::_('JL_ADMIN_XML_IMPORT_ALL_NEW');
									echo '<br />';
									echo '<input type="checkbox" name="togglePersons" value="" onclick="checkAllPersons('.count($persons).')" />';
								?></th>
								<th><?php echo JText::_('JL_ADMIN_XML_IMPORT_PERSON_DATA'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$color1="#DDDDDD";
							$color2="#EEEEEE";
							foreach ($persons AS $key=> $person)
							{
								if ($key%2==1){$color=$color1;}else{$color=$color2;}
								?>
								<tr>
									<td style='text-align:center; vertical-align:middle; background-color:<?php echo $color; ?>'>
										<input type='checkbox' value="<?php echo $key; ?>" name='peid[]' id='pe<?php echo $i; ?>' onchange='testPersonsData(this,<?php echo $key; ?>)' />
									</td>
									<?php
									// Person column starts here
									$color='orange';
									$foundMatchingPerson=0;
									$foundMatchingPersonName='';
									$foundMatchingPersonLastname='';
									$foundMatchingPersonFirstname='';
									$foundMatchingPersonNickname='';
									$foundMatchingPersonBirthday='';

									if (count($this->persons) > 0)
									{
										foreach ($this->persons AS $row1)
										{
											if	((strtolower($person->lastname)==strtolower($row1->lastname))
												&& (strtolower($person->firstname)==strtolower($row1->firstname))
												&&(strtolower($person->nickname)==strtolower($row1->nickname))
												&&($person->birthday==$row1->birthday)
												)
											{
												$color='lightgreen';
												$foundMatchingPerson=$row1->id;
												$foundMatchingPersonLastname=$row1->lastname;
												$foundMatchingPersonFirstname=$row1->firstname;
												$foundMatchingPersonNickname=$row1->nickname;
												$foundMatchingPersonBirthday=$row1->birthday;
												break;
											}
										}
									}
									?>
									<td width='45%' style='text-align:left; background-color:<?php echo $color; ?>' id='prtd<?php echo $key; ?>'>
										<?php
										if ($foundMatchingPerson)
										{
											$checked="checked='checked' ";
											$disabled='';
										}
										else
										{
											$checked='';
											$disabled="disabled='disabled' ";
										}
										echo "<input type='checkbox' name='choosePerson_$key' $checked";
										echo "onclick='if(this.checked)
												{
													document.adminForm.selectPerson_$key.checked=false;
													document.adminForm.createPerson_$key.checked=false;
													document.adminForm.personLastname_$key.disabled=true;
													document.adminForm.personFirstname_$key.disabled=true;
													document.adminForm.personNickname_$key.disabled=true;
													document.adminForm.personBirthday_$key.disabled=true;
												}
												else
												{
												}' $disabled ";
										echo "/>&nbsp;";
										$output1="<input type='text' name='dbPersonLastname_$key' size='30' maxlength='45' value=\"".stripslashes(htmlspecialchars($foundMatchingPersonLastname))."\" style='font-weight: bold;' disabled='disabled' />";
										$output2="<input type='text' name='dbPersonFirstname_$key' size='30' maxlength='45' value=\"".stripslashes(htmlspecialchars($foundMatchingPersonFirstname))."\" style='font-weight: bold;' disabled='disabled' />";
										$output3="<input type='text' name='dbPersonNickname_$key' size='30' maxlength='45' value=\"".stripslashes(htmlspecialchars($foundMatchingPersonNickname))."\" style='font-weight: bold;' disabled='disabled' />";
										$output4="<input type='text' name='dbPersonBirthday_$key' value=\"$foundMatchingPersonBirthday\" maxlength='10' size='11' style='font-weight: bold;' disabled='disabled' />";
										echo JText::sprintf('JL_ADMIN_XML_IMPORT_USE_PERSON',$output1,$output2,$output3,$output4);
										echo "<input type='hidden' name='dbPersonID_$key' value=\"$foundMatchingPerson\" $disabled />";
										echo '<br />';

										if (count($this->persons) > 0)
										{
											echo "<input type='checkbox' name='selectPerson_$key' ";
											echo "onclick='javascript:openSelectWindow(";
											echo $foundMatchingPerson;
											echo ",".$key;
											echo ',"selector"';
											echo ",this";
											echo ",3";
											echo ")' ";
											echo "/>&nbsp;";
											echo JText::_('JL_ADMIN_XML_IMPORT_ASSIGN_PERSON');
											echo '<br />';
										}
										else
										{
											echo "<input type='hidden' name='selectPerson_$key' />";
										}

										if ($foundMatchingPerson)
										{
											$checked='';
											$disabled="disabled'disabled' ";
										}
										else
										{
											$checked="checked='checked' ";
											$disabled='';
										}
										echo "<input type='checkbox' name='createPerson_$key' $checked ";
										echo "onclick='if(this.checked)
															{
																document.adminForm.choosePerson_$key.checked=false;
																document.adminForm.selectPerson_$key.checked=false;
																document.adminForm.personLastname_$key.disabled=false;
																document.adminForm.personFirstname_$key.disabled=false;
																document.adminForm.personNickname_$key.disabled=false;
																document.adminForm.personBirthday_$key.disabled=false;
															}
															else
															{
																document.adminForm.personLastname_$key.disabled=true;
																document.adminForm.personFirstname_$key.disabled=true;
																document.adminForm.personNickname_$key.disabled=true;
																document.adminForm.personBirthday_$key.disabled=true;
													}' ";
										echo "/>&nbsp;";
										echo JText::_('JL_ADMIN_XML_IMPORT_CREATE_PERSON');
										?>
										<br />
										<table cellspacing='0' cellpadding='0'>
											<tr>
												<td><?php echo '<b>'.JText::_('JL_ADMIN_XML_IMPORT_LNAME').'</b>'; ?><br />
													<input type='hidden' name='personID_<?php echo $key; ?>' value="<?php echo $key; ?>" <?php echo $disabled; ?> />
													<input type='text' name='personLastname_<?php echo $key; ?>' size='30' maxlength='45' value="<?php echo stripslashes(htmlspecialchars($person->lastname)); ?>" <?php echo $disabled; ?> />
												</td>
												<td><?php echo '<b>'.JText::_('JL_ADMIN_XML_IMPORT_FNAME').'</b>'; ?><br />
													<input type='text' name='personFirstname_<?php echo $key; ?>' size='30' maxlength='45' value="<?php echo stripslashes(htmlspecialchars($person->firstname)); ?>" <?php echo $disabled; ?> />
												</td>
												<td><?php echo '<b>'.JText::_('JL_ADMIN_XML_IMPORT_NNAME').'</b>'; ?><br />
													<input type='text' name='personNickname_<?php echo $key; ?>' size='30' maxlength='45' value="<?php echo stripslashes(htmlspecialchars($person->nickname)); ?>" <?php echo $disabled; ?> />
												</td>
												<td><?php echo '<b>'.JText::_('JL_ADMIN_XML_IMPORT_BIRTHDAY').'</b>'; ?><br />
													<input type='text' name='personBirthday_<?php echo $key; ?>' maxlength='10' size='11' value="<?php echo $person->birthday; ?>" <?php echo $disabled; ?> />
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<?php
								$i++;
							}
							?>
						</tbody>
					</table>
				</fieldset>
				<p style='text-align:right;'><a href='#page_top'><?php echo JText::_('JL_ADMIN_XML_IMPORT_TOP'); ?></a></p>
				<?php
			}
			?>
		</form>
	</div>
	<?php
	if (JComponentHelper::getParams('com_joomleague')->get('show_debug_info',0))
	{
		echo '<center><hr>';
			echo JText::sprintf('Memory Limit is %1$s',ini_get('memory_limit')).'<br />';
			echo JText::sprintf('Memory Peak Usage was %1$s Bytes',number_format(memory_get_peak_usage(true),0,'','.')).'<br />';
			echo JText::sprintf('Time Limit is %1$s seconds',ini_get('max_execution_time')).'<br />';
			$mtime=microtime();
			$mtime=explode(" ",$mtime);
			$mtime=$mtime[1] + $mtime[0];
			$endtime=$mtime;
			$totaltime=($endtime - $this->starttime);
			echo JText::sprintf('This page was created in %1$s seconds',$totaltime);
		echo '<hr></center>';
	}

}
?>