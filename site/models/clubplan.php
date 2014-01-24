<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

//require_once( JLG_PATH_SITE . DS . 'models' . DS . 'project.php' );

/**
 * sportsmanagementModelClubPlan
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelClubPlan extends JModel
{
	var $clubid = 0;
	var $project_id = 0;
	var $club = null;
	var $startdate = null;
	var $enddate = null;
	var $awaymatches = null;
	var $homematches = null;
	
    var $teamart = 0;
    var $teamprojects = 0;
    var $teamseasons = 0;
    
	function __construct()
	{
		parent::__construct();
		$this->clubid = JRequest::getInt("cid",0);
		$this->project_id = JRequest::getInt("p",0);
		$this->setStartDate(JRequest::getVar("startdate", $this->startdate,'request','string'));
		$this->setEndDate(JRequest::getVar("enddate",$this->enddate,'request','string'));
	}
    
    
    function getTeamsArt()
    {
        $option = JRequest::getCmd('option');
	   $mainframe = JFactory::getApplication();
       // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        if ($this->clubid > 0)
		{
		// Select some fields
        $query->select('info as value,info as text');
        // From 
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team');
        // Where
        $query->where('club_id = '.(int) $this->clubid);
        // Group
        $query->group('info');
        // Order
        $query->order('info ASC');

		$db->setQuery($query);
		$teamsart = $db->loadObjectList();
		}
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
       {
       $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' teamsart'.'<pre>'.print_r($teamsart,true).'</pre>' ),'');
       }
        
        return $teamsart;
    }
    
    function getTeamsProjects()
    {
        $option = JRequest::getCmd('option');
	   $mainframe = JFactory::getApplication();
       // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        if ($this->clubid > 0)
		{
		// Select some fields
        $query->select('p.id as value,p.name as text');
        // From 
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team as t');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st ON st.team_id = t.id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season as s ON s.id = st.season_id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team as pt ON pt.team_id = st.id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project as p ON p.id = pt.project_id ');
        // Where
        $query->where('t.club_id = '.(int) $this->clubid);
        // Group
        $query->group('p.id,p.name');
        // Order
        $query->order('p.name DESC');

		$db->setQuery($query);
		$teamsprojects = $db->loadObjectList();
		}
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
       {
       $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' teamsprojects'.'<pre>'.print_r($teamsprojects,true).'</pre>' ),'');
       }
        
        return $teamsprojects;
        
        
        
    }
    
    function getTeamsSeasons()
    {
        $option = JRequest::getCmd('option');
	   $mainframe = JFactory::getApplication();
       // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        if ($this->clubid > 0)
		{
		// Select some fields
        $query->select('s.id as value,s.name as text');
        // From 
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team as t');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st ON st.team_id = t.id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season as s ON s.id = st.season_id ');
        
        // Where
        $query->where('t.club_id = '.(int) $this->clubid);
        // Group
        $query->group('s.id,s.name');
        // Order
        $query->order('s.name DESC');

		$db->setQuery($query);
		$teamsseasons = $db->loadObjectList();
		}
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
       {
       $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' teamsseasons'.'<pre>'.print_r($teamsseasons,true).'</pre>' ),'');
       }
        
        return $teamsseasons;
        
        
    }



	function getTeams()
	{
		$option = JRequest::getCmd('option');
	   $mainframe = JFactory::getApplication();
       // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        $teams = array(0);
		if ($this->clubid > 0)
		{
		// Select some fields
        $query->select('id,name as team_name,short_name as team_shortcut,info as team_description');
        // From 
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team');
        // Where
        $query->where('club_id = '.(int) $this->clubid);

			$db->setQuery($query);
			$teams = $db->loadObjectList();
		}
        
       
       if ( !$teams )
       {
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
        }
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
       {
       $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' teams'.'<pre>'.print_r($teams,true).'</pre>' ),'');
       }
       
		return $teams;
	}

	function getStartDate()
	{
	   $mainframe = JFactory::getApplication();
       
       if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
       {
       $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' startdate vorher'.'<pre>'.print_r($this->startdate,true).'</pre>' ),'');
       }
	
    	$config = sportsmanagementModelProject::getTemplateConfig("clubplan");
		if (empty($this->startdate))
		{
			$dayz = $config['days_before'];
			//$dayz=6;
			$prevweek = mktime(0,0,0,date("m"),date("d")- $dayz,date("y"));
			$this->startdate = date("Y-m-d",$prevweek);
		}
		if($config['use_project_start_date']=="1") {
			$project = sportsmanagementModelProject::getProject();
			$this->startdate = $project->start_date;
		}
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
       {
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' startdate nachher'.'<pre>'.print_r($this->startdate,true).'</pre>' ),'');
        }
		return $this->startdate;
	}

	function getEndDate()
	{
	   $mainframe = JFactory::getApplication();
       
       if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
       {
       $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' enddate vorher'.'<pre>'.print_r($this->enddate,true).'</pre>' ),'');
       }
       
		if ( empty($this->enddate) )
		{
			$config = sportsmanagementModelProject::getTemplateConfig("clubplan");
			$dayz = $config['days_after'];
			//$dayz=6;
			$nextweek = mktime(0,0,0,date("m"),date("d")+ $dayz,date("y"));
			$this->enddate = date("Y-m-d",$nextweek);
		}
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
       {
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' enddate nachher'.'<pre>'.print_r($this->enddate,true).'</pre>' ),'');
        }
        
		return $this->enddate;
	}

	function setStartDate($date)
	{
	   $mainframe = JFactory::getApplication();
		// should be in proper sql format
		if (strtotime($date)) {
			$this->startdate = strftime("%Y-%m-%d",strtotime($date));
		}
		else {
			$this->startdate = null;
		}
	}

	function setEndDate($date)
	{
	   $mainframe = JFactory::getApplication();
		// should be in proper sql format
		if (strtotime($date)) {
			$this->enddate = strftime("%Y-%m-%d",strtotime($date));
		}
		else {
			$this->enddate = null;
		}
	}

	function getAllMatches($orderBy = 'ASC',$type = 0)
	{
		$option = JRequest::getCmd('option');
	   $mainframe = JFactory::getApplication();
       
       if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
       {
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' orderBy'.'<pre>'.print_r($orderBy,true).'</pre>' ),'');
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' type'.'<pre>'.print_r($type,true).'</pre>' ),'');
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' project_id'.'<pre>'.print_r($this->project_id,true).'</pre>' ),'');
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' clubid'.'<pre>'.print_r($this->clubid,true).'</pre>' ),'');
         }
        
       // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        
        $result = array();
		$teams = self::getTeams();
		$startdate = self::getStartDate();
		$enddate = self::getEndDate();

		if (is_null($teams)) 
        {
			return null;
		}
        
        // Select some fields
		$query->select('m.*,DATE_FORMAT(m.time_present,"%H:%i") time_present');
        $query->select('p.name AS project_name,p.id AS project_id,p.id AS prid,CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\',p.id,p.alias) ELSE p.id END AS project_slug');
        $query->select('r.id AS roundid,r.roundcode AS roundcode,r.name AS roundname');
        $query->select('l.name AS l_name');
        $query->select('playground.name AS pl_name');
        $query->select('t1.club_id as t1club_id,t1.id AS team1_id,t1.name AS tname1,t1.short_name AS tname1_short,t1.middle_name AS tname1_middle,t1.club_id AS club1_id,CASE WHEN CHAR_LENGTH(t1.alias) THEN CONCAT_WS(\':\',t1.id,t1.alias) ELSE t1.id END AS team1_slug');
        $query->select('t2.club_id as t2club_id,t2.id AS team1_id,t2.name AS tname2,t2.short_name AS tname2_short,t2.middle_name AS tname2_middle,t2.club_id AS club2_id,CASE WHEN CHAR_LENGTH(t2.alias) THEN CONCAT_WS(\':\',t2.id,t2.alias) ELSE t2.id END AS team2_slug');
        $query->select('c1.logo_small AS home_logo_small,CASE WHEN CHAR_LENGTH(c1.alias) THEN CONCAT_WS(\':\',c1.id,c1.alias) ELSE c1.id END AS club1_slug');
        $query->select('c2.logo_small AS away_logo_small,CASE WHEN CHAR_LENGTH(c2.alias) THEN CONCAT_WS(\':\',c2.id,c2.alias) ELSE c2.id END AS club2_slug');
        $query->select('tj1.division_id');
        $query->select('d.name AS division_name, d.shortname AS division_shortname, d.parent_id AS parent_division_id,CASE WHEN CHAR_LENGTH(d.alias) THEN CONCAT_WS(\':\',d.id,d.alias) ELSE d.id END AS division_slug');
        // From 
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m');
        // Join 
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team as tj1 ON tj1.id = m.projectteam1_id ');
		$query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team as tj2 ON tj2.id = m.projectteam2_id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st1 ON st1.id = tj1.team_id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st2 ON st2.id = tj2.team_id ');
		$query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_team as t1 ON t1.id = st1.team_id ');
		$query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_team as t2 ON t2.id = st2.team_id ');
		$query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = tj1.project_id ');
		$query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_league as l ON p.league_id = l.id ');
		$query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_club as c1 ON c1.id = t1.club_id ');
		$query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_round as r ON m.round_id = r.id ');
		$query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_club as c2 ON c2.id = t2.club_id ');
		$query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_playground AS playground ON playground.id = m.playground_id ');
		$query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_division as d ON d.id = tj1.division_id');
        // Where
        $query->where('p.published = 1');
        
        if ( $this->project_id == 0 && $this->teamart == 0 && $this->teamseasons == 0)
        {
        $query->where('(m.match_date BETWEEN '.$db->Quote($startdate).' AND '.$db->Quote($enddate).')');
        }

        if( $this->project_id > 0 ) 
        {
			// Where
            $query->where('p.id = '. $db->Quote($this->project_id));
		}
        
        if( $this->teamart > 0 ) 
        {
			// Where
            $query->where("(t1.info LIKE '".$db->Quote($this->teamart)." OR t2.info LIKE '".$db->Quote($this->teamart) . "')");
		}
        
        if( $this->teamseasons > 0 ) 
        {
			// Where
            $query->where('p.season_id = '. $db->Quote($this->teamseasons));
		}
		
        if( $this->clubid > 0 ) 
        {
            switch ($type) 
        {
			case 0:
            case 3:  
            case 4: 
            // Where
            $query->where('(t1.club_id = '.$db->Quote($this->clubid).' OR t2.club_id = '.$db->Quote($this->clubid) . ')' );
            break;
            case 1:
            // Where
            $query->where('t1.club_id = '.$db->Quote($this->clubid) );
            break;
            case 2:
            // Where
            $query->where('t2.club_id = '.$db->Quote($this->clubid) );
            break;
        }
            
		}
        
        // Where
        $query->where('m.published = 1');
        // Order
        $query->order('m.match_date '.$orderBy);
            
		
		
        $db->setQuery($query);
		$this->allmatches = $db->loadObjectList();
        
        if ( !$this->allmatches )
       {
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
        }
        
		return $this->allmatches;
	}



	function getMatchReferees($matchID)
	{
		$query=' SELECT	p.id,'
		      .' p.firstname,'
		      .' p.lastname,'
		      .' mp.project_position_id,'
		    .' CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\',p.id,p.alias) ELSE p.id END AS person_slug '
		      .' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee AS mp '
		      .' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS pref ON mp.project_referee_id=pref.id '
		      .' INNER JOIN	#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p ON pref.person_id=p.id '
		      .' WHERE mp.match_id='.(int)$matchID
		      .' AND p.published = 1';

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	function getClubIconHtmlSimple($logo_small,$country,$type=1,$with_space=0)
	{
		if ($type==1)
		{
			$params=array();
			$params["align"]="top";
			$params["border"]=0;
			if ($with_space==1)
			{
				$params["style"]="padding:1px;";
			}
			if ($logo_small=="")
			{
				$logo_small = sportsmanagementHelper::getDefaultPlaceholder("clublogosmall");
			}

			return JHTML::image($logo_small,"",$params);
		}
		elseif ($type==2 && isset($country))
		{
			return Countries::getCountryFlag($team->country);
		}
	}

}
?>