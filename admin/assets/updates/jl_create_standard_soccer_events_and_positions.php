<?php
/**
 * Joomleague Component script file to CREATE standard soccer events,positions and position-eventtypes of JoomLeague 1.5
 *
 * @author	Kurt Norgaz
 * @package	JoomLeague
 * @since	1.5 - 2010-06-17
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$version			= '2.0.43.b3fd04d-a';
$updateFileDate		= '2012-09-13';
$updateFileTime		= '00:05';
$updateDescription	= '<span style="color:green">Create standard soccer events,positions and position-events for use with JoomLeague</span>';
$excludeFile		= 'false';

function PrintStepResult($result)
{
	if ($result)
	{
		$output=' - <span style="color:green">'.JText::_('SUCCESS').'</span>';
	}
	else
	{
		$output=' - <span style="color:red">'.JText::_('FAILED').'</span>';
	}

	return $output;
}

function addSportsType()
{
	$result	= false;
	$db		= JFactory::getDBO();

	echo JText::sprintf	(	'Adding the sports-type [%1$s] to table [%2$s] if it does not exist yet!',
							'<strong>'.'COM_SPORTSMANAGEMENT_ST_SOCCER'.'</strong>',
							'<strong>'.'#__joomleague_sports_type'.'</strong>'
						);

	$query="SELECT id FROM #__".COM_SPORTSMANAGEMENT_TABLE."_sports_type WHERE name='COM_SPORTSMANAGEMENT_ST_SOCCER'";
	$db->setQuery($query);
	if (!$dbresult=$db->loadObject())
	{
		// Add new sportstype Soccer to #__joomleague_sports_type
		$queryAdd="INSERT INTO #__".COM_SPORTSMANAGEMENT_TABLE."_sports_type (`name`) VALUES ('COM_SPORTSMANAGEMENT_ST_SOCCER')";
		$db->setQuery($queryAdd);
		$result=$db->query();
	}

	echo PrintStepResult($result).'<br />';
	if (!$result) { echo JText::_ ('DO NOT WORRY... Surely the sports-type soccer was already existing in your database!!!').'<br />'; }

	return '';
}

function build_SelectQuery($tablename,$param1)
{
	$query="SELECT * FROM #__".COM_SPORTSMANAGEMENT_TABLE."_".$tablename." WHERE name='".$param1."'";
	//echo '<br />'.$query.'<br />';
	return $query;
}

function build_InsertQuery_Position($tablename,$param1,$param2,$param3,$param4,$sports_type_id,$order_count)
{
	$alias=JFilterOutput::stringURLSafe($param1);
	$query="INSERT INTO #__".COM_SPORTSMANAGEMENT_TABLE."_".$tablename." (`name`,`alias`,`".$param2."`,`parent_id`,`sports_type_id`,`published`,`ordering`) VALUES ('".$param1."','".$alias."','".$param4."','".$param3."','".$sports_type_id."','1','".$order_count."')";
	//echo '<br />'.$query.'<br />';
	return $query;
}

function build_InsertQuery_Event($tablename,$param1,$param2,$sports_type_id,$order_count)
{
	$alias=JFilterOutput::stringURLSafe($param1);
	$query="INSERT INTO #__".COM_SPORTSMANAGEMENT_TABLE."_".$tablename." (`name`,`alias`,`icon`,`sports_type_id`,`published`,`ordering`) VALUES ('".$param1."','".$alias."','".$param2."','".$sports_type_id."','1','".$order_count."')";
	//echo '<br />'.$query.'<br />';
	return $query;
}

function build_InsertQuery_PositionEventType($param1,$param2)
{
	$query="	INSERT INTO	#__".COM_SPORTSMANAGEMENT_TABLE."_position_eventtype
				(`position_id`,`eventtype_id`)
				VALUES
				('".$param1."','".$param2."')";
	//echo '<br />'.$query.'<br />';
	return $query;
}

function addStandardsForSoccer()
{
	$events_player		= array();
	$events_staff		= array();
	$events_referees	= array();
	$events_clubstaff	= array();
	$PlayersPositions	= array();
	$StaffPositions		= array();
	$RefereePositions	= array();
	$ClubStaffPositions	= array();

	$result				= false;
	$db					= JFactory::getDBO();

	if ('AddEvents' == 'AddEvents')
	{
		echo JText::sprintf('Adding standard soccer events to table table [%s]','<b>'.'#__joomleague_eventtype'.'</b>');

		$squery				= 'SELECT * FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype WHERE name=`%s`';
		$isquery			= 'INSERT INTO #__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype (`name`,`icon`) VALUES (`%1$s`,`%2$s`)';
		$query				= "SELECT * FROM #__".COM_SPORTSMANAGEMENT_TABLE."_sports_type WHERE name='COM_SPORTSMANAGEMENT_ST_SOCCER'"; $db->setQuery($query); $sports_type=$db->loadObject();

		$newEventName='COM_SPORTSMANAGEMENT_E_GOAL';
		$newEventIcon='images/com_sportsmanagement/database/events/soccer/goal.png';
		$query=build_SelectQuery('eventtypes',$newEventName); $db->setQuery($query);
		if (!$object=$db->loadObject())
		{
			$query				= build_InsertQuery_Event('eventtype',$newEventName,$newEventIcon,$sports_type->id,1); $db->setQuery($query);
			$result				= $db->query();
			$events_player['0']	= $db->insertid();
		}
		else
		{
			$events_player['0']	= $object->id;
		}

		$newEventName='COM_SPORTSMANAGEMENT_E_ASSISTS';
		$newEventIcon='images/com_sportsmanagement/database/events/soccer/assists.png';
		$query=build_SelectQuery('eventtypes',$newEventName); $db->setQuery($query);
		if (!$object=$db->loadObject())
		{
			$query				= build_InsertQuery_Event('eventtype',$newEventName,$newEventIcon,$sports_type->id,2); $db->setQuery($query);
			$result				= $db->query();
			$events_player['1']	= $db->insertid();
		}
		else
		{
			$events_player['1']	= $object->id;
		}

		$newEventName='COM_SPORTSMANAGEMENT_E_YELLOW_CARD';
		$newEventIcon='images/com_sportsmanagement/database/events/soccer/yellow_card.png';
		$query=build_SelectQuery('eventtypes',$newEventName); $db->setQuery($query);
		if (!$object=$db->loadObject())
		{
			$query					= build_InsertQuery_Event('eventtype',$newEventName,$newEventIcon,$sports_type->id,3); $db->setQuery($query);
			$result					= $db->query();
			$events_player['2']		= $db->insertid();
			$events_staff['2']		= $db->insertid();
			$events_clubstaff['2']	= $db->insertid();
			$events_referees['2']	= $db->insertid();
		}
		else
		{
			$events_player['2']		= $object->id;
			$events_staff['2']		= $object->id;
			$events_clubstaff['2']	= $object->id;
			$events_referees['2']	= $object->id;
		}

		$newEventName='COM_SPORTSMANAGEMENT_E_YELLOW-RED_CARD';
		$newEventIcon='images/com_sportsmanagement/database/events/soccer/yellow_red_card.png';
		$query=build_SelectQuery('eventtypes',$newEventName); $db->setQuery($query);
		if (!$object=$db->loadObject())
		{
			$query					= build_InsertQuery_Event('eventtype',$newEventName,$newEventIcon,$sports_type->id,4); $db->setQuery($query);
			$result					= $db->query();
			$events_player['3']		= $db->insertid();
			$events_staff['3']		= $db->insertid();
			$events_clubstaff['3']	= $db->insertid();
			$events_referees['3']	= $db->insertid();
		}
		else
		{
			$events_player['3']		= $object->id;
			$events_staff['3']		= $object->id;
			$events_clubstaff['3']	= $object->id;
			$events_referees['3']	= $object->id;
		}

		$newEventName='COM_SPORTSMANAGEMENT_E_RED_CARD';
		$newEventIcon='images/com_sportsmanagement/database/events/soccer/red_card.png';
		$query=build_SelectQuery('eventtypes',$newEventName); $db->setQuery($query);
		if (!$object=$db->loadObject())
		{
			$query					= build_InsertQuery_Event('eventtype',$newEventName,$newEventIcon,$sports_type->id,5); $db->setQuery($query);
			$result					= $db->query();
			$events_player['4']		= $db->insertid();
			$events_staff['4']		= $db->insertid();
			$events_clubstaff['4']	= $db->insertid();
			$events_referees['4']	= $db->insertid();
		}
		else
		{
			$events_player['4']		= $object->id;
			$events_staff['4']		= $object->id;
			$events_clubstaff['4']	= $object->id;
			$events_referees['4']	= $object->id;
		}

		$newEventName='COM_SPORTSMANAGEMENT_E_FOUL';
		$newEventIcon='images/com_sportsmanagement/database/events/soccer/foul.png';
		$query=build_SelectQuery('eventtypes',$newEventName); $db->setQuery($query);
		if (!$object=$db->loadObject())
		{
			$query					= build_InsertQuery_Event('eventtype',$newEventName,$newEventIcon,$sports_type->id,6); $db->setQuery($query);
			$result					= $db->query();
			$events_player['5']		= $db->insertid();
			$events_referees['5']	= $db->insertid();
		}
		else
		{
			$events_player['5']		= $object->id;
			$events_referees['5']	= $object->id;
		}

		$newEventName='COM_SPORTSMANAGEMENT_E_FOUL_TIME';
		$newEventIcon='images/com_sportsmanagement/database/events/soccer/foul_time.png';
		$query=build_SelectQuery('eventtypes',$newEventName); $db->setQuery($query);
		if (!$object=$db->loadObject())
		{
			$query					= build_InsertQuery_Event('eventtype',$newEventName,$newEventIcon,$sports_type->id,7); $db->setQuery($query);
			$result					= $db->query();
			$events_player['6']		= $db->insertid();
			$events_referees['6']	= $db->insertid();
		}
		else
		{
			$events_player['6']		= $object->id;
			$events_referees['6']	= $object->id;
		}

		$newEventName='COM_SPORTSMANAGEMENT_E_OWN_GOAL';
		$newEventIcon='images/com_sportsmanagement/database/events/soccer/own_goal.png';
		$query=build_SelectQuery('eventtypes',$newEventName); $db->setQuery($query);
		if (!$object=$db->loadObject())
		{
			$query				= build_InsertQuery_Event('eventtype',$newEventName,$newEventIcon,$sports_type->id,8); $db->setQuery($query);
			$result				= $db->query();
			$events_player['7']	= $db->insertid();
		}
		else
		{
			$events_player['7']	= $object->id;
		}

		$newEventName='COM_SPORTSMANAGEMENT_E_PENALTY_GOAL';
		$newEventIcon='images/com_sportsmanagement/database/events/soccer/penalty_goal.png';
		$query=build_SelectQuery('eventtypes',$newEventName); $db->setQuery($query);
		if (!$object=$db->loadObject())
		{
			$query					= build_InsertQuery_Event('eventtype',$newEventName,$newEventIcon,$sports_type->id,9); $db->setQuery($query);
			$result					= $db->query();
			$events_player['8']		= $db->insertid();
			$events_referees['8']	= $db->insertid();
		}
		else
		{
			$events_player['8']		= $object->id;
			$events_referees['8']	= $object->id;
		}

		$newEventName='COM_SPORTSMANAGEMENT_E_INJURY';
		$newEventIcon='images/com_sportsmanagement/database/events/soccer/injured.png';
		$query=build_SelectQuery('eventtypes',$newEventName); $db->setQuery($query);
		if (!$object=$db->loadObject())
		{
			$query					= build_InsertQuery_Event('eventtype',$newEventName,$newEventIcon,$sports_type->id,10); $db->setQuery($query);
			$result					= $db->query();
			$events_player['9']		= $db->insertid();
			$events_staff['9']		= $db->insertid();
			$events_clubstaff['9']	= $db->insertid();
			$events_referees['9']	= $db->insertid();
		}
		else
		{
			$events_player['9']		= $object->id;
			$events_staff['9']		= $object->id;
			$events_clubstaff['9']	= $object->id;
			$events_referees['9']	= $object->id;
		}

		echo PrintStepResult($result).'<br />';
		if (!$result) { echo JText::_ ('DO NOT WORRY... Surely at least one of the events was already existing in your database!!!').'<br />'; }
		echo '<br />';
	}

	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

	$result=false;

	if ('AddPositions' == 'AddPositions')
	{
		echo JText::sprintf('Adding standard soccer positions to table table [%s]','<b>'.'#__joomleague_position'.'</b>');

		if ('AddGeneralPlayersPositions' == 'AddGeneralPlayersPositions')
		{
			// Add new Parent position to PlayersPositions
			$newPosName='COM_SPORTSMANAGEMENT_F_PLAYERS'; $newPosSwitch='persontype'; $newPosParent='0'; $newPosContent='1';
			$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
			if (!$dbresult=$db->loadObject())
			{
				$query					= build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,1); $db->setQuery($query);
				$result					= $db->query();
				$ParentID				= mysql_insert_id();
				$PlayersPositions['0']	= mysql_insert_id();
			}
			else
			{
				$ParentID				= $dbresult->id;
				$PlayersPositions['0']	= $dbresult->id;
			}

			if ('AddGeneralPlayersChildPositions' == 'AddGeneralPlayersChildPositions')
			{
				// Add new Child positions to PlayersPositions

				// New Child position for PlayersPositions
				$newPosName='COM_SPORTSMANAGEMENT_P_GOALKEEPER'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='1';
				$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
				if (!$object=$db->loadObject())
				{
					$query					= build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,2); $db->setQuery($query);
					$result					= $db->query();
					$PlayersPositions['1']	= mysql_insert_id();
				}
				else
				{
					$PlayersPositions['1']	= $object->id;
				}

				// New Child position for PlayersPositions
				$newPosName='COM_SPORTSMANAGEMENT_P_DEFENDER'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='1';
				$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
				if (!$object=$db->loadObject())
				{
					$query	= build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,3); $db->setQuery($query);
					$result	= $db->query();
					$PlayersPositions['2']	= mysql_insert_id();
				}
				else
				{
					$PlayersPositions['2']	= $object->id;
				}

				// New Child position for PlayersPositions
				$newPosName='COM_SPORTSMANAGEMENT_P_MIDFIELDER'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='1';
				$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
				if (!$object=$db->loadObject())
				{
					$query					= build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,4); $db->setQuery($query);
					$result					= $db->query();
					$PlayersPositions['3']	= mysql_insert_id();
				}
				else
				{
					$PlayersPositions['3']	= $object->id;
				}

				// New Child position for PlayersPositions
				$newPosName='COM_SPORTSMANAGEMENT_P_FORWARD'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='1';
				$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
				if (!$object=$db->loadObject())
				{
					$query					= build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,5); $db->setQuery($query);
					$result					= $db->query();
					$PlayersPositions['4']	= mysql_insert_id();
				}
				else
				{
					$PlayersPositions['4']	= $object->id;
				}
			}
		}

		//----------------------------------------------------------------------

		if ('AddGeneralStaffPositions' == 'AddGeneralStaffPositions')
		{
			if ('AddGeneralStaffTeamStaffPositions' == 'AddGeneralStaffTeamStaffPositions')
			{
				// Add new Parent position to StaffPositions
				$newPosName='COM_SPORTSMANAGEMENT_F_TEAM_STAFF'; $newPosSwitch='persontype'; $newPosParent='0'; $newPosContent='2';
				$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
				if (!$dbresult=$db->loadObject())
				{
					$query					= build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,6); $db->setQuery($query);
					$result					= $db->query();
					$ParentID				= mysql_insert_id();
					$StaffPositions['0']	= mysql_insert_id();
				}
				else
				{
					$ParentID				= $dbresult->id;
					$StaffPositions['0']	= $dbresult->id;
				}
			}

			//----------------------------------------------------------------------

			if ('AddGeneralStaffCoachesPositions' == 'AddGeneralStaffCoachesPositions')
			{
				// Add new Parent position to StaffPositions
				$newPosName='COM_SPORTSMANAGEMENT_F_COACHES'; $newPosSwitch='persontype'; $newPosParent='0'; $newPosContent='2';
				$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
				if (!$dbresult=$db->loadObject())
				{
					$query					= build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,7); $db->setQuery($query);
					$result					= $db->query();
					$ParentID				= mysql_insert_id();
					$StaffPositions['1']	= mysql_insert_id();
				}
				else
				{
					$ParentID				= $dbresult->id;
					$StaffPositions['1']	= $dbresult->id;
				}

				if ('AddGeneralStaffCoachesChildPositions' == 'AddGeneralStaffCoachesChildPositions')
				{
					// New Child position for StaffPositions
					$newPosName='COM_SPORTSMANAGEMENT_F_COACH'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='2';
					$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
					if (!$object=$db->loadObject())
					{
						$query					= build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,8); $db->setQuery($query);
						$result					= $db->query();
						$StaffPositions['2']	= mysql_insert_id();
					}
					else
					{
						$StaffPositions['2']	= $object->id;
					}

					// New Child position for StaffPositions
					$newPosName='COM_SPORTSMANAGEMENT_F_HEAD_COACH'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='2';
					$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
					if (!$object=$db->loadObject())
					{
						$query					= build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,9); $db->setQuery($query);
						$result					= $db->query();
						$StaffPositions['3']	= mysql_insert_id();
					}
					else
					{
						$StaffPositions['3']	= $object->id;
					}
				}
			}

			//----------------------------------------------------------------------

			if ('AddGeneralStaffMaintainerteamPositions' == 'AddGeneralStaffMaintainerteamPositions')
			{
				// Add new Parent position to StaffPositions
				$newPosName='COM_SPORTSMANAGEMENT_F_MAINTAINER_TEAM'; $newPosSwitch='persontype'; $newPosParent='0'; $newPosContent='2';
				$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
				if (!$dbresult=$db->loadObject())
				{
					$query					= build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,10); $db->setQuery($query);
					$result					= $db->query();
					$ParentID				= mysql_insert_id();
					$StaffPositions['4']	= mysql_insert_id();
				}
				else
				{
					$ParentID				= $dbresult->id;
					$StaffPositions['4']	= $dbresult->id;
				}

				if ('AddGeneralStaffMaintainerChildPositions' == 'AddGeneralStaffMaintainerChildPositions')
				{
					// New Child position for StaffPositions
					$newPosName='COM_SPORTSMANAGEMENT_F_MAINTAINER'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='2';
					$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
					if (!$object=$db->loadObject())
					{
						$query					= build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,11); $db->setQuery($query);
						$result					= $db->query();
						$StaffPositions['5']	= mysql_insert_id();
					}
					else
					{
						$StaffPositions['5']	= $object->id;
					}
				}
			}

			//----------------------------------------------------------------------

			if ('AddGeneralStaffMedicalStaffPositions' == 'AddGeneralStaffMedicalStaffPositions')
			{
				// Add new Parent position to StaffPositions
				$newPosName='COM_SPORTSMANAGEMENT_F_MEDICAL_STAFF'; $newPosSwitch='persontype'; $newPosParent='0'; $newPosContent='2';
				$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
				if (!$dbresult=$db->loadObject())
				{
					$query					= build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,12); $db->setQuery($query);
					$result					= $db->query();
					$ParentID				= mysql_insert_id();
					$StaffPositions['6']	= mysql_insert_id();
				}
				else
				{
					$ParentID				= $dbresult->id;
					$StaffPositions['6']	= $dbresult->id;
				}
			}
		}

		//----------------------------------------------------------------------

		if ('AddGeneralRefereesPositions' == 'AddGeneralRefereesPositions')
		{
			// Add new Parent position to RefereesPositions
			$newPosName='COM_SPORTSMANAGEMENT_F_REFEREES'; $newPosSwitch='persontype'; $newPosParent='0'; $newPosContent='3';
			$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
			if (!$dbresult=$db->loadObject())
			{
				$query					= build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,13); $db->setQuery($query);
				$result					= $db->query();
				$ParentID				= mysql_insert_id();
				$RefereePositions['0']	= mysql_insert_id();
			}
			else
			{
				$ParentID				= $dbresult->id;
				$RefereePositions['0']	= $dbresult->id;
			}

			if ('AddGeneralRefereesChildPositions' == 'AddGeneralRefereesChildPositions')
			{
				// New Child position for RefereePositions
				$newPosName='COM_SPORTSMANAGEMENT_F_CENTER_REFEREE'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='3';
				$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
				if (!$object=$db->loadObject())
				{
					$query					= build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,14); $db->setQuery($query);
					$result					= $db->query();
					$RefereePositions['1']	= mysql_insert_id();
				}
				else
				{
					$RefereePositions['1']	= $object->id;
				}

				// New Child position for RefereePositions
				$newPosName='COM_SPORTSMANAGEMENT_F_LINESMAN'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='3';
				$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
				if (!$object=$db->loadObject())
				{
					$query					= build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,15); $db->setQuery($query);
					$result					= $db->query();
					$RefereePositions['2']	= mysql_insert_id();
				}
				else
				{
					$RefereePositions['2']	= $object->id;
				}

				// New Child position for RefereePositions
				$newPosName='COM_SPORTSMANAGEMENT_F_FOURTH_OFFICIAL'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='3';
				$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
				if (!$object=$db->loadObject())
				{
					$query					= build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,16); $db->setQuery($query);
					$result					= $db->query();
					$RefereePositions['3']	= mysql_insert_id();
				}
				else
				{
					$RefereePositions['3']	= $object->id;
				}

				// New Child position for RefereePositions
				$newPosName	= 'COM_SPORTSMANAGEMENT_F_FIFTH_OFFICIAL'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='3';
				$query		= build_SelectQuery('position',$newPosName); $db->setQuery($query);
				if (!$object=$db->loadObject())
				{
					$query					= build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,17); $db->setQuery($query);
					$result					= $db->query();
					$RefereePositions['4']	= mysql_insert_id();
				}
				else
				{
					$RefereePositions['4']	= $object->id;
				}
			}
		}

		//----------------------------------------------------------------------

		if ('AddGeneralClubstaffPositions' == 'AddGeneralClubstaffPositions')
		{
			// Add new Parent position to ClubStaffPositions
			$newPosName	= 'COM_SPORTSMANAGEMENT_F_CLUB_STAFF'; $newPosSwitch='persontype'; $newPosParent='0'; $newPosContent='4';
			$query=build_SelectQuery('position',$newPosName); $db->setQuery($query);
			if (!$dbresult=$db->loadObject())
			{
				$query						= build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,18); $db->setQuery($query);
				$result						= $db->query();
				$ParentID					= mysql_insert_id();
				$ClubStaffPositions['0']	= mysql_insert_id();
			}
			else
			{
				$ParentID					= $dbresult->id;
				$ClubStaffPositions['0']	= $dbresult->id;
			}


			if ('AddGeneralClubstaffChildPositions' == 'AddGeneralClubstaffChildPositions')
			{
				// New Child position for ClubStaffPositions
				$newPosName='COM_SPORTSMANAGEMENT_F_CLUB_MANAGER'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='4';
				$query=build_SelectQuery('position',$newPosName); $db->setQuery($query); // hier die 4 als newposcontent einf�gen
				if (!$object=$db->loadObject())
				{
					$query						= build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,19); $db->setQuery($query);
					$result						= $db->query();
					$ClubStaffPositions['1']	= mysql_insert_id();
				}
				else
				{
					$ClubStaffPositions['1']	= $object->id;
				}

				$newPosName='COM_SPORTSMANAGEMENT_F_CLUB_YOUTH_MANAGER'; $newPosSwitch='persontype'; $newPosParent=$ParentID; $newPosContent='4';
				$query=build_SelectQuery('position',$newPosName); $db->setQuery($query); // hier die 4 als newposcontent einf�gen
				if (!$object=$db->loadObject())
				{
					$query						= build_InsertQuery_Position('position',$newPosName,$newPosSwitch,$newPosParent,$newPosContent,$sports_type->id,20); $db->setQuery($query);
					$result						= $db->query();
					$ClubStaffPositions['2']	= mysql_insert_id();
				}
				else
				{
					$ClubStaffPositions['2']	= $object->id;
				}
			}
		}

		echo PrintStepResult($result).'<br />';
		if (!$result) { echo JText::_ ('DO NOT WORRY... Surely at least one of the positions was already existing in your database!!!').'<br />'; }
		echo '<br />';
	}

	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

	$result		= false;

	echo JText::sprintf('Adding standard position-related-events for soccer to table table [%s]','<b>'.'#__joomleague_position_eventtype'.'</b>');

	foreach ($PlayersPositions AS $ppkey => $ppid)
	{
		foreach ($events_player AS $epkey => $epid)
		{
			$query=build_InsertQuery_PositionEventType($ppid,$epid); $db->setQuery($query); $result=$db->query();
		}
	}

	foreach ($StaffPositions AS $spkey => $spid)
	{
		foreach ($events_staff AS $eskey => $esid)
		{
			$query=build_InsertQuery_PositionEventType($spid,$esid); $db->setQuery($query); $result=$db->query();
		}
	}

	foreach ($RefereePositions AS $rkey => $rid)
	{
		foreach ($events_referees AS $erkey => $erid)
		{
			$query=build_InsertQuery_PositionEventType($rid,$erid); $db->setQuery($query); $result=$db->query();
		}
	}

	foreach ($ClubStaffPositions AS $cskey => $csid)
	{
		foreach ($events_clubstaff AS $ecskey => $escid)
		{
			$query=build_InsertQuery_PositionEventType($csid,$escid); $db->setQuery($query); $result=$db->query();
		}
	}

	echo PrintStepResult($result).'<br />';
	if (!$result) { echo JText::_ ('DO NOT WORRY... Surely at least one of the position related events was already existing in your database!!!').'<br />'; }

	return '';
}

?>
<hr>
<?php
	$output=JText::sprintf(	'JoomLeague v%1$s - Update filedate/time: %2$s / %3$s %4$s',
								$version,$updateFileDate,$updateFileTime,'<br />'.$updateDescription.'<br />');
	JToolBarHelper::title($output);

	echo addSportsType().'<br />';
	echo addStandardsForSoccer().'<br />';
?>