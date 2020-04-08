<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jlxmlimports
 * @file       selectpage.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

switch ($this->selectType)
{
	case '10':	{		// New Club Selection
		$tableTitle = Text::_('Select Club to assign');
		echo '<script><!--' . "\n";
		echo 'var clubs=new Array;';

		foreach ($this->clubs as $club)
		{
			echo "clubs[$club->value]={";
			echo "value: '$club->value', ";
			echo "name: '" . addslashes($club->text) . "', ";
			echo "country: '" . addslashes($club->country) . "'";
			echo "};";
		}

		echo "\n" . '//--></script>';
	}
	break;

	case '9':	{		// Club & Team Selection
		$tableTitle = Text::_('Select Club&Team to assign');
		echo '<script><!--' . "\n";
		echo 'var clubsteams=new Array;';

		foreach ($this->clubsteams as $clubteam)
		{
			echo "clubsteams[$clubteam->value]={";
			echo "value: '$clubteam->value', ";
			echo "name: '" . addslashes($clubteam->text) . "', ";
			echo "clubname: '" . addslashes($clubteam->club_name) . "', ";
			echo "clubid: '" . addslashes($clubteam->club_id) . "', ";
			echo "country: '" . addslashes($clubteam->country) . "', ";
			echo "teamname: '" . addslashes($clubteam->team_name) . "'";
			echo "};";
		}

		echo "\n" . '//--></script>';
	}
	break;
	case '8':	{
		$tableTitle = Text::_('Select statistic to assign');
		echo '<script><!--' . "\n";
		echo 'var statistics=new Array;';

		foreach ($this->statistics as $statistic)
		{
			echo "statistics[$statistic->value]={";
			echo "value: '$statistic->value', ";
			echo "name: '" . addslashes($statistic->text) . "'";
			echo "};";
		}

		echo "\n" . '//--></script>';
	}
	break;
	case '7':	{
		$tableTitle = Text::_('Select parentposition to assign');
		echo '<script><!--' . "\n";
		echo 'var parentpositions=new Array;';

		foreach ($this->parentpositions as $parentposition)
		{
			echo "parentpositions[$parentposition->value]={";
			echo "value: '$parentposition->value', ";
			echo "name: '" . addslashes($parentposition->text) . "'";
			echo "};";
		}

		echo "\n" . '//--></script>';
	}
	break;
	case '6':	{
		$tableTitle = Text::_('Select position to assign');
		echo '<script><!--' . "\n";
		echo 'var positions=new Array;';

		foreach ($this->positions as $position)
		{
			echo "positions[$position->value]={";
			echo "value: '$position->value', ";
			echo "name: '" . addslashes($position->text) . "'";
			echo "};";
		}

		echo "\n" . '//--></script>';
	}
	break;
	case '5':	{
		$tableTitle = Text::_('Select event to assign');
		echo '<script><!--' . "\n";
		echo 'var events=new Array;';

		foreach ($this->events as $event)
		{
			echo "events[$event->value]={";
			echo "value: '$event->value', ";
			echo "name: '" . addslashes($event->text) . "'";
			echo "};";
		}

		echo "\n" . '//--></script>';
	}
	break;
	case '4':	{
		$tableTitle = Text::_('Select playground to assign');
		echo '<script><!--' . "\n";
		echo 'var playgrounds=new Array;';

		foreach ($this->playgrounds as $playground)
		{
			echo "playgrounds[$playground->value]={";
			echo "value: '$playground->value', ";
			echo "name: '" . addslashes($playground->text) . "', ";
			echo "short_name: '" . addslashes($playground->short_name) . "'";
			echo "};";
		}

		echo "\n" . '//--></script>';
	}
	break;
	case '3':	{
		$tableTitle = Text::_('Select person to assign');
		echo '<script><!--' . "\n";
		echo 'var persons=new Array;';

		foreach ($this->persons as $person)
		{
			echo "persons[$person->value]={";
			echo "value: '$person->value', ";
			echo "lastname: '" . addslashes($person->lastname) . "', ";
			echo "firstname: '" . addslashes($person->firstname) . "', ";
			echo "nickname: '" . addslashes($person->nickname) . "', ";
			echo "birthday: '$person->birthday'";
			echo "};";
		}

		echo "\n" . '//--></script>';
	}
	break;
	case '2':	{
		$tableTitle = Text::_('Select club to assign');
		echo '<script><!--' . "\n";
		echo 'var clubs=new Array;';

		foreach ($this->clubs as $club)
		{
			echo "clubs[$club->value]={value: '$club->value', clubname: '" . addslashes($club->text) . "'};";
		}

		echo "\n" . '//--></script>';
	}
	break;
	case '1':
	default:	{
		$tableTitle = Text::_('Select team to assign');
		echo '<script><!--' . "\n";
		echo 'var teams = new Array;';

		foreach ($this->teams as $team)
		{
			echo "teams[$team->value]={    value: '$team->value',
			middle_name: '" . addslashes($team->middle_name) . "',
			short_name: '" . addslashes($team->short_name) . "',
			teamname: '" . addslashes($team->name) . "',
			clubID: '" . $team->club_id . "'};";
		}

		echo 'var clubs=new Array;';

		foreach ($this->clubs as $club)
		{
			echo "clubs[$club->value]={value: '$club->value', clubname: '" . addslashes($club->text) . "'};";
		}

		echo "\n" . '//--></script>';
	}
	break;
}

$tableTitle .= " ($this->recordID)";
?>
<script><!--

//club_id
function insertClub(dClubID) {
	if(typeof(dClubID)=='undefined'){myClubID=this.document.selectorForm.clubID.value;}else{myClubID=dClubID;}
	opener.document.forms['adminForm'].dbClubID_<?php echo $this->recordID; ?>.disabled=false;
	opener.document.forms['adminForm'].dbClubID_<?php echo $this->recordID; ?>.value=myClubID;
	opener.document.forms['adminForm'].clubID_<?php echo $this->recordID; ?>.disabled=true;
	opener.document.getElementById('tetd<?php echo $this->recordID; ?>').style.backgroundColor='orange';
	opener.document.getElementById('cltd<?php echo $this->recordID; ?>').style.backgroundColor='lightgreen';
	opener.focus();
	window.close();
	return false;
}

function insertPerson() {
	myPersonID=this.document.selectorForm.personID.value;
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

function insertPlayground(){
	myPlaygroundID=this.document.selectorForm.playgroundID.value;
	opener.document.forms['adminForm'].choosePlayground_<?php echo $this->recordID; ?>.checked=true;
	opener.document.forms['adminForm'].choosePlayground_<?php echo $this->recordID; ?>.disabled=false;
	opener.document.forms['adminForm'].dbPlaygroundID_<?php echo $this->recordID; ?>.value=myPlaygroundID;
	opener.document.forms['adminForm'].dbPlaygroundID_<?php echo $this->recordID; ?>.disabled=false;
	opener.document.forms['adminForm'].dbPlaygroundClubID_<?php echo $this->recordID; ?>.disabled=false;
	  opener.document.forms['adminForm'].playgroundName_<?php echo $this->recordID; ?>.value=stripslashes(playgrounds[myPlaygroundID].name);	
	opener.document.forms['adminForm'].playgroundName_<?php echo $this->recordID; ?>.disabled=false;
	opener.document.forms['adminForm'].playgroundShortname_<?php echo $this->recordID; ?>.value=stripslashes(playgrounds[myPlaygroundID].short_name);
	  opener.document.forms['adminForm'].playgroundShortname_<?php echo $this->recordID; ?>.disabled=false;
	opener.document.forms['adminForm'].dbPlaygroundName_<?php echo $this->recordID; ?>.value=stripslashes(playgrounds[myPlaygroundID].name);
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

function fillDescription()
{
<?php
switch ($this->selectType)
{
	case '10':
?>
		ImportClub = opener.document.forms['adminForm'].impClubName_<?php echo $this->recordID; ?>.value;
		ImportCountry = opener.document.forms['adminForm'].impClubCountry_<?php echo $this->recordID; ?>.value;
		description = ImportClub;
		if (ImportCountry.length > 0)
		{
			description += " (" + ImportCountry + ")";
		}
		document.getElementById("description").innerHTML = description;
<?php
	break;
	case '9':
?>
		ImportClub = opener.document.forms['adminForm'].impClubName_<?php echo $this->recordID; ?>.value;
		ImportCountry = opener.document.forms['adminForm'].impClubCountry_<?php echo $this->recordID; ?>.value;
		ImportTeam = opener.document.forms['adminForm'].impTeamName_<?php echo $this->recordID; ?>.value;
		description = ImportClub;
		if (ImportCountry.length > 0)
		{
			description += " (" + ImportCountry + ")";
		}
		description += " - " + ImportTeam;
		document.getElementById("description").innerHTML = description;
<?php
	break;
}
?>
}

//--></script>
<form action='<?php echo $this->request_url; ?>' method='post'
	name='selectorForm'>
	<fieldset class='actions'>
		<legend>
			<?php echo $tableTitle; ?>
		</legend>
		<p id='description'></p>
		<?php
		switch ($this->selectType)
{
			case '10':	{
				echo "<script type='text/javascript'>fillDescription();</script>";
				echo $this->lists['clubs'];
			}
			break;

			case '9':	{
				echo "<script type='text/javascript'>fillDescription();</script>";
				echo $this->lists['clubsteams'];
			}
			break;

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
