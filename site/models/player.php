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

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
//require_once('roster.php');

/**
 * sportsmanagementModelPlayer
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelPlayer extends JModelLegacy
{
	
    static $projectid		= 0;
	static $personid		  = 0;
	static $teamplayerid	= 0;
    
    /**
	 * data array for player history
	 * @var array
	 */
	static $_playerhistory =null;
    static $_playerhistorystaff =null;
	static $_teamplayers = null;
    static $_inproject = NULL;
    
    static $cfg_which_database = 0;


	/**
	 * sportsmanagementModelPlayer::__construct()
	 * 
	 * @return
	 */
	function __construct()
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
		parent::__construct();
		self::$projectid = $jinput->getInt('p',0);
		self::$personid = $jinput->getInt('pid',0);
		self::$teamplayerid = $jinput->getInt('pt',0);
        self::$cfg_which_database = $jinput->getInt('cfg_which_database',0);
	}


	
	/**
	 * sportsmanagementModelPlayer::getTeamPlayers()
	 * 
	 * @return
	 */
	function getTeamPlayers($cfg_which_database = 0)
	{
	   $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection(TRUE, $cfg_which_database );
		$query = $db->getQuery(true);
        
        // Select some fields
        $query->select('tp.*');
        $query->select('pt.project_id,pt.team_id,pt.id as projectteam_id');
        $query->select('pos.name AS position_name');
        $query->select('ppos.position_id');
        $query->select('rinjuryfrom.round_date_first as injury_date,rinjuryfrom.name as rinjury_from');
        $query->select('rinjuryto.round_date_last as injury_end,rinjuryto.name as rinjury_to');
        $query->select('rsuspfrom.round_date_first as suspension_date,rsuspfrom.name as rsusp_from');
        $query->select('rsuspto.round_date_last as suspension_end,rsuspto.name as rsusp_to');
        $query->select('rawayfrom.round_date_first as away_date,rawayfrom.name as raway_from');
        $query->select('rawayto.round_date_last as away_end,rawayto.name as raway_to');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pe ON pe.id = tp.person_id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = tp.team_id'); 
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id = tp.project_position_id');
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = pt.project_id');
		$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id = ppos.position_id');
		$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS rinjuryfrom ON pe.injury_date = rinjuryfrom.id');
		$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS rinjuryto ON pe.injury_end = rinjuryto.id');
		$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS rsuspfrom ON pe.suspension_date = rsuspfrom.id');
		$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS rsuspto ON pe.suspension_end = rsuspto.id');
		$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS rawayfrom ON pe.away_date = rawayfrom.id');
		$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS rawayto ON pe.away_end = rawayto.id');        
        $query->where('pt.project_id = '.self::$projectid );
        $query->where('tp.person_id = '.self::$personid );
        $query->where('p.published = 1');
        $db->setQuery($query);
		self::$_teamplayers = $db->loadObjectList('projectteam_id');
        
        if ( !self::$_teamplayers && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
        elseif ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }

		return self::$_teamplayers;
	}

	 
	/**
	 * sportsmanagementModelPlayer::getTeamPlayer()
	 * 
	 * @return
	 */
	static function getTeamPlayer($projectid=0,$personid=0,$teamplayerid=0)
	{
	   $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
	   $query = $db->getQuery(true);
       
       if ( $projectid )
       {
        self::$projectid = $projectid;
       }
       if ( $personid )
       {
        self::$personid = $personid;
       }
       if ( $teamplayerid )
       {
        self::$teamplayerid = $teamplayerid;
       }
       
       // Select some fields
        $query->select('tp.*');
        $query->select('pt.project_id,pt.team_id,pt.notes AS ptnotes');
        $query->select('pos.name AS position_name');
        $query->select('ppos.position_id');
        
        $query->select('ps.firstname, ps.lastname');
        
       $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ');
       $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = tp.team_id');
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id = tp.project_position_id');
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = pt.project_id');
		$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id = ppos.position_id');
        
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS ps ON ps.id = tp.person_id');
              
       $query->where('pt.project_id = '.self::$projectid);
       if ( self::$personid )
       {
        $query->where('tp.person_id = '.self::$personid);
        }
        
        if ( self::$teamplayerid )
       {
        $query->where('tp.id = '.self::$teamplayerid);
        }
        
        $query->where('p.published = 1');
       $query->where('tp.persontype = 1');
       
 		$db->setQuery($query);
 		self::$_inproject = $db->loadObjectList();
        if ( !self::$_inproject && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
        elseif ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }

		return self::$_inproject;
	}

	/**
	 * sportsmanagementModelPlayer::getTeamStaff()
	 * 
	 * @return
	 */
	function getTeamStaff()
	{
	   $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
	   $query = $db->getQuery(true);
       
        // Select some fields
        $query->select('tp.*');
        $query->select('pt.project_id,pt.team_id,pt.notes AS ptnotes');
        $query->select('pos.name AS position_name');
        $query->select('ppos.position_id');
       $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ');
       $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = tp.team_id');
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id = tp.project_position_id');
		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = pt.project_id');
		$query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id = ppos.position_id');      
       $query->where('pt.project_id='.$db->Quote($this->projectid));
        $query->where('tp.person_id='.$db->Quote($this->personid));
        $query->where('p.published = 1');
        $query->where('tp.persontype = 2');
       
       $db->setQuery($query);
 	self::$_inproject = $db->loadObject();
    
    if ( !self::$_inproject && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
        elseif ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }
		

		return self::$_inproject;
	}

//	/**
//	 * sportsmanagementModelPlayer::getPositionEventTypes()
//	 * 
//	 * @param integer $positionId
//	 * @return
//	 */
//	function getPositionEventTypes($positionId=0)
//	{
//	   $app = JFactory::getApplication();
//        $option = JRequest::getCmd('option');
//        // Create a new query object.		
//	   $db = JFactory::getDBO();
//	   $query = $db->getQuery(true);
//       
//		$result=array();
//        $query->select('pet.*');
//        $query->select('pj.id AS ppID');
//        $query->select('et.name,et.icon');
//        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_position_eventtype AS pet ');
//        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype AS et ON et.id=pet.eventtype_id');
//		$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event AS me ON et.id=me.event_type_id');
//        $query->where('me.project_id = '.$this->projectid);
//  
//		if ($positionId > 0)
//		{
//		  $query->where('pet.position_id = '.(int)$positionId);
//		}
//        $query->order('pet.ordering');
//		$db->setQuery($query);
//		$result = $db->loadObjectList();
//		if ($result)
//		{
//			if ($positionId) {
//				return $result;
//			} else {
//				$posEvents=array();
//				foreach ($result as $r)
//				{
//					//$posEvents[$r->position_id][]=$r;
//					$posEvents[$r->ppID][]=$r;
//				}
//				return ($posEvents);
//			}
//		}
//        
//        if ( !$result && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
//        {
//            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
//        }
//        elseif ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
//        {
//            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
//        }
//        
//		return array();
//	}

	
	/**
	 * sportsmanagementModelPlayer::getPlayerHistory()
	 * 
	 * @param integer $sportstype
	 * @param string $order
	 * @return
	 */
	function getPlayerHistory($sportstype = 0, $order = 'ASC', $persontype = 1, $cfg_which_database = 0)
	{
	   $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = sportsmanagementHelper::getDBConnection(TRUE, $cfg_which_database );
	   $query = $db->getQuery(true);
    
    $query->select('pr.id AS pid,pr.firstname,pr.lastname');
    $query->select('tp.person_id,tp.id AS tpid,tp.project_position_id,tp.market_value');
    $query->select('p.name AS project_name,CONCAT_WS(\':\',p.id,p.alias) AS project_slug');
    $query->select('s.name AS season_name,s.id AS season_id');
    $query->select('t.name AS team_name,t.id AS team_id,CONCAT_WS(\':\',t.id,t.alias) AS team_slug');
    $query->select('pos.name AS position_name,pos.id AS posID');
    $query->select('pt.id AS ptid,pt.project_id');
    $query->select('ppos.position_id');
	$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pr'); 
	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ON tp.person_id = pr.id'); 
    $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = tp.team_id AND st.season_id = tp.season_id');
	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id'); 
	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id = st.team_id'); 
	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = pt.project_id'); 
	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season AS s ON s.id = p.season_id'); 
	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_league AS l ON l.id = p.league_id'); 
	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id = tp.project_position_id'); 
	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id = ppos.position_id');        
    
        $query->where('pr.id = '.self::$personid);
        $query->where('p.published = 1');
        $query->where('pr.published = 1');
        $query->where('tp.persontype = '.$persontype); 
        if ($sportstype > 0)
			{
				$query->where('p.sports_type_id = '.$sportstype);
			}
     
             
     $query->order('s.ordering ' . $order .',l.ordering ASC,p.name ASC ');
     
     $db->setQuery($query);
 	$result = $db->loadObjectList();
    
    if ( !$result && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        } 
        elseif ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
            {
                $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            } 
       

        switch ($persontype)
        {
            case 1:
            self::$_playerhistory = $result;
            return self::$_playerhistory;
            break;
            case 2:
            self::$_playerhistorystaff = $result;
            return self::$_playerhistorystaff;
            break;
        }
		//return self::$_playerhistory;
	}

	
//	/**
//	 * sportsmanagementModelPlayer::getPlayerHistoryStaff()
//	 * 
//	 * @param integer $sportstype
//	 * @param string $order
//	 * @return
//	 */
//	function getPlayerHistoryStaff($sportstype=0, $order='ASC')
//	{
//	   $app = JFactory::getApplication();
//        $option = JRequest::getCmd('option');
//        // Create a new query object.		
//	   $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
//	   $query = $db->getQuery(true);
//       
//    $query->select('pr.id AS pid,pr.firstname,pr.lastname');
//    $query->select('tp.person_id,tp.id AS tpid,tp.project_position_id,tp.market_value');
//    $query->select('p.name AS project_name,CONCAT_WS(\':\',p.id,p.alias) AS project_slug');
//    $query->select('s.name AS season_name');
//    $query->select('t.name AS team_name,t.id AS team_id,CONCAT_WS(\':\',t.id,t.alias) AS team_slug');
//    $query->select('pos.name AS position_name,pos.id AS posID');
//    $query->select('pt.id AS ptid,pt.project_id');
//    $query->select('ppos.position_id');
//	$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pr'); 
//	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ON tp.person_id = pr.id'); 
//    $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = tp.team_id');
//	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id'); 
//	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id = pt.team_id'); 
//	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = pt.project_id'); 
//	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season AS s ON s.id = p.season_id'); 
//	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_league AS l ON l.id = p.league_id'); 
//	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id = tp.project_position_id'); 
//	$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id = ppos.position_id');        
//    
//        $query->where('pr.id = '.self::$personid );
//        $query->where('p.published = 1');
//        $query->where('pr.published = 1');
//        $query->where('tp.persontype = 2'); 
//        if ($sportstype > 0)
//			{
//				$query->where('p.sports_type_id = '.$sportstype );
//			}
//     $query->order('s.ordering ' . $order .',l.ordering ASC,p.name ASC ');
//     
//     $db->setQuery($query);
// 	self::$_playerhistorystaff = $db->loadObjectList();
//    
//    if ( !self::$_playerhistorystaff && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
//        {
//            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
//        }
//        elseif ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
//        {
//            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
//        }  
//
//
//        
//		return self::$_playerhistorystaff;
//	}

//	/**
//	 * sportsmanagementModelPlayer::getContactID()
//	 * 
//	 * @param mixed $catid
//	 * @return
//	 */
//	function getContactID($catid)
//	{
//	   $app = JFactory::getApplication();
//        $option = JRequest::getCmd('option');
//        // Create a new query object.		
//	   $db = JFactory::getDBO();
//	   $query = $db->getQuery(true);
//              
//		$person = sportsmanagementModelPerson::getPerson();
//        
//        $query->select('id');
//        $query->from('#__contact_details'); 
//        $query->where('user_id = '.$person->jl_user_id);
//        $query->where('catid = '.$catid);
////		$query='	SELECT	id
////					FROM #__contact_details
////					WHERE user_id='.$person->jl_user_id.'
////					AND catid='.$catid;
//                    
//		$db->setQuery($query);
//        
//        
//		$contact_id = $db->loadResult();
//		return $contact_id;
//	}

//	/**
//	 * sportsmanagementModelPlayer::getRounds()
//	 * 
//	 * @param mixed $roundcodestart
//	 * @param mixed $roundcodeend
//	 * @return
//	 */
//	function getRounds($roundcodestart,$roundcodeend)
//	{
//	   $app = JFactory::getApplication();
//        $option = JRequest::getCmd('option');
//        // Create a new query object.		
//	   $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
//	   $query = $db->getQuery(true);
//       
//		$projectid = self::$projectid;
//		$thisround = 0;
//        
//        $query->select('id');
//        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round'); 
//        $query->where('project_id ='.(int)$projectid);
//        $query->where('roundcode >='.(int)$roundcodestart);
//        $query->where('roundcode <='.(int)$roundcodeend);
//        $query->order('round_date_first');
//                    
//		$db->setQuery($query);
//		$rows = $db->loadResultArray();
//		$rounds = array();
//		if (count($rows) > 0)
//		{
//			$startround =& $this->getTable('Round','sportsmanagementTable');
//			$startround->load($rows[0]);
//			$rounds[0] = $startround;
//			$endround =& $this->getTable('Round','sportsmanagementTable');
//			$endround->load(end($rows));
//			$rounds[1] = $endround;
//		}
//		return $rounds;
//	}

	
	/**
	 * sportsmanagementModelPlayer::getAllEvents()
	 * 
	 * @param integer $sportstype
	 * @return
	 */
	function getAllEvents($sportstype=0)
	{
	   $app = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
	   $query = $db->getQuery(true);
       
		$history = self::getPlayerHistory($sportstype);
		$positionhistory = array();
		foreach($history as $h)
		{
			if (!in_array($h->posID,$positionhistory) && $h->posID!=null)
			{
				$positionhistory[] = $h->posID;
			}
		}
		if (!count($positionhistory))
		{
			return array();
		}
        
        $query->select('DISTINCT et.*');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype AS et');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position_eventtype AS pet ON pet.eventtype_id = et.id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.position_id = pet.position_id');
        $query->where('pet.position_id IN ('. implode(',',$positionhistory) .')');
        $query->where('et.published = 1');
        $query->order('pet.ordering ');
                    
		$db->setQuery($query);
		$info = $db->loadObjectList();
		return $info;
	}
    
    /**
     * sportsmanagementModelPLayer::getTimePlayed()
     * 
     * @param mixed $player_id
     * @param mixed $game_regular_time
     * @param mixed $match_id
     * @param mixed $cards
     * @return
     */
    public static function getTimePlayed( $player_id, $game_regular_time, $match_id = NULL ,$cards = NULL )
    {
        $app = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
	   $query = $db->getQuery(true);
       
       
       
    if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
       {
       $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' player_id<br><pre>'.print_r($player_id,true).'</pre>'),'');
       $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' game_regular_time<br><pre>'.print_r($game_regular_time,true).'</pre>'),'');
       $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' cards<br><pre>'.print_r($cards,true).'</pre>'),'');
       }   
       
    
    $result = 0;
    // startaufstellung ohne ein und auswechselung
    $query->select('COUNT(distinct match_id) as totalmatch');
	$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player'); 
    $query->where('teamplayer_id = '.$player_id);
    $query->where('came_in = 0');

    if ( $match_id )
    {
    $query->where('match_id = '.$match_id);
    }   
           
            
    $db->setQuery($query);
    $totalresult = $db->loadObject();
    if ( $totalresult )
    {
    $result += $totalresult->totalmatch * $game_regular_time;
    }
    
    if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' totalmatch<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' totalresult<br><pre>'.print_r($totalresult,true).'</pre>'),'');
        }
        
    // einwechselung
    $query = $db->getQuery(true);    
    $query->clear();
    $query->select('count(distinct match_id) as totalmatch, SUM(in_out_time) as totalin');
	$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player'); 
    $query->where('teamplayer_id = '.$player_id);
    $query->where('came_in = 1');
    $query->where('in_for IS NOT NULL');
       
    if ( $match_id )
    {
    $query->where('match_id = '.$match_id);
    } 
    $db->setQuery($query);
    $cameinresult = $db->loadObject();
    
    if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' totalin<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' cameinresult<br><pre>'.print_r($cameinresult,true).'</pre>'),'');
        }
    
    if ( $cameinresult )
    {
    $result += ( $cameinresult->totalmatch * $game_regular_time ) - ( $cameinresult->totalin );
    }
    
    // auswechselung
    $query = $db->getQuery(true);    
    $query->clear();
    $query->select('count(distinct match_id) as totalmatch, SUM(in_out_time) as totalout');
	$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player'); 
    $query->where('in_for = '.$player_id);
    $query->where('came_in = 1');
 
    if ( $match_id )
    {
    $query->where('match_id = '.$match_id);
    } 
    $db->setQuery($query);
    $cameautresult = $db->loadObject();
    
    if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' totalout<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' cameautresult<br><pre>'.print_r($cameautresult,true).'</pre>'),'');
        }
    
    if ( $cameautresult )
    {
    // bug erkannt durch @player2000    
    //$result += ( $cameautresult->totalout );
    $result += ( $cameautresult->totalout ) - ( $cameautresult->totalmatch * $game_regular_time );
    }
    
    // jetzt muss man noch die karten berücksichtigen, die zu einer hinausstellung führen
    if ( $cards )
    {
        $query = $db->getQuery(true);    
    $query->clear();
    $query->select('*');
	$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event '); 
    $query->where('teamplayer_id = '.$player_id);
    $query->where('event_type_id IN ('.implode(',', $cards).')');

    if ( $match_id )
    {
        $query->where('match_id = '.$match_id);
    
    }
    $db->setQuery($query);
    $cardsresult = $db->loadObjectList(); 
    foreach ( $cardsresult as $row )
    {
        $result -= ( $game_regular_time - $row->event_time );
    }   
    
    if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' match_event<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' cardsresult<br><pre>'.print_r($cardsresult,true).'</pre>'),'');
        }
        
    }
    
    //$app->enqueueMessage(JText::_('result -> '.'<pre>'.print_r($result,true).'</pre>' ),'');
    
    return $result;    
    }

	/**
	 * sportsmanagementModelPlayer::getInOutStats()
	 * 
	 * @param mixed $project_id
	 * @param mixed $projectteam_id
	 * @param mixed $teamplayer_id
	 * @return
	 */
	public static function getInOutStats($project_id = 0, $projectteam_id = 0, $teamplayer_id = 0, $game_regular_time = 90, $match_id = 0,$cfg_which_database = 0)
	{
	   $app = JFactory::getApplication();
    $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = sportsmanagementHelper::getDBConnection(TRUE, $cfg_which_database );
	   $query = $db->getQuery(true);
		
       if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
       {
       $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' project_id<br><pre>'.print_r($project_id,true).'</pre>'),'');
       $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' projectteam_id<br><pre>'.print_r($projectteam_id,true).'</pre>'),'');
       $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' teamplayer_id<br><pre>'.print_r($teamplayer_id,true).'</pre>'),'');
       }
       
       
       
       $query->select('m.id AS mid, mp.came_in, mp.out, mp.teamplayer_id, mp.in_for, mp.in_out_time');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m'); 
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp ON mp.match_id = m.id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON m.projectteam1_id = pt1.id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st1 ON st1.id = pt1.team_id'); 
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON m.projectteam2_id = pt2.id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st2 ON st2.id = pt2.team_id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = pt1.project_id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp1 ON ( tp1.id = mp.teamplayer_id OR tp1.id = mp.in_for)');
        
        if ( $match_id )
        {
            $query->where('m.id='.$db->Quote((int)$match_id));
        }
        
        $query->where('tp1.id='.$db->Quote((int)$teamplayer_id));
        $query->where('( pt1.project_id='.$db->Quote((int)$project_id).' OR pt2.project_id='.$db->Quote((int)$project_id).' )');
        $query->where('( pt1.id = '.$db->Quote((int)$projectteam_id).' OR pt2.id = '.$db->Quote((int)$projectteam_id).' )');
        $query->where('m.published = 1');
        $query->where('p.published = 1');
       
       $db->setQuery($query);
			$rows = $db->loadObjectList();
       
       if ( !$rows && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' rows<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        } 
        elseif ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }
        
       $inoutstat = new stdclass;
		      $inoutstat->played = 0;
		      $inoutstat->started = 0;
		      $inoutstat->sub_in = 0;
		      $inoutstat->sub_out = 0;
		      foreach ($rows AS $row)
		      {
			     $inoutstat->played += ($row->came_in == 0); 
                 $inoutstat->played += ($row->came_in == 1) && ($row->teamplayer_id == $teamplayer_id);
                 $inoutstat->started += ($row->came_in == 0);
			     $inoutstat->sub_in  += ($row->came_in == 1) && ($row->teamplayer_id == $teamplayer_id);
			     $inoutstat->sub_out += ($row->out == 1) || ($row->in_for == $teamplayer_id);
		      }
       
       if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
       {
       $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' inoutstat<br><pre>'.print_r($inoutstat,true).'</pre>'),'');
       }
       
       
       
		return $inoutstat;
	}

	
	/**
	 * sportsmanagementModelPlayer::getStats()
	 * 
	 * @return
	 */
	function getStats()
	{
	   $app = JFactory::getApplication();
    $option = JRequest::getCmd('option');
    
		$stats = array();
		$players = self::getTeamPlayer();
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' players<br><pre>'.print_r($players,true).'</pre>'),'');
        
		if (is_array($players))
		{
			foreach ($players as $player)
			{
				// Remark: we cannot use array_merge because numerical keys will result in duplicate entries
				// so we check if a key already exists in the output array before adding it.
				$projectStats = sportsmanagementModelProject::getProjectStats(0,$player->position_id);
                
                //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projectStats<br><pre>'.print_r($projectStats,true).'</pre>'),'');
                
				if (is_array($projectStats))
				{
					foreach ($projectStats as $key=>$projectStat)
					{
						if (!array_key_exists($key, $stats))
						{
							$stats[$key] = $projectStat;
						}
					}
				}
			}
		}
		return $stats;
	}

	
	/**
	 * sportsmanagementModelPlayer::getCareerStats()
	 * 
	 * @param mixed $person_id
	 * @param mixed $sports_type_id
	 * @return
	 */
	function getCareerStats($person_id, $sports_type_id)
	{
	   $app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$query_core = JFactory::getDbo()->getQuery(true);

		if (empty($this->_careerStats))
		{
		  $query_core->select('s.id,s.name,s.short,s.class,s.icon,s.calculated,ppos.id AS pposid,ppos.position_id AS position_id,s.params,s.baseparams');
          $query_core->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p');
          $query_core->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ON p.id = tp.person_id ');
          $query_core->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON tp.project_position_id = ppos.id ');
          $query_core->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON ppos.position_id = pos.id ');
          $query_core->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position_statistic AS ps ON ps.position_id = pos.id ');
          $query_core->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_statistic AS s ON ps.statistic_id = s.id ');
          $query_core->where('p.id = '.$person_id);
          
//			$query = 'SELECT s.id,'
//				. ' s.name,'
//				. ' s.short,'
//				. ' s.class,'
//				. ' s.icon,'
//				. ' s.calculated,'
//				. ' ppos.id AS pposid,'
//				. ' ppos.position_id AS position_id,'
//				. ' s.params,'
//				. ' s.baseparams'
//				. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p'
//				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tp ON tp.person_id = p.id'
//				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON tp.project_position_id = ppos.id'
//				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON ppos.position_id = pos.id'
//				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position_statistic AS ps ON ps.position_id = pos.id'
//				. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_statistic AS s ON ps.statistic_id = s.id'
//				. ' WHERE p.id='.$this->_db->Quote($person_id);
			if ($sports_type_id > 0)
			{
				//$query.= ' AND pos.sports_type_id='.$this->_db->Quote($sports_type_id);
                $query_core->where('pos.sports_type_id = '.$sports_type_id);
			}
			//$query.= ' GROUP BY s.id';
            $query_core->group('s.id');

			$db->setQuery($query_core);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query_core<br><pre>'.print_r($query_core->dump(),true).'</pre>'),'');
 }
            
			$this->_careerStats = $db->loadObjectList();
		}
        
		$stats=array();
		if (count($this->_careerStats) > 0)
		{
			foreach ($this->_careerStats as $k => $row)
			{
				$stat = SMStatistic::getInstance($row->class);
				$stat->bind($row);
				$stat->set('position_id',$row->position_id);
				$stats[$row->id] = $stat;
			}
		}
		return $stats;
	}

	
	/**
	 * sportsmanagementModelPlayer::getPlayerStatsByGame()
	 * 
	 * @return
	 */
	function getPlayerStatsByGame()
	{
		$teamplayers = self::getTeamPlayers();
		$displaystats=array();
		if (count($teamplayers))
		{
			$project = sportsmanagementModelProject::getProject();
			$project_id = $project->id;
			// Determine teamplayer id(s) of the player (plural if (s)he played in multiple teams of the project
			// and the position_id(s) where the player played
			$teamplayer_ids = array();
			$position_ids = array();
			foreach ($teamplayers as $teamplayer)
			{
				$teamplayer_ids[] = $teamplayer->id;
				if (!in_array($teamplayer->position_id, $position_ids))
				{
					$position_ids[] = $teamplayer->position_id;
				}
			}
			// For each position_id get the statistics types and merge the results (prevent duplicate statistics ids)
			// ($pos_stats is an array indexed by statistic_id)
			$pos_stats = array();
			foreach ($position_ids as $position_id)
			{
				$stats_for_position_id = sportsmanagementModelProject::getProjectStats(0, $position_id);
				foreach ($stats_for_position_id as $id => $stat)
				{
					if (!array_key_exists($id, $pos_stats))
					{
						$pos_stats[$id] = $stat;
					}
				}
			}
			foreach ($pos_stats as $stat)
			{
				if(!empty($stat))
				{
					if ($stat->showInSingleMatchReports())
					{
						$stat->set('gamesstats',$stat->getPlayerStatsByGame($teamplayer_ids, $project_id));
						$displaystats[]=$stat;
					}
				}
			}
		}
		return $displaystats;
	}

	
	/**
	 * sportsmanagementModelPlayer::getPlayerStatsByProject()
	 * 
	 * @param integer $sportstype
	 * @return
	 */
	function getPlayerStatsByProject($sportstype=0)
	{
	   $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        
		$teamplayer = self::getTeamPlayer();
		$result = array();
		if (is_array($teamplayer) && !empty($teamplayer))
		{
			// getTeamPlayer can return multiple teamplayers, because a player can be transferred from 
			// one team to another inside a season, but they are all the same person so have same person_id.
			// So we get the player_id from the first array entry.
			$stats  = self::getCareerStats($teamplayer[0]->person_id, $sportstype);
			$history = self::getPlayerHistory($sportstype);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' teamplayer<br><pre>'.print_r($teamplayer,true).'</pre>'),'');
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' stats<br><pre>'.print_r($stats,true).'</pre>'),'');
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' history<br><pre>'.print_r($history,true).'</pre>'),'');
 }
            
			if(count($history)>0)
			{
				foreach ($stats as $stat)
				{
					if(!empty($stat))
					{
						foreach ($history as $player)
						{

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{							
       $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' person_id<br><pre>'.print_r($player->person_id,true).'</pre>'),'');
       $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projectteam_id<br><pre>'.print_r($player->ptid,true).'</pre>'),'');
       $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project_id<br><pre>'.print_r($player->project_id,true).'</pre>'),'');
       $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' sportstype<br><pre>'.print_r($sportstype,true).'</pre>'),'');
 }
                            
                            $result[$stat->id][$player->project_id][$player->ptid] = $stat->getPlayerStatsByProject($player->person_id, $player->ptid, $player->project_id, $sportstype);
       
       
						}
						$result[$stat->id]['totals'] = $stat->getPlayerStatsByProject($player->person_id, 0, 0, $sportstype);
					}
				}
			}
		}
		return $result;
	}

	/**
	 * sportsmanagementModelPlayer::getGames()
	 * 
	 * @return
	 */
	function getGames()
	{
	   $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
	   $query = $db->getQuery(true);
       //$subquery1 = $db->getQuery(true);
       
		$teamplayers = self::getTeamPlayers();
		$games = array();
        
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' teamplayers<br><pre>'.print_r($teamplayers,true).'</pre>'),'Error');
        
		if (count($teamplayers))
		{
			$quoted_tpids=array();
			foreach ($teamplayers as $teamplayer)
			{
				$quoted_tpids[] = $db->Quote($teamplayer->id);
			}
			$tpid_list = '('.implode(',', $quoted_tpids).')';

			// Get all games played by the player (possible of multiple teams in the project)
			// A player was in a match if:
			// 1. He is defined as a match player in the match
			// 2. There is one or more statistic on his name for the match
			// 3. There is one or more event on his name for the match
     

			
            $query->select('m.*');
            $query->select('t1.id AS team1');
            $query->select('t2.id AS team2');
            $query->select('mp.teamplayer_id');
            $query->select('r.roundcode,r.project_id');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp ON mp.match_id = m.id');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round r ON m.round_id = r.id ');
			$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = r.project_id ');
			$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON m.projectteam1_id = pt1.id ');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st1 ON st1.id = pt1.team_id'); 
			$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON t1.id = st1.team_id ');
			$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON m.projectteam2_id = pt2.id ');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st2 ON st2.id = pt2.team_id'); 
			$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON t2.id = st2.team_id');
            
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS stp ON stp.id = mp.teamplayer_id');
            
            $query->where('mp.teamplayer_id IN ( '.$tpid_list.' )');
            $query->where('p.id = '.self::$projectid );
            $query->where('m.published = 1');
            $query->where('p.published = 1');
            
            $query->order('m.match_date');
            
            $db->setQuery($query);
			$games =  $db->loadObjectList();
		}
        
        if ( !$games && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
        elseif ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' tpid_list<br><pre>'.print_r($tpid_list,true).'</pre>'),'');
        }
        
        foreach ($games as $game)
			{
            
            $inoutstats = self::getInOutStats($game->project_id, $game->projectteam1_id, $game->teamplayer_id, 0, $game->id );
			$game->started += $inoutstats->started;
			$game->sub_in += $inoutstats->sub_in ;
			$game->sub_out += $inoutstats->sub_out;
            $game->playedtime += 0; 
             
            } 
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' games<br><pre>'.print_r($games,true).'</pre>'),'');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' inoutstats<br><pre>'.print_r($inoutstats,true).'</pre>'),'');
        }
        
		return $games;
	}

	/**
	 * sportsmanagementModelPlayer::getGamesEvents()
	 * 
	 * @return
	 */
	function getGamesEvents()
	{
	   $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.		
	   $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
	   $query = $db->getQuery(true);
       
       
		$teamplayers = self::getTeamPlayers();
		$gameevents = array();
        
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' teamplayers<br><pre>'.print_r($teamplayers,true).'</pre>'),'Error');
        
		if (count($teamplayers))
		{
			$quoted_tpids = array();
			foreach ($teamplayers as $teamplayer)
			{
				$quoted_tpids[]=$this->_db->Quote($teamplayer->id);
			}
            
            $query->select('SUM(me.event_sum) as value,me.*');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event AS me ');
            $query->where('me.teamplayer_id IN ('. implode(',', $quoted_tpids) .')');
            $query->group('me.match_id, me.event_type_id');

			$db->setQuery($query);
			$events = $db->loadObjectList();
            
            //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' events<br><pre>'.print_r($events,true).'</pre>'),'Error');
            
			foreach ((array) $events as $ev)
			{
				if (isset($gameevents[$ev->match_id]))
				{
					$gameevents[$ev->match_id][$ev->event_type_id] = $ev->value;
				}
				else
				{
					$gameevents[$ev->match_id] = array($ev->event_type_id => $ev->value);
				}
			}
		}
		return $gameevents;
	}
    
    
    
    

}

?>