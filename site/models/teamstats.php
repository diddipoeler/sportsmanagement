<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      teamstats.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage teamstats
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.model');

/**
 * sportsmanagementModelTeamStats
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelTeamStats extends JModelLegacy
{
	static $projectid = 0;
	static $teamid = 0;
    static $projectteamid = 0;
	static $highest_home = null;
	static $highest_away = null;
	static $highestdef_home = null;
	static $highestdef_away = null;
	static $highestdraw_home = null;
	static $highestdraw_away = null;
	static $totalshome = null;
	static $totalsaway = null;
	static $matchdaytotals = null;
	static $totalrounds = null;
	static $attendanceranking = null;
    static $team = null;
    static $nogoals_against = 0;
    static $cfg_which_database = 0;

	/**
	 * sportsmanagementModelTeamStats::__construct()
	 * 
	 * @return
	 */
	function __construct( )
	{
		parent::__construct();
// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
		self::$projectid = JFactory::getApplication()->input->get('p', 0, 'INT');
		self::$teamid = JFactory::getApplication()->input->get('tid', 0, 'INT');
        self::$projectteamid = JFactory::getApplication()->input->get('ptid', 0, 'INT');
		sportsmanagementModelProject::$projectid = self::$projectid;
        self::$cfg_which_database = JFactory::getApplication()->input->get('cfg_which_database', 0, 'INT');
		//preload the team;
		self::getTeam();
	}

	/**
	 * sportsmanagementModelTeamStats::getTeam()
	 * 
	 * @return
	 */
	public static function getTeam( )
	{
	   $option = JFactory::getApplication()->input->getCmd('option');
	    $app = JFactory::getApplication();
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' teamid<br><pre>'.print_r($this->teamid,true).'</pre>'),'');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' team<br><pre>'.print_r($this->team,true).'</pre>'),'');
        
		# it should be checked if any tid is given in the params of the url
		# if ( is_null( $this->team ) )
		if ( !isset( self::$team ) )
		{
			if ( self::$teamid > 0 )
			{
			 $query->select('*');
             $query->from('#__sportsmanagement_team');
             $query->where('id = '. self::$teamid );
             $db->setQuery($query);
             self::$team = $db->loadObject();
//				$this->team = $this->getTable( 'Team', 'sportsmanagementTable' );
//				$this->team->load( $this->teamid );
			}
		}
        
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' team<br><pre>'.print_r($this->team,true).'</pre>'),'');
        
		return self::$team;
	}

	/**
	 * sportsmanagementModelTeamStats::getHighest()
	 * 
	 * @return
	 */
	public static function getHighest($homeaway, $which)
	{
	   $option = JFactory::getApplication()->input->getCmd('option');
	    $app = JFactory::getApplication();
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
        $starttime = microtime(); 
		
			$query->select('matches.id AS matchid, t1.name AS hometeam');
            $query->select('t2.name AS guestteam');
            $query->select('team1_result AS homegoals');
            $query->select('team2_result AS guestgoals');
            $query->select('t1.id AS team1_id');
            $query->select('t2.id AS team2_id');
            
            $query->select('CONCAT_WS(\':\',t1.id,t1.alias) AS team1_slug');
            $query->select('CONCAT_WS(\':\',t2.id,t2.alias) AS team2_slug');
        
        $query->select('pt1.id AS pt1_id');
        $query->select('pt2.id AS pt2_id');
        
        $query->select('st1.id AS st1_id');
        $query->select('st2.id AS st2_id');
        $query->select('CONCAT_WS(\':\',matches.id,CONCAT_WS("_",t1.alias,t2.alias)) AS match_slug ');
        $query->from('#__sportsmanagement_match as matches ');
        
        $query->join('INNER',' #__sportsmanagement_project_team AS pt1 ON pt1.id = matches.projectteam1_id ');
        $query->join('INNER',' #__sportsmanagement_project_team AS pt2 ON pt2.id = matches.projectteam2_id  ');
           
        $query->join('INNER',' #__sportsmanagement_season_team_id as st1 ON st1.id = pt1.team_id ');
        $query->join('INNER',' #__sportsmanagement_team AS t1 ON st1.team_id = t1.id ');
           
        $query->join('INNER',' #__sportsmanagement_season_team_id as st2 ON st2.id = pt2.team_id ');
        $query->join('INNER',' #__sportsmanagement_team AS t2 ON st2.team_id = t2.id ');
        
        $query->where('pt1.project_id = '.self::$projectid);
        
        $query->where('matches.published = 1');
        $query->where('alt_decision = 0');
        $query->where('(matches.cancel IS NULL OR matches.cancel = 0)');
        switch ($homeaway)
        {
            case 'HOME':
            $query->where('t1.id = '. self::$team->id);
            
            switch ($which)
            {
            case 'WIN':
            $query->where('team1_result > team2_result');
            $query->order('(team1_result-team2_result) DESC');
            break;
            case 'DEF':
            $query->where('team2_result > team1_result');
            $query->order('(team2_result-team1_result) DESC');
            break;
            case 'DRAW':
            $query->where('team2_result = team1_result');
            $query->order('team1_result DESC');
            break;
            }
            
            break;
            
            case 'AWAY':
            $query->where('t2.id = '. self::$team->id);
            
            switch ($which)
            {
            case 'WIN':
            $query->where('team2_result > team1_result');
            $query->order('(team2_result-team1_result) DESC');
            break;
            case 'DEF':
            $query->where('team1_result > team2_result');
            $query->order('(team1_result-team2_result) DESC');
            break;
            case 'DRAW':
            $query->where('team1_result = team2_result');
            $query->order('team2_result DESC');
            break;
            }
                        
            break;
        }

            $db->setQuery($query, 0, 1);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
       if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text = 'getErrorMsg <pre>'.print_r($db->getErrorMsg(),true).'</pre>';
            $my_text .= 'dump <pre>'.print_r($query->dump(),true).'</pre>';
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text);
        }
        
        $result= $db->loadObject( );
        
        if ( !$result )
        {
            //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
        
        return $result;
        
        
        
    }

    
    /**
     * sportsmanagementModelTeamStats::getNoGoalsAgainst()
     * 
     * @return
     */
    public static function getNoGoalsAgainst( )
    {
        $option = JFactory::getApplication()->input->getCmd('option');
	    $app = JFactory::getApplication();
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
        $starttime = microtime(); 
        
    	if ( (!isset( self::$nogoals_against )) || is_null( self::$nogoals_against ) )
    	{
           $query->select('COUNT( round_id ) AS totalzero ');
	       $query->select('SUM( t1.id = '.self::$team->id.' AND team2_result=0 ) AS homezero ');
           $query->select('SUM( t2.id = '.self::$team->id.' AND team1_result=0 ) AS awayzero ');
           $query->from('#__sportsmanagement_match AS matches');
           $query->join('INNER',' #__sportsmanagement_project_team pt1 ON pt1.id = matches.projectteam1_id ');
           $query->join('INNER',' #__sportsmanagement_season_team_id as st1 ON st1.id = pt1.team_id ');
           $query->join('INNER',' #__sportsmanagement_team AS t1 ON st1.team_id = t1.id ');
           
           $query->join('INNER',' #__sportsmanagement_project_team pt2 ON pt2.id = matches.projectteam2_id ');
           $query->join('INNER',' #__sportsmanagement_season_team_id as st2 ON st2.id = pt2.team_id ');
           $query->join('INNER',' #__sportsmanagement_team AS t2 ON st2.team_id = t2.id ');
        
           $query->where('pt1.project_id = '.self::$projectid);
           $query->where('matches.published = 1 ');
           $query->where('alt_decision = 0');
           $query->where('( (t1.id = '.self::$team->id.' AND team2_result=0 ) OR (t2.id = '.self::$team->id.' AND team1_result=0 ) ) ');
           $query->where('( matches.cancel IS NULL OR matches.cancel = 0 )');
                   
    		$db->setQuery($query);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
    		self::$nogoals_against = $db->loadObject( );
    	}
    	return self::$nogoals_against;
    }
    
    
    /**
     * sportsmanagementModelTeamStats::getSeasonTotals()
     * 
     * @param mixed $which
     * @return
     */
    public static function getSeasonTotals($which)
    {
        $option = JFactory::getApplication()->input->getCmd('option');
	    $app = JFactory::getApplication();
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
        $starttime = microtime(); 

        $query->select('COUNT(matches.id) AS totalmatches ');
        $query->select('COUNT(team1_result) AS playedmatches ');
//	    $query->select('IFNULL(SUM(team1_result),0) AS goalsfor,IFNULL(SUM(team2_result),0) AS goalsagainst,IFNULL(SUM(team1_result + team2_result),0) AS totalgoals,IFNULL(SUM(IF(team1_result=team2_result,1,0)),0) AS totaldraw,IFNULL(SUM(IF(team1_result<team2_result,1,0)),0) AS totalloss,IFNULL(SUM(IF(team1_result>team2_result,1,0)),0) AS totalwin  ');
		$query->select('COUNT(crowd) AS attendedmatches ');
		$query->select('SUM(crowd) AS sumspectators ');
        $query->from('#__sportsmanagement_match AS matches');
        
        switch ($which)
        {
            case 'HOME':
            $query->select('IFNULL(SUM(team1_result),0) AS goalsfor,IFNULL(SUM(team2_result),0) AS goalsagainst,IFNULL(SUM(team1_result + team2_result),0) AS totalgoals,IFNULL(SUM(IF(team1_result=team2_result,1,0)),0) AS totaldraw,IFNULL(SUM(IF(team1_result<team2_result,1,0)),0) AS totalloss,IFNULL(SUM(IF(team1_result>team2_result,1,0)),0) AS totalwin  ');
            $query->join('INNER',' #__sportsmanagement_project_team pt1 ON pt1.id = matches.projectteam1_id ');
            break;
            case 'AWAY':
            $query->select('IFNULL(SUM(team2_result),0) AS goalsfor,IFNULL(SUM(team1_result),0) AS goalsagainst,IFNULL(SUM(team2_result + team1_result),0) AS totalgoals,IFNULL(SUM(IF(team2_result=team1_result,1,0)),0) AS totaldraw,IFNULL(SUM(IF(team2_result<team1_result,1,0)),0) AS totalloss,IFNULL(SUM(IF(team2_result>team1_result,1,0)),0) AS totalwin  ');
            $query->join('INNER',' #__sportsmanagement_project_team pt1 ON pt1.id = matches.projectteam2_id ');
            break;
        }
        
        $query->join('INNER',' #__sportsmanagement_season_team_id as st ON st.id = pt1.team_id ');
        $query->join('INNER',' #__sportsmanagement_team AS t ON st.team_id = t.id ');
        
        $query->where('pt1.project_id = '.self::$projectid);
        $query->where('matches.published = 1');
        $query->where('t.id = '.self::$team->id);
        $query->where('(matches.cancel IS NULL OR matches.cancel = 0)');
        
        $db->setQuery($query, 0, 1);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
    	//$this->totalshome = $db->loadObject();
    	
        if ( !$db->loadObject() )
        {
            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text = 'getErrorMsg <pre>'.print_r($db->getErrorMsg(),true).'</pre>';
            $my_text .= 'dump <pre>'.print_r($query->dump(),true).'</pre>';
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text);
        }
        
        switch ($which)
        {
            case 'HOME':
            if ( is_null( self::$totalshome ) )
    	    {
    	       self::$totalshome = $db->loadObject();
               return self::$totalshome;
    	    }   
            break;
            case 'AWAY':
            if ( is_null( self::$totalsaway ) )
    	    {
    	       self::$totalsaway = $db->loadObject();
               return self::$totalsaway;
    	    }
            break;
        }
        
    	
        
    }
    
		/**
		 * sportsmanagementModelTeamStats::getChartData()
		 * 
		 * @return
		 */
		function getChartData( )
		{
		  $option = JFactory::getApplication()->input->getCmd('option');
	    $app = JFactory::getApplication();
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
        
        $query->select('rounds.id');
        $query->select('SUM(CASE WHEN st1.team_id ='.self::$teamid.' THEN matches.team1_result ELSE matches.team2_result END) AS goalsfor');
        $query->select('SUM(CASE WHEN st1.team_id ='.self::$teamid.' THEN matches.team2_result ELSE matches.team1_result END) AS goalsagainst');
        $query->select('rounds.roundcode');
        
        $query->from('#__sportsmanagement_round AS rounds ');
        
        $query->join('INNER',' #__sportsmanagement_match AS matches ON rounds.id = matches.round_id ');
        $query->join('INNER',' #__sportsmanagement_project_team AS pt1 ON pt1.id = matches.projectteam1_id ');
        $query->join('INNER',' #__sportsmanagement_project_team AS pt2 ON pt2.id = matches.projectteam2_id  ');
           
        $query->join('INNER',' #__sportsmanagement_season_team_id as st1 ON st1.id = pt1.team_id ');
        $query->join('INNER',' #__sportsmanagement_team AS t1 ON st1.team_id = t1.id ');
           
        $query->join('INNER',' #__sportsmanagement_season_team_id as st2 ON st2.id = pt2.team_id ');
        $query->join('INNER',' #__sportsmanagement_team AS t2 ON st2.team_id = t2.id ');
        
        $query->where('rounds.project_id = '.self::$projectid);
        $query->where('( (st1.team_id ='.self::$teamid.' ) OR (st2.team_id ='.self::$teamid.' ) )' );
        $query->where('(matches.cancel IS NULL OR matches.cancel = 0)');
        $query->where('team1_result IS NOT NULL');
        $query->group('rounds.roundcode');   


                   
    		$db->setQuery( $query );
    		self::$matchdaytotals = $db->loadObjectList();
            
            if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $my_text = 'getErrorMsg <pre>'.print_r($db->getErrorMsg(),true).'</pre>';
            $my_text .= 'dump <pre>'.print_r($query->dump(),true).'</pre>';
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text);
            }
            
            if ( !self::$matchdaytotals )
        {
            //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
            
            
            
    		return self::$matchdaytotals;
    }
    
    /**
     * sportsmanagementModelTeamStats::getMatchDayTotals()
     * 
     * @return
     */
    public static function getMatchDayTotals( )
    {
        $option = JFactory::getApplication()->input->getCmd('option');
	    $app = JFactory::getApplication();
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
        
    	if ( is_null( self::$matchdaytotals ) )
    	{
    	   $query->select('rounds.id');
           $query->select('COUNT(matches.round_id) AS totalmatchespd');
           $query->select('COUNT(matches.id) as playedmatchespd');
           $query->select('SUM(matches.team1_result) AS homegoalspd');
           $query->select('SUM(matches.team2_result) AS guestgoalspd');
           $query->select('rounds.roundcode');

           $query->from('#__sportsmanagement_round AS rounds ');
           $query->join('INNER',' #__sportsmanagement_match AS matches ON rounds.id = matches.round_id ');
           $query->join('INNER',' #__sportsmanagement_project_team AS pt1 ON pt1.id = matches.projectteam1_id ');
           $query->join('INNER',' #__sportsmanagement_project_team AS pt2 ON pt2.id = matches.projectteam2_id  ');
           
           $query->join('INNER',' #__sportsmanagement_season_team_id as st1 ON st1.id = pt1.team_id ');
           $query->join('INNER',' #__sportsmanagement_team AS t1 ON st1.team_id = t1.id ');
           
           $query->join('INNER',' #__sportsmanagement_season_team_id as st2 ON st2.id = pt2.team_id ');
           $query->join('INNER',' #__sportsmanagement_team AS t2 ON st2.team_id = t2.id ');
           
           $query->where('rounds.project_id = '.self::$projectid);
           $query->where('( (st1.team_id ='.self::$teamid.' ) OR (st2.team_id ='.self::$teamid.' ) )' );
           $query->where('(matches.cancel IS NULL OR matches.cancel = 0)');
           $query->group('rounds.roundcode');
        
            
    		$db->setQuery( $query );
            
            if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $my_text = 'getErrorMsg <pre>'.print_r($db->getErrorMsg(),true).'</pre>';
            $my_text .= 'dump <pre>'.print_r($query->dump(),true).'</pre>';
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text);
         }
            
    		self::$matchdaytotals = $db->loadObjectList();
            
            if ( !self::$matchdaytotals )
        {
            //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
            
            
            
    	}
    	return self::$matchdaytotals;
    }

    /**
     * sportsmanagementModelTeamStats::getTotalRounds()
     * 
     * @return
     */
    public static function getTotalRounds( )
    {
        $option = JFactory::getApplication()->input->getCmd('option');
	    $app = JFactory::getApplication();
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
        $starttime = microtime(); 
        
        if ( is_null( self::$totalrounds ) )
        {
            $query->select('COUNT(id)');
           $query->from('#__sportsmanagement_round ');
           $query->where('project_id = '.self::$projectid);
                    
            $db->setQuery($query);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
            self::$totalrounds = $db->loadResult();
        }
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text = 'getErrorMsg <pre>'.print_r($db->getErrorMsg(),true).'</pre>';
            $my_text .= 'dump <pre>'.print_r($query->dump(),true).'</pre>';
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text);
        }
        
        if ( !self::$totalrounds )
        {
            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
        
        return self::$totalrounds;
    }

   
    /**
     * sportsmanagementModelTeamStats::_getAttendance()
     * 
     * @return
     */
    public static function _getAttendance( )
    {
        $option = JFactory::getApplication()->input->getCmd('option');
	    $app = JFactory::getApplication();
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
        $starttime = microtime(); 
        
    	if ( is_null( self::$attendanceranking ) )
    	{
    	   $query->select('matches.crowd');

        $query->from('#__sportsmanagement_match AS matches ');
        $query->join('INNER',' #__sportsmanagement_project_team AS pt1 ON pt1.id = matches.projectteam1_id ');
        $query->join('INNER',' #__sportsmanagement_season_team_id as st1 ON st1.id = pt1.team_id ');
        $query->join('INNER',' #__sportsmanagement_team AS t1 ON st1.team_id = t1.id ');
        $query->join('LEFT',' #__sportsmanagement_playground AS playground ON pt1.standard_playground = playground.id ');
        
        $query->where('st1.team_id = '.self::$teamid);
        $query->where('matches.crowd > 0 ');
        $query->where('matches.published = 1');

                       
    		$db->setQuery( $query );
        
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        if(version_compare(JVERSION,'3.0.0','ge')) 
        {
        // Joomla! 3.0 code here
        self::$attendanceranking = $db->loadColumn();
        }
        elseif(version_compare(JVERSION,'2.5.0','ge')) 
        {
        // Joomla! 2.5 code here
        self::$attendanceranking = $db->loadResultArray();
        } 

    	}
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text = 'getErrorMsg <pre>'.print_r($db->getErrorMsg(),true).'</pre>';
            $my_text .= 'dump <pre>'.print_r($query->dump(),true).'</pre>';
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text);
        }
            
    	         
        if ( !self::$attendanceranking )
        {
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
        }
        
    	return self::$attendanceranking;
    }

	/**
	 * sportsmanagementModelTeamStats::getBestAttendance()
	 * 
	 * @return
	 */
	public static function getBestAttendance( )
	{
		$attendance = self::_getAttendance();
		return (count($attendance)>0) ? max($attendance) : 0;
	}

	/**
	 * sportsmanagementModelTeamStats::getWorstAttendance()
	 * 
	 * @return
	 */
	public static function getWorstAttendance( )
	{
		$attendance = self::_getAttendance();
		return (count($attendance)>0) ? min($attendance) : 0;
	}

	/**
	 * sportsmanagementModelTeamStats::getTotalAttendance()
	 * 
	 * @return
	 */
	public static function getTotalAttendance( )
	{
		$attendance = self::_getAttendance();
		return (count($attendance)>0) ? array_sum($attendance) : 0;
	}
	
	/**
	 * sportsmanagementModelTeamStats::getAverageAttendance()
	 * 
	 * @return
	 */
	public static function getAverageAttendance( )
	{
		$attendance = self::_getAttendance();
		return (count($attendance)>0) ? round(array_sum($attendance)/count($attendance), 0) : 0;
	}

	/**
	 * sportsmanagementModelTeamStats::getChartURL()
	 * 
	 * @return
	 */
	public static function getChartURL( )
	{
		$url = sportsmanagementHelperRoute::getTeamStatsChartDataRoute( self::$projectid, self::$teamid,self::$cfg_which_database );
		$url = str_replace( '&', '%26', $url );
		return $url;
	}

	/**
	 * sportsmanagementModelTeamStats::getLogo()
	 * 
	 * @return
	 */
	public static function getLogo( )
	{
	   $option = JFactory::getApplication()->input->getCmd('option');
	    $app = JFactory::getApplication();
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
        
		$query->select('logo_big');
        $query->from('#__sportsmanagement_club AS clubs ');
        $query->join('LEFT',' #__sportsmanagement_team AS teams ON clubs.id = teams.club_id ');
        $query->where('teams.id = '.self::$teamid);
    	$db->setQuery( $query );
    	$logo = JURI::root().$db->loadResult();

		return $logo;
	}

	/**
	 * sportsmanagementModelTeamStats::getResults()
	 * 
	 * @return
	 */
	public static function getResults()
	{
	   $option = JFactory::getApplication()->input->getCmd('option');
	    $app = JFactory::getApplication();
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);
        $starttime = microtime(); 
        
        $query->select('m.id, m.projectteam1_id, m.projectteam2_id, pt1.team_id AS steam1_id, pt2.team_id AS steam2_id');
        $query->select('m.team1_result, m.team2_result');
        $query->select('m.alt_decision, m.team1_result_decision, m.team2_result_decision');
        $query->select('t1.id AS team1_id, t2.id AS team2_id');

        $query->from('#__sportsmanagement_match AS m ');
        $query->join('INNER',' #__sportsmanagement_project_team AS pt1 ON pt1.id = m.projectteam1_id ');
        $query->join('INNER',' #__sportsmanagement_project_team AS pt2 ON pt2.id = m.projectteam2_id  ');
           
        $query->join('INNER',' #__sportsmanagement_season_team_id as st1 ON st1.id = pt1.team_id ');
        $query->join('INNER',' #__sportsmanagement_team AS t1 ON st1.team_id = t1.id ');
         
        $query->join('INNER',' #__sportsmanagement_season_team_id as st2 ON st2.id = pt2.team_id ');
        $query->join('INNER',' #__sportsmanagement_team AS t2 ON st2.team_id = t2.id ');
           
        $query->where('pt1.project_id = '.self::$projectid);
        $query->where('( (st1.team_id = '.self::$teamid.' ) OR (st2.team_id = '.self::$teamid.' ) )' );
           
        $query->where('(m.team1_result IS NOT NULL OR m.alt_decision > 0)');
        $query->where('(m.cancel IS NULL OR m.cancel = 0)');
           


		$db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
        $matches = $db->loadObjectList();
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text = 'getErrorMsg <pre>'.print_r($db->getErrorMsg(),true).'</pre>';
            $my_text .= 'dump <pre>'.print_r($query->dump(),true).'</pre>';
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text);
        }
            
    	         
        if ( !$matches )
        {
            //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
		
		$results = array(	'win' => array(), 'tie' => array(), 'loss' => array(), 'forfeit' => array(),
							'home_wins' => 0, 'home_draws' => 0, 'home_losses' => 0, 
							'away_wins' => 0, 'away_draws' => 0, 'away_losses' => 0,);
		foreach ($matches as $match)
		{
			if (!$match->alt_decision)
			{
				if ($match->team1_id == self::$teamid)
				{
					// We are the home team
					if ($match->team1_result > $match->team2_result)
					{
						$results['win'][] = $match;
						$results['home_wins']++;
					}
					else if ($match->team1_result < $match->team2_result)
					{
						$results['loss'][] = $match;
						$results['home_losses']++;
					}
					else
					{
						$results['tie'][] = $match;
						$results['home_draws']++;
					}
				}
				else
				{
					// We are the away team
					if ($match->team1_result > $match->team2_result)
					{
						$results['loss'][] = $match;
						$results['away_losses']++;
					}
					else if ($match->team1_result < $match->team2_result)
					{
						$results['win'][] = $match;
						$results['away_wins']++;
					}
					else
					{
						$results['tie'][] = $match;
						$results['away_draws']++;
					}
				}
			}
			else
			{
				if ($match->team1_id == $this->teamid)
				{
					// We are the home team
					if (empty($match->team1_result_decision)) {
						$results['forfeit'][] = $match;
					}
					else if (empty($match->team2_result_decision)) {
						$results['win'][] = $match;
					}
					else {
						if ($match->team1_result_decision > $match->team2_result_decision) {
							$results['win'][] = $match;
							$results['home_wins']++;
						}
						else if ($match->team1_result_decision < $match->team2_result_decision) {
							$results['loss'][] = $match;
							$results['home_losses']++;
						}
						else {
							$results['tie'][] = $match;
							$results['home_draws']++;
						}
					}
				}
				else
				{
					// We are the away team
					if (empty($match->team2_result_decision)) {
						$results['forfeit'][] = $match;
					}
					else if (empty($match->team1_result_decision)) {
						$results['win'][] = $match;
					}
					else {
						if ($match->team1_result_decision > $match->team2_result_decision) {
							$results['loss'][] = $match;
							$results['away_losses']++;
						}
						else if ($match->team1_result_decision < $match->team2_result_decision) {
							$results['win'][] = $match;
							$results['away_wins']++;
						}
						else {
							$results['tie'][] = $match;
							$results['away_draws']++;
						}
					}
				}
			}
		}
		
		return $results;
	}
	
	/**
	 * sportsmanagementModelTeamStats::getStats()
	 * 
	 * @return
	 */
	function getStats()
	{
		$stats = sportsmanagementModelProject::getProjectStats(0,0,self::$cfg_which_database);
		
		// those are per positions, group them so that we have team globlas stats
		
		$teamstats = array();
		foreach ($stats as $pos => $pos_stats)
		{
			foreach ($pos_stats as $k => $stat) 
			{
				if ($stat->getParam('show_in_teamstats', 1))
				{
					if (!isset($teamstats[$k])) 
					{
						$teamstats[$k] = $stat;
						$teamstats[$k]->value = $stat->getRosterTotalStats($this->teamid, $this->projectid);
					}
				}
			}
		}
		
		return $teamstats;
	}
}
?>
