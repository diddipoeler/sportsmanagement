<?php 
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

//require_once( JLG_PATH_SITE . DS . 'models' . DS . 'project.php' );

class sportsmanagementModelPerson extends JModel
{

	var $projectid		= 0;
	var $personid		  = 0;
	var $teamplayerid	= 0;
	var $person			  = null;
	var $teamplayer		= null;

	/**
	 * store person info specific to the project
	 * @var object
	 */
	var $_inproject   = null;
	/**
	 * data array for player history
	 * @var array
	 */
	var $_playerhistory = null;



 	function __construct()
  	{
 		parent::__construct();
  		$this->projectid	= JRequest::getInt( 'p', 0 );
 		$this->personid		= JRequest::getInt( 'pid', 0 );
 		$this->teamplayerid	= JRequest::getInt( 'pt', 0 );
 	}



	function getPerson()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $this->personid	= JRequest::getInt( 'pid', 0 );
        
        //$mainframe->enqueueMessage(JText::_('getPerson personid<br><pre>'.print_r($this->personid,true).'</pre>'),'');
        
       // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        
        //if ( is_null( $this->person ) )
		//{
		// Select some fields
		$query->select('p.*');
        $query->select('CASE WHEN CHAR_LENGTH( p.alias ) THEN CONCAT_WS( \':\', p.id, p.alias ) ELSE p.id END AS slug ');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p ');
        $query->where('p.id='.$db->Quote($this->personid));
        
        //$mainframe->enqueueMessage(JText::_('getPerson query<br><pre>'.print_r($query,true).'</pre>'),'');
        
		$db->setQuery($query);
		$this->person = $db->loadObject();
		//}
		return $this->person;
	}

	function &getReferee()
	{
		if ( is_null( $this->_inproject ) )
		{
			$query = ' SELECT tp.*, pos.name AS position_name '
					. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS tp '
							. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id = tp.project_position_id '
									. ' WHERE tp.project_id = '. $this->_db->Quote($this->projectid)
									. '   AND tp.person_id = '. $this->_db->Quote($this->personid)
									;
									$this->_db->setQuery($query);
									$this->_inproject = $this->_db->loadObject();
		}
		return $this->_inproject;
	}

	function getPositionEventTypes( $positionId = 0 )
	{
		$result = array();

		$query = '	SELECT	pet.*,
				et.name,
				et.icon

				FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_position_eventtype AS pet
				INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype AS et ON et.id = pet.eventtype_id
				INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event AS me ON et.id = me.event_type_id
				WHERE me.project_id=' . $this->projectid;

		if ( $positionId > 0 )
		{
			$query .= ' AND pet.position_id = ' . (int)$positionId;
		}
		$query .= ' ORDER BY pet.ordering';

		$this->_db->setQuery( $query );
		$result = $this->_db->loadObjectList();

		if ( $result )
		{
			if ( $positionId )
			{
				return $result;
			}
			else
			{
				$posEvents = array();
				foreach ( $result as $r )
				{
					$posEvents[$r->position_id][] = $r;
				}
				return ( $posEvents );
			}
		}
		return array();
	}


	/**
	 * get person history across all projects, with team, season, position,... info
	 *
	 * @param int $person_id , linked to player_id from Person object
	 * @param int $order ordering for season and league, default is ASC ordering
	 * @param string $filter e.g. "s.name = 2007/2008", default empty string
	 * @return array of objects
	 */
	function getRefereeHistory($order = 'ASC')
	{
		$personid = $this->personid;

		$query = ' SELECT	p.id AS person_id, '
				. ' tt.project_id, '
				. ' p.firstname AS fname, '
				. ' p.lastname AS lname, '
				. ' pj.name AS pname, '
				. ' s.name AS sname, '
				. ' pos.name AS position, '
				. ' COUNT(mr.id) AS matchesCount '
				. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee AS mr '
				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m ON m.id = mr.match_id '
				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p ON p.id = mr.project_referee_id '
				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS tt ON tt.id = m.projectteam1_id '
				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS pj ON pj.id = tt.project_id '
				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_season AS s ON s.id = pj.season_id '
				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_league AS l ON l.id = pj.league_id '
				. ' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id = mr.project_position_id '
				. ' WHERE p.id = ' . (int)$personid
				. ' GROUP BY (tt.project_id) '
				. ' ORDER BY s.ordering ASC, l.ordering ASC, pj.name ASC ';

		$this->_db->setQuery( $query );
		$results = $this->_db->loadObjectList();
		return $results;
	}

	function getContactID( $catid )
	{
		$person = self::getPerson();

		$query = '	SELECT	id
				FROM #__contact_details
				WHERE user_id = ' . $person->jl_user_id . '
						AND catid=' . $catid;

		$this->_db->setQuery( $query );
		$contact_id = $this->_db->loadResult();

		return $contact_id;
	}

	function getRounds( $roundcodestart, $roundcodeend )
	{
		$projectid = $this->projectid;

		$thisround = 0;
		$query = "	SELECT	id
				FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_round
				WHERE project_id='" . (int)$projectid . "'
				AND roundcode>='" . (int)$roundcodestart . "'
				AND roundcode<='" . (int)$roundcodeend . "'
				ORDER BY round_date_first";

		$this->_db->setQuery( $query );
		$rows = $this->_db->loadResultArray();

		$rounds = array();
		if ( count( $rows ) > 0 )
		{
			$startround =& $this->getTable( 'Round', 'Table' );
			$startround->load( $rows[0] );
			$rounds[0] = $startround;
			$endround =& $this->getTable( 'Round', 'Table' );
			$endround->load( end( $rows ) );
			$rounds[1] = $endround;
		}
		return $rounds;
	}

	/**
	 * get all positions the player was assigned too in different projects
	 * @return unknown_type
	 */
	function getAllEvents()
	{
		$history = &$this->getPlayerHistory();
		$positionhistory = array();
		foreach($history as $h)
		{
			if (!in_array($h->position_id, $positionhistory))
				$positionhistory[] = $h->position_id;
		}
		if (!count($positionhistory)) {
			return array();
		}
		$query = ' SELECT et.* '
				. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype AS et '
				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position_eventtype AS pet ON pet.eventtype_id = et.id '
				. ' WHERE published = 1 '
				. '   AND pet.position_id IN ('. implode(',', $positionhistory) .')'
				. ' ORDER BY et.ordering '
				;
				$this->_db->setQuery( $query );
				$info = $this->_db->loadObjectList();
		return $info;
	}

	/**
	 * get player events total, global or per project
	 * @param int $eventid
	 * @param int $projectid, all projects if null (default)
	 * @return array
	 */
	function getPlayerEvents($eventid, $projectid = null, $projectteamid = null)
	{
		$query = ' SELECT	SUM(me.event_sum) as total '
				. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event AS me '
				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tp ON me.teamplayer_id = tp.id '
				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.id = tp.projectteam_id '
				. ' WHERE me.event_type_id=' . $this->_db->Quote((int) $eventid)
				. ' AND tp.person_id = ' . $this->_db->Quote((int) $this->personid)
				;
				if ($projectteamid)
				{
					$query .= ' AND pt.id='.$this->_db->Quote((int) $projectteamid);
				}
				if ($projectid)
				{
					$query .= ' AND pt.project_id=' . $this->_db->Quote((int) $projectid);
				}
				$query .= ' GROUP BY tp.person_id';

				$this->_db->setQuery($query);
				$result = $this->_db->loadResult();
				return $result;
	}

	function getInOutStats( $project_id, $person_id )
	{
		$query = ' SELECT	sum(IF(came_in=0,1,0)+max(came_in=1)) AS played, '
				. ' sum(mp.came_in) AS sub_in, '
				. ' sum(mp.out = 1) AS sub_out '
				. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp '
				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m ON mp.match_id = m.id '
				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tp ON tp.id = mp.teamplayer_id '
				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON m.projectteam1_id = pt.id '
				. ' WHERE tp.person_id=' . $this->_db->Quote((int)$person_id)
				. ' AND pt.project_id=' . $this->_db->Quote((int)$project_id)
				. ' AND tp.published = 1 ';
		$this->_db->setQuery( $query );
		$inoutstat = $this->_db->loadObjectList();

		return $inoutstat;
	}

	function getPlayerChangedRecipients()
	{
		$query = "SELECT email
				FROM #__users
				WHERE usertype = 'Super Administrator'
				OR usertype = 'Administrator'";

		$this->_db->setQuery( $query );

		return $this->_db->loadResultArray();
	}

	function sendMailTo($listOfRecipients, $subject, $message)
	{
		$mainframe	= JFactory::getApplication();
		$mailFrom = $mainframe->getCfg('mailfrom');
		$fromName = $mainframe->getCfg('fromname');
		JUtility::sendMail( $mailFrom, $fromName, $listOfRecipients, $subject, $message );
	}

	function getAllowed($config_editOwnPlayer)
	{
		$user = JFactory::getUser();
		$allowed=false;
		if(self::_isAdmin($user) || self::_isOwnPlayer($user,$config_editOwnPlayer)) {
			$allowed=true;
		}
		return $allowed;
	}

	function _isAdmin($user)
	{
	   // Get a refrence of the page instance in joomla
		$document = JFactory::getDocument();
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
		$allowed=false;
		if ($user->id > 0)
		{
			$project=&$this->getProject();

			// Check if user is project admin or editor
			if ( $this->isUserProjectAdminOrEditor($user->id,$project) )
			{
				$allowed = true;
			}
				
			// If not, then check if user has ACL rights
			if (!$allowed)
			{
				if (!$user->authorise('person.edit', $option)) {
					$allowed = false;
				} else {
					$allowed = true;
				}
			}
		}
		return $allowed;
	}

	function _isOwnPlayer($user,$config_editOwnPlayer)
	{
		if($user->id > 0)
		{
			$person=$this->getPerson();
			return $config_editOwnPlayer && $user->id == $person->user_id;
		}
		return false;
	}

	function isEditAllowed($config_editOwnPlayer,$config_editAllowed)
	{
		$allowed = false;
		$user = JFactory::getUser();
		if($user->id > 0)
		{
			if(JoomleagueModelPerson::_isAdmin($user) || ($config_editAllowed && JoomleagueModelPerson::_isOwnPlayer($user,$config_editOwnPlayer))) {
				$allowed = true;
			}
			return $allowed;
		}
		return false;
	}

	function _getProjectTeamIds4UserId($userId)
	{
		// team_player
		$query='	SELECT tp.projectteam_id
				FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pr
				INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tp ON tp.person_id=pr.id
				WHERE pr.user_id='.$userId.'
						AND pr.published = 1
						AND tp.published = 1 ';
		$this->_db->setQuery($query);
		$projectTeamIds=array();
		$projectTeamIds=$this->_db->loadResultArray();
		// team_staff
		$query='	SELECT ts.projectteam_id
				FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_person pr
				INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_staff AS ts ON ts.person_id=pr.id
				WHERE pr.user_id='.$userId.'
						AND pr.published = 1
						AND ts.published = 1';
		$this->_db->setQuery($query);
		$projectTeamIds=array_merge($projectTeamIds,$this->_db->loadResultArray());
		return $projectTeamIds;
	}

	function isContactDataVisible($config_showContactDataOnlyTeamMembers)
	{
		$user = JFactory::getUser();
		$result=true;
		// project admin and editor,see contact always
		if($config_showContactDataOnlyTeamMembers && !sportsmanagementModelProject::isUserProjectAdminOrEditor($user->id,sportsmanagementModelProject::getProject()))
		{
			$result=false;
			if($user->id > 0)
			{
				// get project_team id to user-id from team-player or team-staff
				$projectTeamIds = self::_getProjectTeamIds4UserId($user->id);
				$teamplayer = sportsmanagementModelPlayer::getTeamPlayer();
				if(isset($teamplayer->projectteam_id)) {
					$result=in_array($teamplayer->projectteam_id, $projectTeamIds);
				}
			}
		}
		return $result;
	}

}
?>