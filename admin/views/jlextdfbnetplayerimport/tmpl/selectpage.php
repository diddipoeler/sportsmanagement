<?php defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip');
//echo '~'.JFactory::getApplication()->getUserState('com_joomleague' . 'project').'~';
//echo '~'.JFactory::getApplication()->getUserState('com_joomleague' . 'selectType').'~';
//echo '<br /><pre>~' . print_r($this->events,true) . '~</pre><br />';
switch ($this->selectType)
{
	case '8':	{
					$tableTitle = JText::_('Select statistic to assign');
					echo '<script language="javascript" type="text/javascript"><!--'."\n";
					echo 'var statistics=new Array;';
					foreach ($this->statistics as $statistic)
					{
						echo "statistics[$statistic->value]={";
						echo "value: '$statistic->value', ";
						echo "name: '".addslashes($statistic->text)."'";
						echo "};";
					}
					echo "\n".'//--></script>';
				}
				break;
	case '7':	{
					$tableTitle = JText::_('Select parentposition to assign');
					echo '<script language="javascript" type="text/javascript"><!--'."\n";
					echo 'var parentpositions=new Array;';
					foreach ($this->parentpositions as $parentposition)
					{
						echo "parentpositions[$parentposition->value]={";
						echo "value: '$parentposition->value', ";
						echo "name: '".addslashes($parentposition->text)."'";
						echo "};";
					}
					echo "\n".'//--></script>';
				}
				break;
	case '6':	{
					$tableTitle = JText::_('Select position to assign');
					echo '<script language="javascript" type="text/javascript"><!--'."\n";
					echo 'var positions=new Array;';
					foreach ($this->positions as $position)
					{
						echo "positions[$position->value]={";
						echo "value: '$position->value', ";
						echo "name: '".addslashes($position->text)."'";
						echo "};";
					}
					echo "\n".'//--></script>';
				}
				break;
	case '5':	{
					$tableTitle = JText::_('Select event to assign');
					echo '<script language="javascript" type="text/javascript"><!--'."\n";
					echo 'var events=new Array;';
					foreach ($this->events as $event)
					{
						echo "events[$event->value]={";
						echo "value: '$event->value', ";
						echo "name: '".addslashes($event->text)."'";
						echo "};";
					}
					echo "\n".'//--></script>';
				}
				break;
	case '4':	{
					$tableTitle = JText::_('Select playground to assign');
					echo '<script language="javascript" type="text/javascript"><!--'."\n";
					echo 'var playgrounds=new Array;';
					foreach ($this->playgrounds as $playground)
					{
						echo "playgrounds[$playground->value]={";
						echo "value: '$playground->value', ";
						echo "name: '".addslashes($playground->text)."', ";
						echo "short_name: '".addslashes($playground->short_name)."'";
						echo "};";
					}
					echo "\n".'//--></script>';
				}
				break;
	case '3':	{
					$tableTitle = JText::_('Select person to assign');
					echo '<script language="javascript" type="text/javascript"><!--'."\n";
					echo 'var persons=new Array;';
					foreach ($this->persons as $person)
					{
						echo "persons[$person->value]={";
						echo "value: '$person->value', ";
						echo "lastname: '".addslashes($person->lastname)."', ";
						echo "firstname: '".addslashes($person->firstname)."', ";
						echo "nickname: '".addslashes($person->nickname)."', ";
						echo "birthday: '$person->birthday'";
						echo "};";
					}
					echo "\n".'//--></script>';
				}
				break;
	case '2':	{
					$tableTitle = JText::_('COM_JOOMLEAGUE_ADMIN_DFBNET_IMPORT_ASSIGN_CLUB');
					echo '<script language="javascript" type="text/javascript"><!--'."\n";
					echo 'var clubs=new Array;';
					foreach ($this->clubs as $club)
					{
						echo "clubs[$club->value]={value: '$club->value', clubname: '".addslashes($club->text)."'};";
					}
					echo "\n".'//--></script>';
				}
				break;
	case '1':
	default:	{
					$tableTitle = JText::_('COM_JOOMLEAGUE_ADMIN_DFBNET_IMPORT_TEAM_CLUB');
					echo '<script language="javascript" type="text/javascript"><!--'."\n";
					echo 'var teams = new Array;';
					foreach ($this->teams as $team)
					{
						echo "teams[$team->value]={value: '$team->value', teamname: '".addslashes($team->name)."', clubID: '".$team->club_id."'};";
					}
					echo 'var clubs=new Array;';
					foreach ($this->clubs as $club)
					{
						echo "clubs[$club->value]={value: '$club->value', clubname: '".addslashes($club->text)."'};";
					}
					echo "\n".'//--></script>';
				}
				break;
}
$tableTitle.=" ($this->recordID)";
?>
<script language="javascript" type="text/javascript"><!--

function stripslashes(str) {
str=str.replace(/\\'/g,'\'');
str=str.replace(/\\"/g,'"');
str=str.replace(/\\0/g,'\0');
str=str.replace(/\\\\/g,'\\');
return str;
}

// team_id
function insertTeam() {
myteamID=this.document.selectorForm.teamID.value;
//alert(myteamID);
//alert(teams[myteamID].teamname);
opener.document.forms['adminForm'].chooseTeam_<?php echo $this->recordID; ?>.checked=true;
opener.document.forms['adminForm'].chooseTeam_<?php echo $this->recordID; ?>.disabled=false;
opener.document.forms['adminForm'].dbTeamID_<?php echo $this->recordID; ?>.value=myteamID;
opener.document.forms['adminForm'].dbTeamID_<?php echo $this->recordID; ?>.disabled=false;
//opener.document.forms['adminForm'].teamID_<?php echo $this->recordID; ?>.value=myteamID;
opener.document.forms['adminForm'].teamID_<?php echo $this->recordID; ?>.disabled=true;
opener.document.forms['adminForm'].dbTeamName_<?php echo $this->recordID; ?>.value=stripslashes(teams[myteamID].teamname);
opener.document.forms['adminForm'].teamName_<?php echo $this->recordID; ?>.value=stripslashes(teams[myteamID].teamname);
opener.document.getElementById('tetd<?php echo $this->recordID; ?>').style.backgroundColor='lightgreen';

//insertClub(teams[myteamID].clubID);
opener.document.forms['adminForm'].createClub_<?php echo $this->recordID; ?>.checked=false;
opener.document.forms['adminForm'].clubName_<?php echo $this->recordID; ?>.disabled=true;
opener.document.forms['adminForm'].clubCountry_<?php echo $this->recordID; ?>.disabled=true;
opener.document.forms['adminForm'].clubID_<?php echo $this->recordID; ?>.disabled=true;

opener.focus();
window.close();
return false;
}


//club_id
function insertClub(dClubID) {
//alert(dClubID);
if(typeof(dClubID)=='undefined'){myClubID=this.document.selectorForm.clubID.value;}else{myClubID=dClubID;}
//alert(myClubID);
opener.document.forms['adminForm'].chooseClub_<?php echo $this->recordID; ?>.checked=true;
opener.document.forms['adminForm'].chooseClub_<?php echo $this->recordID; ?>.disabled=false;
opener.document.forms['adminForm'].dbClubID_<?php echo $this->recordID; ?>.disabled=false;
opener.document.forms['adminForm'].dbClubID_<?php echo $this->recordID; ?>.value=myClubID;
opener.document.forms['adminForm'].dbClubName_<?php echo $this->recordID; ?>.value=stripslashes(clubs[myClubID].clubname);
opener.document.forms['adminForm'].clubName_<?php echo $this->recordID; ?>.value=stripslashes(clubs[myClubID].clubname);
opener.document.forms['adminForm'].clubID_<?php echo $this->recordID; ?>.disabled=true;
opener.document.getElementById('tetd<?php echo $this->recordID; ?>').style.backgroundColor='orange';

opener.document.forms['adminForm'].chooseTeam_<?php echo $this->recordID; ?>.checked=false;
opener.document.forms['adminForm'].chooseTeam_<?php echo $this->recordID; ?>.disabled=true;

opener.document.getElementById('cltd<?php echo $this->recordID; ?>').style.backgroundColor='lightgreen';
opener.document.forms['adminForm'].createTeam_<?php echo $this->recordID; ?>.checked=true;
opener.document.forms['adminForm'].teamName_<?php echo $this->recordID; ?>.disabled=false;
//opener.document.forms['adminForm'].teamName_<?php echo $this->recordID; ?>.value="<?php echo JText::_('Team ');?>"+stripslashes(clubs[myClubID].clubname);
opener.document.forms['adminForm'].teamShortname_<?php echo $this->recordID; ?>.disabled=false;
//opener.document.forms['adminForm'].teamShortname_<?php //echo $this->recordID; ?>.value='';
opener.document.forms['adminForm'].teamInfo_<?php echo $this->recordID; ?>.disabled=false;
//opener.document.forms['adminForm'].teamInfo_<?php //echo $this->recordID; ?>.value='';
opener.document.forms['adminForm'].teamMiddleName_<?php echo $this->recordID; ?>.disabled=false;
//opener.document.forms['adminForm'].teamMiddleName_<?php //echo $this->recordID; ?>.value='';


opener.focus();

window.close();
return false;
}

// person id
function insertPerson() {
myPersonID=this.document.selectorForm.personID.value;
//alert(myPersonID);
//alert(persons[myPersonID].lastname+", "+persons[myPersonID].firstname+", "+persons[myPersonID].nickname+", "+persons[myPersonID].birthday);
opener.document.forms['adminForm'].choosePerson_<?php echo $this->recordID; ?>.checked=true;
opener.document.forms['adminForm'].choosePerson_<?php echo $this->recordID; ?>.disabled=false;
opener.document.forms['adminForm'].dbPersonID_<?php echo $this->recordID; ?>.value=myPersonID;
opener.document.forms['adminForm'].dbPersonLastname_<?php echo $this->recordID; ?>.value=stripslashes(persons[myPersonID].lastname);
opener.document.forms['adminForm'].dbPersonFirstname_<?php echo $this->recordID; ?>.value=stripslashes(persons[myPersonID].firstname);
opener.document.forms['adminForm'].dbPersonNickname_<?php echo $this->recordID; ?>.value=stripslashes(persons[myPersonID].nickname);
opener.document.forms['adminForm'].dbPersonBirthday_<?php echo $this->recordID; ?>.value=persons[myPersonID].birthday;
opener.document.getElementById('prtd<?php echo $this->recordID; ?>').style.backgroundColor='lightgreen';
opener.focus();
window.close();
return false;
}

// playground_id
function insertPlayground(){
myPlaygroundID=this.document.selectorForm.playgroundID.value;
//alert(myPlaygroundID);
//alert(playgrounds[myPlaygroundID].name+" - "+playgrounds[myPlaygroundID].short_name);
opener.document.forms['adminForm'].choosePlayground_<?php echo $this->recordID; ?>.checked=true;
opener.document.forms['adminForm'].choosePlayground_<?php echo $this->recordID; ?>.disabled=false;
opener.document.forms['adminForm'].dbPlaygroundID_<?php echo $this->recordID; ?>.value=myPlaygroundID;
opener.document.forms['adminForm'].dbPlaygroundName_<?php echo $this->recordID; ?>.value=stripslashes(playgrounds[myPlaygroundID].name);
opener.document.forms['adminForm'].playgroundName_<?php echo $this->recordID; ?>.value=stripslashes(playgrounds[myPlaygroundID].name);
opener.document.forms['adminForm'].dbPaygroundShortname_<?php echo $this->recordID; ?>.value=stripslashes(playgrounds[myPlaygroundID].short_name);
opener.document.getElementById('pltd<?php echo $this->recordID; ?>').style.backgroundColor='lightgreen';
opener.focus();
window.close();
return false;
}

function insertEvent() {
myEventID=this.document.selectorForm.eventID.value;
opener.document.forms['adminForm'].chooseEvent_<?php echo $this->recordID; ?>.checked=true;
opener.document.forms['adminForm'].chooseEvent_<?php echo $this->recordID; ?>.disabled=false;
opener.document.forms['adminForm'].dbEventID_<?php echo $this->recordID; ?>.value=myEventID;
opener.document.forms['adminForm'].dbEventName_<?php echo $this->recordID; ?>.value=stripslashes(events[myEventID].name);
opener.document.getElementById('evtd<?php echo $this->recordID; ?>').style.backgroundColor='lightgreen';
opener.focus();
window.close();
return false;
}

function insertPosition() {
myPositionID=this.document.selectorForm.positionID.value;
opener.document.forms['adminForm'].choosePosition_<?php echo $this->recordID; ?>.checked=true;
opener.document.forms['adminForm'].choosePosition_<?php echo $this->recordID; ?>.disabled=false;
opener.document.forms['adminForm'].dbPositionID_<?php echo $this->recordID; ?>.value=myPositionID;
opener.document.forms['adminForm'].dbPositionName_<?php echo $this->recordID; ?>.value=stripslashes(positions[myPositionID].name);
opener.document.getElementById('potd<?php echo $this->recordID; ?>').style.backgroundColor='lightgreen';
opener.focus();
window.close();
return false;
}

function insertParentPosition() {
myParentPositionID=this.document.selectorForm.parentPositionID.value;
opener.document.forms['adminForm'].chooseParentPosition_<?php echo $this->recordID; ?>.checked=true;
opener.document.forms['adminForm'].chooseParentPosition_<?php echo $this->recordID; ?>.disabled=false;
opener.document.forms['adminForm'].dbParentPositionID_<?php echo $this->recordID; ?>.value=myParentPositionID;
opener.document.forms['adminForm'].dbParentPositionName_<?php echo $this->recordID; ?>.value=stripslashes(parentpositions[myParentPositionID].name);
opener.document.getElementById('pptd<?php echo $this->recordID; ?>').style.backgroundColor='lightgreen';
opener.focus();
window.close();
return false;
}

function insertStatistic() {
myStatisticID=this.document.selectorForm.statisticID.value;
opener.document.forms['adminForm'].chooseStatistic_<?php echo $this->recordID; ?>.checked=true;
opener.document.forms['adminForm'].chooseStatistic_<?php echo $this->recordID; ?>.disabled=false;
opener.document.forms['adminForm'].dbStatisticID_<?php echo $this->recordID; ?>.value=myStatisticID;
opener.document.forms['adminForm'].dbStatisticName_<?php echo $this->recordID; ?>.value=stripslashes(statistics[myStatisticID].name);
opener.document.getElementById('sttd<?php echo $this->recordID; ?>').style.backgroundColor='lightgreen';
opener.focus();
window.close();
return false;
}
//--></script>
<form action='<?php echo $this->request_url; ?>' method='post' name='selectorForm'>
	<fieldset class='actions'>
		<legend><?php echo $tableTitle; ?></legend>
		<?php
		switch ($this->selectType)
		{
			case '8':	{
							echo $this->lists['statistics'];
						}
						break;

			case '7':	{
							echo $this->lists['parentpositions'];
						}
						break;

			case '6':	{
							echo $this->lists['positions'];
						}
						break;

			case '5':	{
							echo $this->lists['events'];
						}
						break;

			case '4':	{
							echo $this->lists['playgrounds'];
						}
						break;

			case '3':	{
							echo $this->lists['persons'];
						}
						break;

			case '2':	{
							echo $this->lists['clubs'];
						}
						break;

			case '1':
			default:	{
							echo $this->lists['teams'];
						}
						break;
		}
		?>
	</fieldset>
</form>