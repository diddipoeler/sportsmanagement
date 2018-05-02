<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      staff.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage staff
 */
 
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');

/**
 * sportsmanagementModelStaff
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelStaff extends JModelLegacy
{
	static $projectid = 0;
	static $personid = 0;
	static $teamplayerid = 0;
    static $teamid = 0;
    
    /**
	 * data array for staff history
	 * @var array
	 */
	static $_history = null;
    
    static $_inproject = null;
    
    static $cfg_which_database = 0;


 	/**
 	 * sportsmanagementModelStaff::__construct()
 	 * 
 	 * @return void
 	 */
 	function __construct()
 	{
 		
 		self::$projectid = JFactory::getApplication()->input->getInt('p',0);
 		self::$personid = JFactory::getApplication()->input->getInt('pid',0);
 		self::$teamid = JFactory::getApplication()->input->getInt('tid',0);
        self::$cfg_which_database = JFactory::getApplication()->input->getInt( 'cfg_which_database', 0 );
        parent::__construct();
 	}


	/**
	 * sportsmanagementModelStaff::getTeamStaff()
	 * 
	 * @return
	 */
	function getTeamStaff()
	{
	   $app = JFactory::getApplication();
    $option = JFactory::getApplication()->input->getCmd('option');
        // Create a new query object.		
	   $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
	   $query = $db->getQuery(true);
       
		if (is_null(self::$_inproject))
		{
		  $query->select('ts.*,ts.picture as picture,ts.picture as season_picture');
          $query->select('pos.name AS position_name');
          $query->select('ppos.id AS pPosID, ppos.position_id');
          $query->from('#__sportsmanagement_season_team_person_id AS ts'); 
          $query->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = ts.team_id AND st.season_id = ts.season_id');
          $query->join('INNER','#__sportsmanagement_project_team AS pt ON st.id = pt.team_id');
          
          $query->join('LEFT','#__sportsmanagement_project_position AS ppos ON ppos.id = ts.project_position_id');
          $query->join('LEFT','#__sportsmanagement_position AS pos ON pos.id = ppos.position_id');
        
          $query->where('pt.project_id = '.self::$projectid );
          $query->where('ts.person_id = '.self::$personid );
          $query->where('ts.published = 1');
          
			$db->setQuery($query);
			self::$_inproject = $db->loadObject();
		}
		return self::$_inproject;
	}

	/**
	 * get person history across all projects,with team,season,position,... info
	 *
	 * @param int $person_id,linked to player_id from Person object
	 * @param int $order ordering for season and league,default is ASC ordering
	 * @param string $filter e.g. "s.name=2007/2008",default empty string
	 * @return array of objects
	 */
	function getStaffHistory($order='ASC')
	{
		$app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
		$query = $db->getQuery(true);
        
        //$app->enqueueMessage(JText::_('getStaffHistory personid<br><pre>'.print_r($this->personid,true).'</pre>'),'');
        
        //if (empty($this->_history))
		//{
			$personid = self::$personid;
            
            // Select some fields
		    $query->select('pr.id AS pid,pr.firstname,pr.lastname');
            $query->select('o.person_id,o.picture as season_picture');
            $query->select('tt.project_id,tt.id AS ptid');
            $query->select('t.id AS team_id,t.name AS team_name,CONCAT_WS(\':\',t.id,t.alias) AS team_slug');
            $query->select('p.name AS project_name,CONCAT_WS(\':\',p.id,p.alias) AS project_slug');
            $query->select('s.name AS season_name');
            $query->select('ppos.position_id');
            $query->select('pos.name AS position_name,pos.id AS posID');
            //$query->from('#__sportsmanagement_person AS pr ');
            //$query->join('INNER','#__sportsmanagement_season_team_person_id AS o ON o.person_id = pr.id');
            $query->from('#__sportsmanagement_season_team_person_id AS o');
            $query->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = o.team_id AND st.season_id = o.season_id');
            $query->join('INNER','#__sportsmanagement_person AS pr ON o.person_id = pr.id');
            $query->join('INNER','#__sportsmanagement_project_team AS tt ON tt.team_id = st.id');
            $query->join('INNER','#__sportsmanagement_team AS t ON t.id = o.team_id');
            $query->join('INNER','#__sportsmanagement_project AS p ON p.id = tt.project_id');
            $query->join('INNER','#__sportsmanagement_season AS s ON s.id = p.season_id');
            $query->join('INNER','#__sportsmanagement_league AS l ON l.id = p.league_id');
            $query->join('LEFT','#__sportsmanagement_project_position AS ppos ON ppos.id = o.project_position_id');
            $query->join('LEFT','#__sportsmanagement_position AS pos ON pos.id = ppos.position_id ');
            //$query->where('pr.id = '.self::$personid );
            $query->where('o.person_id = '.self::$personid );
            $query->where('pr.published = 1');
            $query->where('o.published = 1');
            $query->where('p.published = 1');
            $query->where('o.persontype = 2');
            $query->order('s.ordering '.$order.', l.ordering ASC, p.name ASC ');
            $db->setQuery($query);
			self::$_history = $db->loadObjectList();
   
		//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
        if ( !self::$_history )
        {
            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
        
		return self::$_history;
	}

	/**
	 * sportsmanagementModelStaff::getPresenceStats()
	 * 
	 * @param mixed $project_id
	 * @param mixed $person_id
	 * @return
	 */
	function getPresenceStats($project_id,$person_id)
	{
	   $app = JFactory::getApplication();
    $option = JFactory::getApplication()->input->getCmd('option');
        // Create a new query object.		
	   $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
	   $query = $db->getQuery(true);
       
		$query='	SELECT	count(mp.id) AS present
					FROM #__sportsmanagement_match_staff AS mp
					INNER JOIN #__sportsmanagement_match AS m ON mp.match_id=m.id
					INNER JOIN #__sportsmanagement_team_staff AS tp ON tp.id=mp.team_staff_id
					INNER JOIN #__sportsmanagement_project_team AS pt ON m.projectteam1_id=pt.id
					WHERE tp.person_id='.$this->_db->Quote((int)$person_id).' 
					  AND pt.project_id='.$this->_db->Quote((int)$project_id) . '
					  AND tp.published = 1';
		$db->setQuery($query,0,1);
		$inoutstat = $db->loadResult();
		return $inoutstat;
	}

	/**
	 * get stats for the player position
	 * @return array
	 */
	function getStats()
	{
		$staff = self::getTeamStaff();
		if(!isset($staff->position_id))
        {
            $staff->position_id=0;
        }
		$result = sportsmanagementModelProject::getProjectStats(0,$staff->position_id,self::$cfg_which_database);
		return $result;
	}

	/**
	 * get player stats
	 * @return array
	 */
	function getStaffStats()
	{
		$staff = self::getTeamStaff();
		if (!isset($staff->position_id))
        {
            $staff->position_id=0;
        }
		$stats = sportsmanagementModelProject::getProjectStats(0,$staff->position_id,self::$cfg_which_database);
		$history = self::getStaffHistory();
		$result = array();
		if(count($history) > 0 && count($stats) > 0)
		{
			foreach ($history as $player)
			{
				foreach ($stats as $stat)
				{
					if(!isset($stat) && $stat->position_id != null)
					{
						$result[$stat->id][$player->project_id] = $stat->getStaffStats($player->person_id,$player->team_id,$player->project_id);
					}
				}
			}
		}
		return $result;
	}

	/**
	 * sportsmanagementModelStaff::getHistoryStaffStats()
	 * 
	 * @return
	 */
	function getHistoryStaffStats()
	{
		$staff = self::getTeamStaff();
		$stats = sportsmanagementModelProject::getProjectStats(0,$staff->position_id,self::$cfg_which_database);
		$result = array();
		if (count($stats) > 0)
		{
			foreach ($stats as $stat)
			{
				if (!isset($stat))
				{
					$result[$stat->id] = $stat->getHistoryStaffStats($staff->person_id);
				}
			}
		}
		return $result;
	}

}
?>