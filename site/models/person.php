<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

//require_once( JLG_PATH_SITE . DS . 'models' . DS . 'project.php' );

/**
 * sportsmanagementModelPerson
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
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



 	/**
 	 * sportsmanagementModelPerson::__construct()
 	 * 
 	 * @return void
 	 */
 	function __construct()
  	{
 		parent::__construct();
  		$this->projectid	= JRequest::getInt( 'p', 0 );
 		$this->personid		= JRequest::getInt( 'pid', 0 );
 		$this->teamplayerid	= JRequest::getInt( 'pt', 0 );
 	}



	/**
	 * sportsmanagementModelPerson::getPerson()
	 * 
	 * @param integer $personid
	 * @return
	 */
	function getPerson($personid=0)
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $this->personid	= JRequest::getInt( 'pid', 0 );
        $starttime = microtime(); 
        
        //$mainframe->enqueueMessage(JText::_('getPerson personid<br><pre>'.print_r($this->personid,true).'</pre>'),'');
        
       // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        
        //if ( is_null( $this->person ) )
		//{
		// Select some fields
		$query->select('p.*');
        $query->select('CONCAT_WS( \':\', p.id, p.alias ) AS slug ');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p ');
        $query->where('p.id = '.$db->Quote($this->personid));
        

        
		$db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
                
		$this->person = $db->loadObject();
		//}
		return $this->person;
	}


	/**
	 * sportsmanagementModelPerson::getReferee()
	 * 
	 * @return
	 */
	function &getReferee()
	{
	   // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        
		//if ( is_null( $this->_inproject ) )
		//{
		$query->select('p.*,CONCAT_WS(\':\',p.id,p.alias) AS slug');
        $query->select('pr.id,pr.notes AS prnotes,pr.picture');
        $query->select('pos.name AS position_name');
        
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS pr ');
        
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS o ON o.id = pr.person_id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p ON p.id = o.person_id');
        
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id = pr.project_position_id');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id = ppos.position_id');
        
        $query->where('pr.project_id = '.$db->Quote($this->projectid));
        $query->where('p.published = 1 ');
        $query->where('o.person_id = '.$db->Quote($this->personid));
          
//        $query='	SELECT	p.*,
//								CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\',p.id,p.alias) ELSE p.id END AS slug,
//								pr.id,
//								pr.notes AS prnotes,
//								pos.name AS position_name,
//								pr.picture
//						FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS pr
//						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p ON p.id=pr.person_id
//						LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id=pr.project_position_id
//						LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id=ppos.position_id						
//						WHERE pr.project_id='.$this->_db->Quote($this->projectid).' 
//						  AND p.published = 1 
//						  AND pr.person_id='.$this->_db->Quote($this->personid);
                            
//			$query = ' SELECT tp.*, pos.name AS position_name '
//					. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS tp '
//							. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id = tp.project_position_id '
//									. ' WHERE tp.project_id = '. $this->_db->Quote($this->projectid)
//									. '   AND tp.person_id = '. $this->_db->Quote($this->personid)
//									;
//									$this->_db->setQuery($query);
//									$this->_inproject = $this->_db->loadObject();
		
        
        
        //}
		return $this->_inproject;
	}

//	/**
//	 * sportsmanagementModelPerson::getPositionEventTypes()
//	 * 
//	 * @param integer $positionId
//	 * @return
//	 */
//	function getPositionEventTypes( $positionId = 0 )
//	{
//	   // Create a new query object.		
//		$db = JFactory::getDBO();
//		$query = $db->getQuery(true);
//        
//		$result = array();
//
//		$query = '	SELECT	pet.*,
//				et.name,
//				et.icon
//
//				FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_position_eventtype AS pet
//				INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype AS et ON et.id = pet.eventtype_id
//				INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event AS me ON et.id = me.event_type_id
//				WHERE me.project_id=' . $this->projectid;
//
//		if ( $positionId > 0 )
//		{
//			$query .= ' AND pet.position_id = ' . (int)$positionId;
//		}
//		$query .= ' ORDER BY pet.ordering';
//
//		$this->_db->setQuery( $query );
//		$result = $this->_db->loadObjectList();
//
//		if ( $result )
//		{
//			if ( $positionId )
//			{
//				return $result;
//			}
//			else
//			{
//				$posEvents = array();
//				foreach ( $result as $r )
//				{
//					$posEvents[$r->position_id][] = $r;
//				}
//				return ( $posEvents );
//			}
//		}
//		return array();
//	}


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

	/**
	 * sportsmanagementModelPerson::getContactID()
	 * 
	 * @param mixed $catid
	 * @return
	 */
	function getContactID( $catid )
	{
		$mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
        
        $person = self::getPerson();

        $query->select('id');
        $query->from('#__contact_details');
        $query->where('user_id = ' . $person->jl_user_id);
        $query->where('catid = ' . $catid);
        
		$db->setQuery( $query );
		$contact_id = $db->loadResult();

		return $contact_id;
	}

//	function getRounds( $roundcodestart, $roundcodeend )
//	{
//		$projectid = $this->projectid;
//
//		$thisround = 0;
//		$query = "	SELECT	id
//				FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_round
//				WHERE project_id='" . (int)$projectid . "'
//				AND roundcode>='" . (int)$roundcodestart . "'
//				AND roundcode<='" . (int)$roundcodeend . "'
//				ORDER BY round_date_first";
//
//		$this->_db->setQuery( $query );
//		$rows = $this->_db->loadResultArray();
//
//		$rounds = array();
//		if ( count( $rows ) > 0 )
//		{
//			$startround =& $this->getTable( 'Round', 'Table' );
//			$startround->load( $rows[0] );
//			$rounds[0] = $startround;
//			$endround =& $this->getTable( 'Round', 'Table' );
//			$endround->load( end( $rows ) );
//			$rounds[1] = $endround;
//		}
//		return $rounds;
//	}

	/**
	 * get all positions the player was assigned too in different projects
	 * @return unknown_type
	 */
	function getAllEvents()
	{
	   $mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
		$history = sportsmanagementModelPlayer::getPlayerHistory();
		$positionhistory = array();
		foreach($history as $h)
		{
			if (!in_array($h->position_id, $positionhistory))
				$positionhistory[] = $h->position_id;
		}
		if (!count($positionhistory)) 
        {
			return array();
		}
        
        $query->select('et.*');
       $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype AS et'); 
       $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position_eventtype AS pet ON pet.eventtype_id = et.id');
       $query->where('published = 1');
       $query->where('pet.position_id IN ('. implode(',', $positionhistory) .')');
       $query->order('et.ordering');
                
				$db->setQuery( $query );
                
                if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
                {
                $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
                }
                
				$info = $db->loadObjectList();
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
	   $mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
       $query->select('SUM(me.event_sum) as total');
       $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event AS me'); 
       $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp1 ON tp1.id = me.teamplayer_id');
       $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st1 ON st1.team_id = tp1.team_id'); 
       $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON st1.id = pt.team_id');

        $query->where('me.event_type_id =' . $db->Quote((int) $eventid));
        $query->where('tp1.person_id = ' . $db->Quote((int) $this->personid));
                        
				if ($projectteamid)
				{
					//$query .= ' AND pt.id='.$this->_db->Quote((int) $projectteamid);
                    $query->where('pt.id ='.$db->Quote((int) $projectteamid));
				}
				if ($projectid)
				{
					//$query .= ' AND pt.project_id=' . $this->_db->Quote((int) $projectid);
                    $query->where('pt.project_id =' . $db->Quote((int) $projectid));
				}
				//$query .= ' GROUP BY tp.person_id';
                $query->group('tp1.person_id');

				$db->setQuery($query);
				$result = $db->loadResult();
                
                if ( !$result && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
        elseif ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }
        
				return $result;
	}



	/**
	 * sportsmanagementModelPerson::getPlayerChangedRecipients()
	 * 
	 * @return
	 */
	function getPlayerChangedRecipients()
	{
	    $mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
       $query->select('email');
       $query->from('#__users'); 
       $query->where('usertype = \'Super Administrator\' OR usertype = \'Administrator\' ');

		$db->setQuery( $query );
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
        }

		return $db->loadResultArray();
	}

	/**
	 * sportsmanagementModelPerson::sendMailTo()
	 * 
	 * @param mixed $listOfRecipients
	 * @param mixed $subject
	 * @param mixed $message
	 * @return void
	 */
	function sendMailTo($listOfRecipients, $subject, $message)
	{
		$mainframe	= JFactory::getApplication();
		$mailFrom = $mainframe->getCfg('mailfrom');
		$fromName = $mainframe->getCfg('fromname');
		JUtility::sendMail( $mailFrom, $fromName, $listOfRecipients, $subject, $message );
	}

	/**
	 * sportsmanagementModelPerson::getAllowed()
	 * 
	 * @param mixed $config_editOwnPlayer
	 * @return
	 */
	function getAllowed($config_editOwnPlayer)
	{
		$user = JFactory::getUser();
		$allowed = false;
		if(self::_isAdmin($user) || self::_isOwnPlayer($user,$config_editOwnPlayer)) {
			$allowed=true;
		}
		return $allowed;
	}

	/**
	 * sportsmanagementModelPerson::_isAdmin()
	 * 
	 * @param mixed $user
	 * @return
	 */
	function _isAdmin($user)
	{
	   // Get a refrence of the page instance in joomla
		$document = JFactory::getDocument();
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
		$allowed = false;
		if ($user->id > 0)
		{
			$project= sportsmanagementModelProject::getProject();

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

	/**
	 * sportsmanagementModelPerson::_isOwnPlayer()
	 * 
	 * @param mixed $user
	 * @param mixed $config_editOwnPlayer
	 * @return
	 */
	function _isOwnPlayer($user,$config_editOwnPlayer)
	{
		if($user->id > 0)
		{
			$person = self::getPerson();
			return $config_editOwnPlayer && $user->id == $person->user_id;
		}
		return false;
	}

	/**
	 * sportsmanagementModelPerson::isEditAllowed()
	 * 
	 * @param mixed $config_editOwnPlayer
	 * @param mixed $config_editAllowed
	 * @return
	 */
	function isEditAllowed($config_editOwnPlayer,$config_editAllowed)
	{
		$allowed = false;
		$user = JFactory::getUser();
		if($user->id > 0)
		{
			if(self::_isAdmin($user) || ($config_editAllowed && self::_isOwnPlayer($user,$config_editOwnPlayer))) {
				$allowed = true;
			}
			return $allowed;
		}
		return false;
	}

	/**
	 * sportsmanagementModelPerson::_getProjectTeamIds4UserId()
	 * 
	 * @param mixed $userId
	 * @return
	 */
	function _getProjectTeamIds4UserId($userId)
	{
	   $mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
       // team_player
       $query->select('tp.projectteam_id');
       $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pr'); 
       $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ON tp.person_id = pr.id');
       $query->where('pr.user_id ='.$userId);
       $query->where('pr.published = 1');
       $query->where('tp.persontype = 1');
                        
		$db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
        }
        
		$projectTeamIds = array();
		$projectTeamIds = $db->loadResultArray();
        
        
		// team_staff
        $query->select('tp.projectteam_id');
       $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pr'); 
       $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ON tp.person_id = pr.id');
       $query->where('pr.user_id ='.$userId);
       $query->where('pr.published = 1');
       $query->where('tp.persontype = 2');
                        
		$db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
        }
        
		$projectTeamIds = array_merge($projectTeamIds,$db->loadResultArray());
		return $projectTeamIds;
	}

	/**
	 * sportsmanagementModelPerson::isContactDataVisible()
	 * 
	 * @param mixed $config_showContactDataOnlyTeamMembers
	 * @return
	 */
	function isContactDataVisible($config_showContactDataOnlyTeamMembers)
	{
		$user = JFactory::getUser();
		$result = true;
		// project admin and editor,see contact always
		if($config_showContactDataOnlyTeamMembers && !sportsmanagementModelProject::isUserProjectAdminOrEditor($user->id,sportsmanagementModelProject::getProject()))
		{
			$result=false;
			if($user->id > 0)
			{
				// get project_team id to user-id from team-player or team-staff
				$projectTeamIds = self::_getProjectTeamIds4UserId($user->id);
				$teamplayer = sportsmanagementModelPlayer::getTeamPlayer();
				if(isset($teamplayer->projectteam_id)) 
                {
					$result = in_array($teamplayer->projectteam_id, $projectTeamIds);
				}
			}
		}
		return $result;
	}

}
?>