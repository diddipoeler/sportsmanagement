<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      teamplan.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage teamplan
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');

/**
 * sportsmanagementModelTeamPlan
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelTeamPlan extends JModelLegacy
{
	static $projectid = 0;
	static $teamid = 0;
    static $projectteamid = 0;
	static $pro_teamid = 0;
	var $team = null;
	var $club = null;
	static $divisionid = 0;
	static $mode = 0;
    
    static $cfg_which_database = 0;

	/**
	 * sportsmanagementModelTeamPlan::__construct()
	 * 
	 * @return
	 */
	function __construct()
	{
		$app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;

		self::$projectid = (int) $jinput->get('p',0, '');
		self::$teamid = (int) $jinput->get('tid',0, '');
        self::$projectteamid = (int) $jinput->get('ptid',0, '');
        self::$pro_teamid = (int) $jinput->get('ptid',0, '');
		self::$divisionid = (int) $jinput->get('division',0, '');
		self::$mode = (int) $jinput->get('mode',0, '');
        self::$cfg_which_database = (int) $jinput->get('cfg_which_database',0, '');
//        sportsmanagementModelProject::setProjectID($jinput->getInt('p',0),self::$cfg_which_database);
sportsmanagementModelProject::$projectid = self::$projectid;
sportsmanagementModelProject::$cfg_which_database= self::$cfg_which_database;		
        parent::__construct();
	}

	/**
	 * sportsmanagementModelTeamPlan::getDivisionID()
	 * 
	 * @return
	 */
	function getDivisionID()
	{
		return self::$divisionid;
	}

	/**
	 * sportsmanagementModelTeamPlan::getMode()
	 * 
	 * @return
	 */
	function getMode()
	{
		return $this->mode;
	}

	/**
	 * sportsmanagementModelTeamPlan::getDivision()
	 * 
	 * @return
	 */
	public static function getDivision()
	{
	$option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
       // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
        $starttime = microtime(); 
        
        $division = null;
		if ( self::$divisionid > 0 )
		{
			
        // Select some fields
        $query->select('d.*,CONCAT_WS(\':\',id,alias) AS slug');
        // From 
	$query->from('#__sportsmanagement_division AS d');
        // Where
        $query->where('d.id = '.self::$divisionid);
        $db->setQuery($query,0,1);

        try{
	$division = $db->loadObject();
	$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect	
          }
            catch (Exception $e)
            {
	$app->enqueueMessage(JText::_($e->getMessage()), 'error');	
	$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect	
            }	  
            
        
		}
		return $division;
	}

	/**
	 * sportsmanagementModelTeamPlan::getProjectTeamId()
	 * 
	 * @return
	 */
	public static function getProjectTeamId()
	{
		$option = JFactory::getApplication()->input->getCmd('option');
	   $app = JFactory::getApplication();
       // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
        $starttime = microtime(); 
        
        // Select some fields
        $query->select('pt.id');
        // From 
	$query->from('#__sportsmanagement_project_team as pt');
        $query->join('INNER',' #__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
        $query->join('INNER',' #__sportsmanagement_team as t ON t.id = st.team_id ');
        // Where
        $query->where('pt.project_id = '.self::$projectid);
        $query->where('t.id='.self::$teamid);
	$db->setQuery($query,0,1);
		
		try{
		$result = $db->loadResult();
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect	
		}
        catch (Exception $e)
            {
	self::$pro_teamid = 0;
            return 0;	
	$app->enqueueMessage(JText::_($e->getMessage()), 'error');	
	$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect	
            }
       
        
		self::$pro_teamid = $result;
        self::$projectteamid = $result;
		return $result;
	}

	/**
	 * sportsmanagementModelTeamPlan::getMatchesPerRound()
	 * 
	 * @param mixed $config
	 * @param mixed $rounds
	 * @return
	 */
	public static function getMatchesPerRound($config,$rounds)
	{
	   $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
		$rm=array();

		$ordering='DESC';
		if ($config['plan_order'])
		{
			$ordering=$config['plan_order'];
		}
		foreach ($rounds as $round)
		{
			$matches = self::_getResultsRows($round->roundcode,self::$pro_teamid,$ordering,0,1,$config['show_referee']);
			$rm[$round->roundcode] = $matches;
		}
		return $rm;
	}

	/**
	 * sportsmanagementModelTeamPlan::getMatches()
	 * 
	 * @param mixed $config
	 * @return
	 */
	public static function getMatches($config)
	{
	   $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        
		$ordering = 'DESC';
		if ($config['plan_order'])
		{
			$ordering = $config['plan_order'];
		}
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
		{
		$my_text = 'pro_teamid -><pre>'.print_r(self::$pro_teamid,true).'</pre>';
          //$my_text .= 'dump -><pre>'.print_r($query->dump(),true).'</pre>';  
          sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text);  
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' pro_teamid'.'<pre>'.print_r($this->pro_teamid,true).'</pre>' ),'');
        }
        
		return self::_getResultsPlan(self::$pro_teamid,$ordering,0,1,$config['show_referee']);
	}

	/**
	 * sportsmanagementModelTeamPlan::getMatchesRefering()
	 * 
	 * @param mixed $config
	 * @return
	 */
	public static function getMatchesRefering($config)
	{
	   $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
		$ordering = 'DESC';
		if ($config['plan_order'])
		{
			$ordering = $config['plan_order'];
		}
		return self::_getResultsPlan(0,$ordering,self::$pro_teamid,1,$config['show_referee']);
	}

	/**
	 * sportsmanagementModelTeamPlan::_getResultsPlan()
	 * 
	 * @param integer $team
	 * @param string $ordering
	 * @param integer $referee
	 * @param integer $getplayground
	 * @param integer $getreferee
	 * @return
	 */
	public static function _getResultsPlan($team=0,$ordering='ASC',$referee=0,$getplayground=0,$getreferee=0)
	{
		$app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
       // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
        $query2 = $db->getQuery(true);
        $starttime = microtime(); 

        $matches = array();
		$project = sportsmanagementModelProject::getProject(self::$cfg_which_database);

		if (self::$divisionid > 0)
		{
		$query->clear();
        // Select some fields
        $query->select('id');
        // From 
	$query->from('#__sportsmanagement_division');
        // Where
        $query->where('parent_id = '.self::$divisionid);

			$db->setquery($query);
            
			if(version_compare(JVERSION,'3.0.0','ge')) 
        {
        // Joomla! 3.0 code here
        $div_for_teams = $db->loadColumn();
        }
        elseif(version_compare(JVERSION,'2.5.0','ge')) 
        {
        // Joomla! 2.5 code here
        $div_for_teams = $db->loadResultArray();
        } 

			$div_for_teams[] = self::getDivision()->id;
		}

try{
		$query->clear();
        // Select some fields
        $query->select('m.*,DATE_FORMAT(m.time_present,"%H:%i") time_present,r.roundcode,r.id roundid,r.project_id,r.name');
        $query->select('t1.id AS team1');
        $query->select('t2.id AS team2');
        $query->select('CONCAT_WS(\':\',m.id,CONCAT_WS("_",t1.alias,t2.alias)) AS match_slug ');
        $query->select('CONCAT_WS(\':\',r.id,r.alias) AS round_slug');
        $query->select('CONCAT_WS(\':\',p.id,p.alias) AS project_slug');
        $query->select('CONCAT_WS(\':\',d.id,d.alias) AS division_slug');
        // From 
		$query->from('#__sportsmanagement_match AS m');
        // Join 
        $query->join('INNER',' #__sportsmanagement_round AS r ON m.round_id = r.id ');
        $query->join('INNER',' #__sportsmanagement_project AS p ON p.id = r.project_id ');
        $query->join('LEFT',' #__sportsmanagement_division AS d ON d.id = m.division_id ');
        
        $query->join('LEFT',' #__sportsmanagement_project_team as pt1 ON pt1.id = m.projectteam1_id ');
        $query->join('LEFT',' #__sportsmanagement_season_team_id as st1 ON st1.id = pt1.team_id ');
        $query->join('LEFT',' #__sportsmanagement_team as t1 ON st1.team_id = t1.id ');
        
        $query->join('LEFT',' #__sportsmanagement_project_team as pt2 ON pt2.id = m.projectteam2_id ');
        $query->join('LEFT',' #__sportsmanagement_season_team_id as st2 ON st2.id = pt2.team_id ');
        $query->join('LEFT',' #__sportsmanagement_team as t2 ON st2.team_id = t2.id ');
        
        // Where
        $query->where('m.published=1');

/**
 * win matches
 */
		if ((self::$mode)== 1)
		{
		  //$query->where('( (m.projectteam1_id= ' .$team. ' AND m.team1_result > m.team2_result)'.' OR (m.projectteam2_id= ' .$team. ' AND m.team1_result < m.team2_result) )');
          $query->where('( (m.projectteam1_id = ' .self::$projectteamid. ' AND m.team1_result > m.team2_result)'.' OR (m.projectteam2_id = ' .self::$projectteamid. ' AND m.team1_result < m.team2_result) )');
		}
/**
 * draw matches
 */
		if ((self::$mode)== 2)
		{
		  $query->where('m.team1_result = m.team2_result');
		}
/**
 * lost matches
 */
		if ((self::$mode)== 3)
		{
			$query->where('( (m.projectteam1_id = ' .self::$projectteamid. ' AND m.team1_result < m.team2_result)'.' OR (m.projectteam2_id = ' .self::$projectteamid. ' AND m.team1_result > m.team2_result) )');
		}
	
		if (self::$divisionid > 0)
		{
		  $query->where('(pt1.division_id IN ('.(implode(',',$div_for_teams)).') OR pt2.division_id IN ('.(implode(',',$div_for_teams)).')  OR m.division_id IN ('.(implode(',',$div_for_teams)).')   )');
		}

		if ($referee != 0)
		{
			$query->select('p.name AS project_name');
            $query->join('INNER',' #__sportsmanagement_match_referee AS mref ON mref.match_id = m.id ');
            $query->where('mref.project_referee_id = '.$referee);
            $query->where('p.season_id = '.$project->season_id);
		}
		else
		{
            $query->where('r.project_id = '.self::$projectid);
		}

		if (self::$teamid != 0)
		{
            $query->where("(m.projectteam1_id = ".self::$projectteamid." OR m.projectteam2_id = ".self::$projectteamid.")");
		}
        
        // Group
        //$query->group('m.id');
        // Order
        $query->order("r.roundcode ".$ordering.",m.match_date,m.match_number");

		if ($getplayground)
		{
            $query->select('playground.name AS playground_name,playground.short_name AS playground_short_name');
	$query->select('CONCAT_WS( \':\', playground.id, playground.alias ) AS playground_slug');	
            $query->join('LEFT',' #__sportsmanagement_playground AS playground ON playground.id = m.playground_id ');
		}
		
		$db->setQuery($query);
               
	$matches = $db->loadObjectList();
	$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect	
          }
            catch (Exception $e)
            {
	$app->enqueueMessage(JText::_($e->getMessage()), 'error');	
	$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect	
            }	

		if ($getreferee)
		{
			self::_getRefereesByMatch($matches,$project);
		}
		
		return $matches;
	}

	/**
	 * sportsmanagementModelTeamPlan::_getResultsRows()
	 * 
	 * @param integer $roundcode
	 * @param integer $teamId
	 * @param string $ordering
	 * @param integer $unpublished
	 * @param integer $getplayground
	 * @param integer $getreferee
	 * @return
	 */
	public static function _getResultsRows($roundcode=0,$teamId=0,$ordering='ASC',$unpublished=0,$getplayground=0,$getreferee=0)
	{
		$app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
       // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
        $starttime = microtime();
        
        $matches = array();

		$project = sportsmanagementModelProject::getProject(self::$cfg_which_database);
        
        // Select some fields
        $query->select('matches.*');
        // From 
		$query->from('#__sportsmanagement_match AS matches');
        // Join 
        $query->join('INNER',' #__sportsmanagement_round AS r ON matches.round_id = r.id ');
        // Where
        $query->where('r.project_id = '.self::$projectid);
        $query->where('r.roundcode = '.$roundcode);

		if ($teamId)
		{
		  //$query->where("(matches.projectteam1_id=".$teamId." OR matches.projectteam2_id=".$teamId.")");
          $query->where("(matches.projectteam1_id = ".self::$projectteamid." OR matches.projectteam2_id = ".self::$projectteamid.")");
		}
		// Group
        //$query->group('matches.id');
        // Order
        $query->order('matches.match_date '.$ordering.',matches.match_number');

		if (self::$divisionid > 0)
		{
		  $query->join('LEFT',' #__sportsmanagement_project_team as pt1 ON pt1.id = matches.projectteam1_id ');
          $query->join('LEFT',' #__sportsmanagement_project_team as pt2 ON pt2.id = matches.projectteam2_id ');
          
          $query->join('LEFT',' #__sportsmanagement_division AS d1 ON pt1.division_id = d1.id ');
          $query->join('LEFT',' #__sportsmanagement_division AS d2 ON pt2.division_id = d2.id ');
          $query->where("(d1.id = ".self::$divisionid." OR d1.parent_id = ".self::$divisionid." OR d2.id = ".self::$divisionid." OR d2.parent_id = ".self::$divisionid." OR matches.division_id = ".self::$divisionid." )");
		}

		if ($unpublished != 1)
		{
		  $query->where('matches.published=1');
		}

		if ($getplayground)
		{
			$query->select('playground.name AS playground_name,playground.short_name AS playground_short_name');
			$query->join('LEFT',' #__sportsmanagement_playground AS playground ON playground.id = matches.playground_id');
		}

		$db->setQuery($query);
        
        try{
	$matches = $db->loadObjectList();
	$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect	
          }
            catch (Exception $e)
            {
	$app->enqueueMessage(JText::_($e->getMessage()), 'error');	
	$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect	
            }	

		if ($getreferee)
		{
			self::_getRefereesByMatch($matches,$project);
		}

		return $matches;
	}

	/**
	 * sportsmanagementModelTeamPlan::_getRefereesByMatch()
	 * 
	 * @param mixed $matches
	 * @param mixed $projekt
	 * @return
	 */
	public static function _getRefereesByMatch($matches,$projekt)
	{
	   	$option = JFactory::getApplication()->input->getCmd('option');
	   $app = JFactory::getApplication();
       // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
        $starttime = microtime();
        
		for ($index=0; $index < count($matches); $index++) 
        {
			$referees = array();
			if ($projekt->teams_as_referees)
			{
			 $query->clear();
			 // Select some fields
             $query->select('ref.name AS referee_name');
             // From 
             $query->from('#__sportsmanagement_team AS ref');
             $query->join('LEFT',' #__sportsmanagement_match_referee AS link ON link.project_referee_id=ref.id ');
             // Where
             $query->where('link.match_id = '.$matches[$index]->id);
             // Order
             $query->order('link.ordering');

			}
			else
			{
			 $query->clear();
			 // Select some fields
             $query->select('ref.firstname AS referee_firstname,ref.lastname AS referee_lastname,ref.id as referee_id');
             $query->select('ppos.position_id');
             $query->select('pos.name AS referee_position_name');
             // From 
             $query->from('#__sportsmanagement_person AS ref');
             $query->join('LEFT',' #__sportsmanagement_season_person_id AS sp ON sp.person_id = ref.id ');
             $query->join('LEFT',' #__sportsmanagement_project_referee AS pref ON pref.person_id = sp.id ');
             $query->join('LEFT',' #__sportsmanagement_match_referee AS link ON link.project_referee_id = pref.id ');
             
             $query->join('INNER',' #__sportsmanagement_project_position AS ppos ON ppos.id = link.project_position_id');
             $query->join('INNER',' #__sportsmanagement_position AS pos ON pos.id = ppos.position_id');

             // Where
             $query->where('link.match_id = '.$matches[$index]->id);
             $query->where('ref.published = 1');
             // Order
             $query->order('link.ordering');

			}

			$db->setQuery($query);
            
           try{
	$referees = $db->loadObjectList();
	$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect	
          }
            catch (Exception $e)
            {
	$app->enqueueMessage(JText::_($e->getMessage()), 'error');	
	$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect	
            }	
        
			
			
			$matches[$index]->referees = $referees;
		}
		return $matches;
	}

	/**
	 * sportsmanagementModelTeamPlan::getEventTypes()
	 * 
	 * @param mixed $match_id
	 * @return
	 */
	function getEventTypes($match_id)
	{
	$option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
       // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
	$result = NULL;
        // Select some fields
        $query->select('et.id as etid,me.event_type_id as id,et.*');
        // From 
	$query->from('#__sportsmanagement_eventtype as et');
        $query->join('INNER',' #__sportsmanagement_match_event as me ON et.id = me.event_type_id ');
        $query->join('INNER',' #__sportsmanagement_match as m ON m.id = me.match_id ');
        // Where
        $query->where('me.match_id = '.$match_id);
        // Order
        $query->order('et.ordering');

	$db->setQuery($query);
		
		 try{
	$result = $db->loadObjectList('etid');
	$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect	
          }
            catch (Exception $e)
            {
	$app->enqueueMessage(JText::_($e->getMessage()), 'error');	
	$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect	
            }	
		return $result;
	}

}
?>
