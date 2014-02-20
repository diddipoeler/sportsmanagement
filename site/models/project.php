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
jimport( 'joomla.utilities.arrayhelper' );

//require_once (JPATH_COMPONENT_ADMINISTRATOR .DS.'models'.DS.'rounds.php');

/**
 * sportsmanagementModelProject
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelProject extends JModel
{
	var $_project = null;
	var $projectid = 0;

	/**
	 * project league country
	 * @var string
	 */
	var $country = null;
	/**
	 * data array for teams
	 * @var array
	 */
	var $_teams = null;

	/**
	 * data array for matches
	 * @var array
	 */
	var $_matches = null;

	/**
	 * data array for rounds
	 * @var array
	 */
	var $_rounds = null;

	/**
	 * data project stats
	 * @var array
	 */
	var $_stats = null;

	/**
	 * data project positions
	 * @var array
	 */
	var $_positions = null;

	/**
	 * cache for project divisions
	 *
	 * @var array
	 */
	var $_divisions = null;

	/**
	 * caching for current round
	 * @var object
	 */
	var $_current_round;


	function __construct()
	{
		$this->projectid = JRequest::getInt('p',0);
		parent::__construct();
	}


	function getProject()
	{
		$option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        

      // $this->projectid = JRequest::getInt('p',0);
    
    if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
    $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' projectid<br><pre>'.print_r($this->projectid,true).'</pre>'),'');
    }
    
        if (is_null($this->_project) && $this->projectid > 0)
		{
			//fs_sport_type_name = sport_type folder name
            $query->select('p.*, l.country, st.id AS sport_type_id, st.name AS sport_type_name');
            $query->select('st.icon AS sport_type_picture, l.picture as leaguepicture');
            $query->select('LOWER(SUBSTR(st.name, CHAR_LENGTH( "COM_SPORTSMANAGEMENT_ST_")+1)) AS fs_sport_type_name');
            $query->select('CASE WHEN CHAR_LENGTH( p.alias ) THEN CONCAT_WS( \':\', p.id, p.alias ) ELSE p.id END AS slug');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type AS st ON p.sports_type_id = st.id ');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_league AS l ON p.league_id = l.id ');
            $query->where('p.id ='. $db->Quote($this->projectid));
            

			$db->setQuery($query,0,1);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
    $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' projectid<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
    }
    
			$this->_project = $db->loadObject();
            
            if ( !$this->_project && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
            
            
		}
		return $this->_project;
	}

	function setProjectID($id=0)
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        
		$this->projectid = $id;
		$this->_project = null;
        $this->_current_round = 0;
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' projectid<br><pre>'.print_r($this->projectid,true).'</pre>'),'');
        }
        
	}

	function getSportsType()
	{
		if (!$project = self::getProject())
		{
			$this->setError(0, Jtext::_('COM_SPORTSMANAGEMENT_ERROR_PROJECTMODEL_PROJECT_IS_REQUIRED'));
			return false;
		}
		
		return $project->sports_type_id;
	}
  
  
  

	/**
	 * returns project current round id
	 * 
	 * @return int
	 */
	function getCurrentRound()
	{
		$round = self::increaseRound();
		return ($round ? $round->id : 0);
	}

	/**
	 * returns project current round code
	 * 
	 * @return int
	 */
	function getCurrentRoundNumber()
	{
		$round = self::increaseRound();
		return ($round ? $round->roundcode : 0);
	}

	/**
	 * method to update and return the project current round
	 * @return object
	 */
	function increaseRound()
	{
		$option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        

        
        if (!$this->_current_round)
		{
			if (!$project = self::getProject()) 
            {
				$this->setError(0, Jtext::_('COM_SPORTSMANAGEMENT_ERROR_PROJECTMODEL_PROJECT_IS_REQUIRED'));
				return false;
			}
			
			$current_date = strftime("%Y-%m-%d %H:%M:%S");
	
			// determine current round according to project settings
			switch ($project->current_round_auto)
			{
				case 0 :	 // manual mode
					$query="SELECT r.id, r.roundcode FROM #__".COM_SPORTSMANAGEMENT_TABLE."_round AS r
							 WHERE r.id =".$project->current_round;
					break;
	
				case 1 :	 // get current round from round_date_first
					$query="SELECT r.id, r.roundcode FROM #__".COM_SPORTSMANAGEMENT_TABLE."_round AS r
							 WHERE r.project_id=".$project->id."
								AND (r.round_date_first - INTERVAL ".($project->auto_time)." MINUTE < '".$current_date."')
							 ORDER BY r.round_date_first DESC LIMIT 1";
					break;
	
				case 2 : // get current round from round_date_last
					$query="SELECT r.id, r.roundcode FROM #__".COM_SPORTSMANAGEMENT_TABLE."_round AS r
							  WHERE r.project_id=".$project->id."
								AND (r.round_date_last + INTERVAL ".($project->auto_time)." MINUTE > '".$current_date."')
							  ORDER BY r.round_date_first ASC LIMIT 1";
					break;
	
				case 3 : // get current round from first game of the round
					$query="SELECT r.id, r.roundcode FROM #__".COM_SPORTSMANAGEMENT_TABLE."_round AS r,#__".COM_SPORTSMANAGEMENT_TABLE."_match AS m
							WHERE r.project_id=".$project->id."
								AND m.round_id=r.id
								AND (m.match_date - INTERVAL ".($project->auto_time)." MINUTE < '".$current_date."')
							ORDER BY m.match_date DESC LIMIT 1";
					break;
	
				case 4 : // get current round from last game of the round
					$query="SELECT r.id, r.roundcode FROM #__".COM_SPORTSMANAGEMENT_TABLE."_round AS r, #__".COM_SPORTSMANAGEMENT_TABLE."_match AS m
							WHERE r.project_id=".$project->id."
								AND m.round_id=r.id
								AND (m.match_date + INTERVAL ".($project->auto_time)." MINUTE > '".$current_date."')
							ORDER BY m.match_date ASC LIMIT 1";
					break;
			}
			$db->setQuery($query);
			$result = $db->loadObject();
				
			// If result is empty, it probably means either this is not started, either this is over, depending on the mode. 
			// Either way, do not change current value
			if (!$result)
			{
				$query = ' SELECT r.id, r.roundcode FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r '
				       . ' WHERE r.project_id = '. $project->current_round
				       ;
				$db->setQuery($query);
				$result = $db->loadObject();
				
				if (!$result)
				{
					if ($project->current_round_auto == 2) {
					    // the current value is invalid... saison is over, just take the last round
					    $query = ' SELECT r.id, r.roundcode FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r '
						    . ' WHERE r.project_id = '. $project->id
						    . ' ORDER BY . r.roundcode DESC '
						    ;
					    $db->setQuery($query);
					    $result = $db->loadObject();					
					} else {
					    // the current value is invalid... just take the first round
					    $query = ' SELECT r.id, r.roundcode FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r '
						    . ' WHERE r.project_id = '. $project->id
						    . ' ORDER BY . r.roundcode ASC '
						    ;
					    $db->setQuery($query);
					    $result = $db->loadObject();
					}
					    
				}
			}
			
			// Update the database if determined current round is different from that in the database
			if ($result && ($project->current_round <> $result->id))
			{
				$query = ' UPDATE #__'.COM_SPORTSMANAGEMENT_TABLE.'_project SET current_round = '.$result->id
				       . ' WHERE id = ' . $db->Quote($project->id);
				$db->setQuery($query);
				if (!$db->query()) {
					JError::raiseWarning(0, JText::_('COM_SPORTSMANAGEMENT_ERROR_CURRENT_ROUND_UPDATE_FAILED'));					
				}
			}
            
            if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' result<br><pre>'.print_r($result,true).'</pre>'),'');
        }
       
            
			$this->_current_round = $result;
		}
        
//        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' _current_round<br><pre>'.print_r($this->_current_round,true).'</pre>'),'');
//        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' result<br><pre>'.print_r($result,true).'</pre>'),'');
//        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' project->id<br><pre>'.print_r($project->id,true).'</pre>'),'');
        
        
		return $this->_current_round;
	}

	function getColors($configcolors='')
	{
		$s=substr($configcolors,0,-1);

		$arr1=array();
		if(trim($s) != "")
		{
			$arr1=explode(";",$s);
		}

		$colors=array();

		$colors[0]["from"]="";
		$colors[0]["to"]="";
		$colors[0]["color"]="";
		$colors[0]["description"]="";

		for($i=0; $i < count($arr1); $i++)
		{
			$arr2=explode(",",$arr1[$i]);
			if(count($arr2) != 4)
			{
				break;
			}

			$colors[$i]["from"]=$arr2[0];
			$colors[$i]["to"]=$arr2[1];
			$colors[$i]["color"]=$arr2[2];
			$colors[$i]["description"]=$arr2[3];
		}
		return $colors;
	}

	function getDivisionsId($divLevel=0)
	{
		$option = JRequest::getCmd('option');
	   $mainframe = JFactory::getApplication();
       // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        // Select some fields
        $query->select('id');
        // From 
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_division');
        // Where
        $query->where('project_id = '.$this->projectid);
        
        if ( $divLevel == 1 )
		{
            $query->where('(parent_id=0 OR parent_id IS NULL)');
		}
		else if ($divLevel==2)
		{
            $query->where('parent_id > 0');
		}
        $query->order('ordering');
		$db->setQuery($query);
		$res = $db->loadResultArray();
		if(count($res) == 0) {
			echo JText::_('COM_SPORTSMANAGEMENT_RANKING_NO_SUBLEVEL_DIVISION_FOUND') . $divLevel;
		}
		return $res;
	}

	/**
	 * return an array of division id and it's subdivision ids
	 * @param int division id
	 * @return int
	 */
	function getDivisionTreeIds($divisionid)
	{
		if ($divisionid == 0) {
			return self::getDivisionsId();
		}
		$divisions = self::getDivisions();
		$res = array($divisionid);
		foreach ($divisions as $d)
		{
			if ($d->parent_id == $divisionid) {
				$res[]=$d->id;
			}
		}
		return $res;
	}

	function getDivision($id)
	{
		$divs = self::getDivisions();
		if ($divs && isset($divs[$id])) {
			return $divs[$id];
		}
		$div = new stdClass();
		$div->id = 0;
		$div->name = '';
		return $div;
	}

	function getDivisions($divLevel=0)
	{
		$option = JRequest::getCmd('option');
	   $mainframe = JFactory::getApplication();
       // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        $project = self::getProject(); 
		if ($project->project_type == 'DIVISIONS_LEAGUE')
		{
			if (empty($this->_divisions))
			{
				// Select some fields
                $query->select('*');
                // From 
		          $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_division');
                // Where
                $query->where('project_id = '.$this->projectid);

				$db->setQuery($query);
				$this->_divisions = $db->loadObjectList('id');
			}
			if ($divLevel)
			{
				$ids = self::getDivisionsId($divLevel);
				$res = array();
				foreach ($this->_divisions as $d)
				{
					if (in_array($d->id,$ids)) {
						$res[] = $d;
					}
				}
				return $res;
			}
			return $this->_divisions;
		}
		return array();
	}

	/**
	 * return project rounds objects ordered by roundcode
	 *
	 * @param string ordering 'ASC or 'DESC'
	 * @return array
	 */
	function getRounds($ordering='ASC')
	{
		$option = JRequest::getCmd('option');
	   $mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        if (empty($this->_rounds))
		{
			// Select some fields
                $query->select('id,round_date_first,round_date_last,CASE LENGTH(name) when 0 then roundcode	else name END as name,roundcode');
                // From 
		          $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round');
                // Where
                $query->where('project_id = '.$this->projectid);
                // order
                $query->order('roundcode ASC');

			$db->setQuery($query);
			$this->_rounds = $db->loadObjectList();
		}
		
        if ( !$this->_rounds )
	    {
		$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
	    }
        
        if ($ordering == 'DESC') 
        {
			return array_reverse($this->_rounds);
		}
		
        return $this->_rounds;
	}

	/**
	 * return project rounds as array of objects(roundid as value,name as text)
	 *
	 * @param string $ordering
	 * @return array
	 */
	function getRoundOptions($ordering='ASC')
	{
		$option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        

        $query="SELECT
					id as value,
				    CASE LENGTH(name)
				    	when 0 then CONCAT('".JText::_('COM_SPORTSMANAGEMENT_MATCHDAY_NAME'). "',' ', id)
				    	else name
				    END as text
				  FROM #__".COM_SPORTSMANAGEMENT_TABLE."_round
				  WHERE project_id=".(int)$this->projectid."
				  ORDER BY roundcode ".$ordering;

		$db->setQuery($query);
		return $db->loadObjectList();
	}

	function getTeaminfo($projectteamid)
	{
		$option = JRequest::getCmd('option');
	   $mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        // Select some fields
        $query->select('t.*,t.id as team_id,t.picture as team_picture,t.extended as teamextended ');
        $query->select('pt.division_id,pt.picture AS projectteam_picture');
        $query->select('c.logo_small,c.logo_middle,c.logo_big');
        $query->select('IF((ISNULL(pt.picture) OR (pt.picture="")),(IF((ISNULL(t.picture) OR (t.picture="")), c.logo_big , t.picture)) , pt.picture) as picture');
        // From 
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st ON st.id = pt.team_id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON st.team_id = t.id ');
        $query->join('INNER',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_club AS c ON t.club_id = c.id  ');
        // Where
        $query->where('pt.id = '. $db->Quote($projectteamid));
        
        
/**
 *         $query=' SELECT t.*, pt.division_id, t.id as team_id,
 * 				pt.picture AS projectteam_picture,
 * 				t.picture as team_picture,
 * 				c.logo_small,
 * 				c.logo_middle,
 * 				c.logo_big,
 * 				IF((ISNULL(pt.picture) OR (pt.picture="")), 
 * 					(IF((ISNULL(t.picture) OR (t.picture="")), c.logo_big , t.picture)) , pt.picture) as picture,
 * 				t.extended as teamextended 
 * 				FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt 
 * 				INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON pt.team_id=t.id
 * 				LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_club AS c ON t.club_id=c.id 
 * 				WHERE pt.id='. $db->Quote($projectteamid);
 */
                
		$db->setQuery($query);
        
        $result = $db->loadObject();
            if ( !$result )
		    {
			$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
		    }
            if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
       {
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' team_id'.'<pre>'.print_r($result,true).'</pre>' ),'');
        }
        
        
        
		return $result;
	}

	function & _getTeams()
	{
	   $option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        

        
		if (empty($this->_teams))
		{
			$query='	SELECT	tl.id AS projectteamid,
								tl.division_id,
								tl.standard_playground,
								tl.admin,
								tl.start_points,
								tl.points_finally,
								tl.neg_points_finally,
								tl.matches_finally,
								tl.won_finally,
								tl.draws_finally,
								tl.lost_finally,
								tl.homegoals_finally,
								tl.guestgoals_finally,
								tl.diffgoals_finally,
								tl.info,
								tl.reason,
								tl.team_id,
								tl.checked_out,
								tl.checked_out_time,
								tl.is_in_score,
								tl.picture AS projectteam_picture,
								t.picture as team_picture,
								IF((ISNULL(tl.picture) OR (tl.picture="")), 
									(IF((ISNULL(t.picture) OR (t.picture="")), c.logo_small , t.picture)) , t.picture) as picture,
								tl.project_id,

								t.id,t.name,
								t.short_name,
								t.middle_name,
								t.notes,
								t.club_id,

								u.username,
								u.email,

								c.email as club_email,
								c.logo_small,
								c.logo_middle,
								c.logo_big,
								c.country,
								c.website,

								d.name AS division_name,
								d.shortname AS division_shortname,
								d.parent_id AS parent_division_id,

								plg.name AS playground_name,
								plg.short_name AS playground_short_name,

								CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\',p.id,p.alias) ELSE p.id END AS project_slug,
								CASE WHEN CHAR_LENGTH(t.alias) THEN CONCAT_WS(\':\',t.id,t.alias) ELSE t.id END AS team_slug,
								CASE WHEN CHAR_LENGTH(d.alias) THEN CONCAT_WS(\':\',d.id,d.alias) ELSE d.id END AS division_slug,
								CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\',c.id,c.alias) ELSE c.id END AS club_slug

						FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team tl
							
                            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id st ON st.id = tl.team_id
                            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team t ON st.team_id = t.id
							LEFT JOIN #__users u ON tl.admin=u.id
							LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_club c ON t.club_id = c.id
							LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_division d ON d.id = tl.division_id
							LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_playground plg ON plg.id = tl.standard_playground
							LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = tl.project_id

						WHERE tl.project_id='.(int)$this->projectid;

			$db->setQuery($query);
			$this->_teams = $db->loadObjectList();
            
            //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' teams<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'');
            //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' teams<br><pre>'.print_r($this->_teams,true).'</pre>'),'');
		}
		return $this->_teams;
	}

	/**
	 * return teams of the project
	 *
	 * @param int $division
	 * @return array
	 */
	function getTeams($division=0)
	{
		$teams = array();
		if ($division != 0)
		{
			$divids = self::getDivisionTreeIds($division);
			foreach ((array)$this->_getTeams() as $t)
			{
				if (in_array($t->division_id,$divids))
				{
					$teams[]=$t;
				}
			}
		}
		else
		{
			$teams = self::_getTeams();
		}

		return $teams;
	}

	/**
	 * return array of team ids
	 *
	 * @return array	 *
	 */
	function getTeamIds($division=0)
	{
		$teams=array();
		foreach ((array)$this->_getTeams() as $t)
		{
			if (!$division || $t->division_id == $division) {
				$teams[]=$t->id;
			}
		}
		return $teams;
	}

	function getTeamsIndexedById($division=0)
	{
		$result=$this->getTeams($division);
		$teams=array();
		if (count($result))
		{
			foreach($result as $r)
			{
				$teams[$r->id]=$r;
			}
		}

		return $teams;
	}

	function getTeamsIndexedByPtid($division=0)
	{
		$result=self::getTeams($division);
		$teams=array();

		if (count($result))
		{
			foreach($result as $r)
			{
				$teams[$r->projectteamid]=$r;
			}
		}
		return $teams;
	}

	function getFavTeams()
	{
		$project = self::getProject();
		if(!is_null($project))
		return explode(",",$project->fav_team);
		else
		return array();
	}

	function getEventTypes($evid=0)
	{
	   $option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        

		$query="SELECT	et.id AS etid,
							me.event_type_id AS id,
							et.*
							FROM #__".COM_SPORTSMANAGEMENT_TABLE."_eventtype AS et
							LEFT JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_match_event AS me ON et.id=me.event_type_id";
		if ($evid != 0)
		{
			if ($this->projectid > 0)
			{
				$query .= " AND";
			}
			else
			{
				$query .= " WHERE";
			}
			$query .= " me.event_type_id=".(int)$evid;
		}

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList('etid');
	}

	function getprojectteamID($teamid)
	{
	   $option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        

		$query="SELECT id
				  FROM #__".COM_SPORTSMANAGEMENT_TABLE."_project_team
				  WHERE team_id=".(int)$teamid."
					AND project_id=".(int)$this->projectid;

		$this->_db->setQuery($query);
		$result=$this->_db->loadResult();

		return $result;
	}

	/**
	 * Method to return a playgrounds array (id,name)
	 *
	 * @access  public
	 * @return  array
	 * @since 0.1
	 */
	function getPlaygrounds()
	{
	   $option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        

		$query='	SELECT	id AS value,
							name AS text
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_playground
					ORDER BY text ASC ';
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		else
		{
			return $result;
		}
	}
    
    /**
	 * 
	 * @param $project_id
	 */
	function getProjectGameRegularTime($project_id)
	{
	   $option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        

		$gameprojecttime = 0;
        $query = 'SELECT game_regular_time 
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project 
					WHERE id='.$project_id;
		$this->_db->setQuery($query);
		$result = $this->_db->loadObject();
		
        $gameprojecttime += $result->game_regular_time;
        if ( $result->allow_add_time )
        {
            $gameprojecttime += $result->add_time;
        }
        
        return $gameprojecttime;
	}

	function getReferees()
	{
	   $option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
		$project=$this->getProject();
		if ($project->teams_as_referees)
		{
			$query='	SELECT	id AS value,
								name AS text
						FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team
						ORDER BY name';

			$this->_db->setQuery($query);
			$refs=$this->_db->loadObjectList();
		}
		else
		{
			$query='	SELECT	id AS value,
								firstname,
								lastname

						FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee
						ORDER BY lastname';

			$this->_db->setQuery($query);
			$refs=$this->_db->loadObjectList();
			foreach($refs as $ref)
			{
				$ref->text=$ref->lastname.",".$ref->firstname;
			}
		}
		return $refs;
	}

	function getTemplateConfig($template)
	{
		$option = JRequest::getCmd('option');
        $mainframe	= JFactory::getApplication();
        $this->projectid = JRequest::getInt('p',0);
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query2 = $db->getQuery(true);
        
        //first load the default settings from the default <template>.xml file
		$paramsdata="";
		$arrStandardSettings=array();
        
        $xmlfile = JPATH_COMPONENT_SITE.DS.'settings'.DS.'default'.DS.$template.'.xml';
        
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' projectid<br><pre>'.print_r($this->projectid,true).'</pre>'),'');
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' xmlfile<br><pre>'.print_r($xmlfile,true).'</pre>'),'');
        
        /*
		if(file_exists(JPATH_COMPONENT_SITE. DS.'settings'.DS."default".DS.$template.'.xml')) 
        {
			$strXmlFile = JPATH_COMPONENT_SITE. DS.'settings'.DS."default".DS.$template.'.xml';
			$form = JForm::getInstance($template, $strXmlFile);
			$fieldsets = $form->getFieldsets();
			foreach ($fieldsets as $fieldset) {
				foreach($form->getFieldset($fieldset->name) as $field) {
					$arrStandardSettings[$field->name]=$field->value;
				}				
			}
		}
        */
        
/*
		//second load the default settings from the default extensions <template>.xml file
		$extensions=sportsmanagementHelper::getExtensions(JRequest::getInt('p'));
		foreach ($extensions as $e => $extension) {
			$JLGPATH_EXTENSION= JPATH_COMPONENT_SITE.DS.'extensions'.DS.$extension;
			$paramsdata="";
			$strXmlFile=$JLGPATH_EXTENSION. DS.'settings'.DS."default".DS.$template.'.xml';
			if(file_exists($JLGPATH_EXTENSION. DS.'settings'.DS."default".DS.$template.'.xml')) {
				$form = JForm::getInstance($template, $strXmlFile);
				$fieldsets = $form->getFieldsets();
				foreach ($fieldsets as $fieldset) {
					foreach($form->getFieldset($fieldset->name) as $field) {
						$arrStandardSettings[$field->name]=$field->value;
					}
				}
			}
		}
*/
		if( $this->projectid == 0) return $arrStandardSettings;

$query->select('t.params');
$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_template_config AS t');
$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id=t.project_id');
$query->where('t.template='.$db->Quote($template));
$query->where('p.id='.$db->Quote($this->projectid));

//        $query= "SELECT t.params
//				   FROM #__".COM_SPORTSMANAGEMENT_TABLE."_template_config AS t
//				   INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_project AS p ON p.id=t.project_id
//				   WHERE t.template=".$db->Quote($template)."
//				   AND p.id=".$db->Quote($this->projectid);

		$db->setQuery($query);
		if (! $result = $db->loadResult())
		{
			$project = self::getProject();
			if (!empty($project) && $project->master_template>0)
			{
				$query2->select('t.params');
                $query2->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_template_config AS t');
                $query2->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id=t.project_id');
                $query2->where('t.template='.$db->Quote($template));
                $query2->where('p.id='.$db->Quote($project->master_template));
                
//                $query="SELECT t.params
//						  FROM #__".COM_SPORTSMANAGEMENT_TABLE."_template_config AS t
//						  INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_project AS p ON p.id=t.project_id
//						  WHERE t.template=".$db->Quote($template)."
//						  AND p.id=".$db->Quote($project->master_template);

				$db->setQuery($query2);
				if (! $result = $db->loadResult())
				{
					JError::raiseNotice(500,JText::_('COM_SPORTSMANAGEMENT_MASTER_TEMPLATE_MISSING')." ".$template);
					JError::raiseNotice(500,JText::_('COM_SPORTSMANAGEMENT_MASTER_TEMPLATE_MISSING_PID'). $project->master_template);
					JError::raiseNotice(500,JText::_('COM_SPORTSMANAGEMENT_TEMPLATE_MISSING_HINT'));
					return $arrStandardSettings;
				}
			}
			else
			{
				//JError::raiseNotice(500,'project ' . $this->projectid . '  setting not found');
				//there are no saved settings found, use the standard xml file default values
				return $arrStandardSettings;
			}
		}
		
        $jRegistry = new JRegistry;
		//$jRegistry->loadString($result, 'ini');
        $jRegistry->loadJSON($result);
        
/*        
        $extended = JForm::getInstance('extended', $xmlfile,
				array('control'=> 'extended'),
				false, '/config');
		$extended->bind($jRegistry);
*/        
        
		$configvalues = $jRegistry->toArray(); 

		//merge and overwrite standard settings with individual view settings
		$settings = array_merge($arrStandardSettings,$configvalues);
        return $settings;
		
		//return $extended;
	}

	function getOverallConfig()
	{
		return self::getTemplateConfig('overall');
	}

	function getMapConfig()
	{
		return self::getTemplateConfig('map');
	}

  	/**
   	* @author diddipoeler 
   	* @since  2011-11-12
   	* @return country from project-league
   	*/
   	function getProjectCountry()
	{
	   $option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        

		 $query = 'SELECT l.country
					from #__'.COM_SPORTSMANAGEMENT_TABLE.'_league as l
					inner join #__'.COM_SPORTSMANAGEMENT_TABLE.'_project as pro
					on pro.league_id = l.id 
					WHERE pro.id = '. $this->_db->Quote($this->projectid);
		  $this->_db->setQuery( $query );
		  $this->country = $this->_db->loadResult();
		  return $this->country;
  	} 
        
	/**
	 * return events assigned to the project
	 * @param int position_id if specified,returns only events assigned to this position
	 * @return array
	 */
	function getProjectEvents($position_id=0)
	{
	   $option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        

        
		$query=' SELECT	et.id,
						et.name,
						et.icon
						FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype AS et
						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position_eventtype AS pet ON pet.eventtype_id=et.id
						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.position_id=pet.position_id
						WHERE ppos.project_id='.$db->Quote($this->projectid);
		if ($position_id)
		{
			$query=' AND ppos.position_id='. $db->Quote($position_id);
		}
		$query .= ' GROUP BY et.id';
		$db->setQuery($query);
		$events = $db->loadObjectList('id');
		return $events;
	}

	/**
	 * returns stats assigned to positions assigned to project
	 * @param int statid 0 for all stats
	 * @param int positionid 0 for all positions
	 * @return array objects
	 */
	function getProjectStats($statid=0,$positionid=0)
	{
	  $option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        

        
		if (empty($this->_stats))
		{
			require_once (JPATH_COMPONENT_ADMINISTRATOR .DS.'statistics'.DS.'base.php');
			$project = self::getProject();
			$project_id = $project->id;
			$query='	SELECT	stat.id,
								stat.name,
								stat.short,
								stat.class,
								stat.icon,
								stat.calculated,
								ppos.id as pposid,
								ppos.position_id AS position_id,
								stat.params, stat.baseparams
						FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_statistic AS stat
						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position_statistic AS ps ON ps.statistic_id=stat.id
						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.position_id=ps.position_id
						  AND ppos.project_id='.$project_id.' 
						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id=ps.position_id
						WHERE stat.published=1 
						  AND pos.published =1 
						  ';
			$query .= ' ORDER BY pos.ordering,ps.ordering ';
			$db->setQuery($query);
			$this->_stats = $db->loadObjectList();

		}
		// sort into positions
		$positions = self::getProjectPositions();
		$stats = array();
		// init
		foreach ($positions as $pos)
		{
			$stats[$pos->id]=array();
		}
		if (count($this->_stats) > 0)
		{
			foreach ($this->_stats as $k => $row)
			{
				if (!$statid || $statid == $row->id || (is_array($statid) && in_array($row->id, $statid)))
				{
					$stat=&SMStatistic::getInstance($row->class);
					$stat->bind($row);
					$stat->set('position_id',$row->position_id);
					$stats[$row->position_id][$row->id]=$stat;
				}
			}
			if ($positionid)
			{
				return (isset($stats[$positionid]) ? $stats[$positionid] : array());
			}
			else
			{
				return $stats;
			}
		}
		else
		{
			return $stats;
		}
	}

	function getProjectPositions()
	{
	   $option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        

        
		if (empty($this->_positions))
		{
			$query='	SELECT	pos.id,
								pos.persontype,
								pos.name,
								pos.ordering,
								pos.published,
								ppos.id AS pposid
						FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos
						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON ppos.position_id=pos.id
						WHERE ppos.project_id='.$db->Quote($this->projectid);
			$db->setQuery($query);
			$this->_positions = $db->loadObjectList('id');
		}
		return $this->_positions;
	}

	function getClubIconHtml(&$team,$type=1,$with_space=0)
	{
		$small_club_icon = $team->logo_small;
		if ($type==1)
		{
			$params=array();
			$params['align']="top";
			$params['border']=0;
			$params['width']=21;
			if ($with_space==1)
			{
				$params['style']='padding:1px;';
			}
			if ($small_club_icon=='')
			{
				$small_club_icon = sportsmanagementHelper::getDefaultPlaceholder("clublogosmall");
			}

			return JHtml::image($small_club_icon,'',$params);
		}
		elseif (($type==2) && (isset($team->country)))
		{
			return JSMCountries::getCountryFlag($team->country);
		}
	}

	/**
	 * Method to store the item
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function store($data,$table='')
	{
		if ($table=='')
		{
			$row =& $this->getTable();
		}
		else
		{
			$row =& JTable::getInstance($table,'Table');
		}

		// Bind the form fields to the items table
		if (!$row->bind($data))
		{
			$this->setError(JText::_('Binding failed'));
			return false;
		}

		// Create the timestamp for the date
		$row->checked_out_time=gmdate('Y-m-d H:i:s');

		// if new item,order last,but only if an ordering exist
		if ((isset($row->id)) && (isset($row->ordering)))
		{
			if (!$row->id && $row->ordering != NULL)
			{
				$row->ordering=$row->getNextOrder();
			}
		}

		// Make sure the item is valid
		if (!$row->check())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Store the item to the database
		if (!$row->store())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return $row->id;
	}

	public static function isUserProjectAdminOrEditor($userId=0, $project)
	{
		$result=false;
		if($userId > 0)
		{
			$result= ($userId==$project->admin || $userId==$project->editor);
		}
		return $result;
	}

	/**
	 * returns match substitutions
	 * @param int match id
	 * @return array
	 */
	function getMatchSubstitutions($match_id)
	{
	  $option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' match_id'.'<pre>'.print_r($match_id,true).'</pre>' ),'');  
        
        // Select some fields
        $query->select('mp.in_out_time,mp.teamplayer_id,mp.in_for');
        $query->select('pt.team_id,pt.id AS ptid');
        $query->select('tp1.person_id,tp1.jerseynumber');
        $query->select('tp2.person_id AS out_person_id');
        $query->select('p2.id AS out_ptid,p2.firstname AS out_firstname,p2.nickname AS out_nickname,p2.lastname AS out_lastname');
        $query->select('p.firstname,p.nickname,p.lastname');
        $query->select('pos.name AS in_position');
        $query->select('ppos.id AS pposid1');
        $query->select('pos2.name AS out_position');
        $query->select('ppos2.id AS pposid2');
        $query->select('CASE WHEN CHAR_LENGTH(t.alias) THEN CONCAT_WS(\':\',t.id,t.alias) ELSE t.id END AS team_slug');
        $query->select('CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\',p.id,p.alias) ELSE p.id END AS person_slug');
        
        // From 
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp1 ON tp1.id = mp.teamplayer_id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st1 ON st1.team_id = tp1.team_id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st1.id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id = st1.team_id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p ON tp1.person_id = p.id');
        
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp2 ON tp2.id = mp.in_for');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p2 ON tp2.person_id = p2.id');
        
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id = mp.project_position_id');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON ppos.position_id = pos.id');
        
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp2 ON mp.match_id = mp2.match_id AND mp.in_for = mp2.teamplayer_id');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos2 ON ppos2.id = mp2.project_position_id');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos2 ON ppos2.position_id = pos2.id');
   
        // Where
        $query->where('mp.match_id = '.$match_id);
        $query->where('mp.came_in > 0');
        $query->where('p.published = 1');
        $query->where('p2.published = 1');
        // group
        $query->group('mp.in_out_time, mp.teamplayer_id, pt.team_id');
        // order
        $query->order('(mp.in_out_time+0)');                
                    
		$db->setQuery($query);
		//echo($this->_db->getQuery());
		$result = $db->loadObjectList();
 
        if ( !$result )
	    {
	       if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
       {
		$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
        }
	    }
        
		return $result;
	}

	/**
	 * returns match events
	 * @param int match id
	 * @return array
	 */
	function getMatchEvents($match_id,$showcomments=0,$sortdesc=0)
	{
		$option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        if ($showcomments == 1) 
        {
		    $join = 'LEFT';
		    //$addline = ' me.notes,';
            $query->select('me.notes');
		} 
        else 
        {
		    $join = 'INNER';
		    //$addline = '';
		}
		$esort = '';
        $arrayobjectsort = '1';
		if ($sortdesc == 1) 
        {
		    $esort = ' DESC';
            $arrayobjectsort = '-1';
		}
        // Select some fields
        $query->select('me.event_type_id,me.id as event_id,me.event_time,me.notice,me.projectteam_id AS ptid,me.event_sum');
        $query->select('pt.team_id AS team_id');
        $query->select('et.name AS eventtype_name');
        $query->select('t.name AS team_name');
        $query->select('tp.picture AS tppicture1');
        $query->select('p.id AS playerid,p.firstname AS firstname1,p.nickname AS nickname1,p.lastname AS lastname1,p.picture AS picture1');
        // From 
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event AS me');
        $query->join($join,'#__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype AS et ON me.event_type_id = et.id');
        $query->join($join,'#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON me.projectteam_id = pt.id');
        $query->join($join,'#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.id = pt.team_id');
        $query->join($join,'#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON st.team_id = t.id');
        $query->join($join,'#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ON tp.team_id = st.team_id AND tp.id = me.teamplayer_id');
        $query->join($join,'#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p ON tp.person_id = p.id');

		// Where
        $query->where('me.match_id = '.$match_id );
        $query->where('p.published = 1');
        // order
        $query->order('(me.event_time + 0)'. $esort .', me.event_type_id, me.id');
        	
		$db->setQuery( $query );
		
        $events = $db->loadObjectList();
        
        if ( !$events )
	    {
	       if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
       {
		$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
        }
	    }
        
    $query = $db->getQuery(true);    
    $query->clear();
    // Select some fields
        $query->select('*');
        // From 
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_commentary');
        // Where
        $query->where('match_id = '. (int)$this->matchid );
    
    $db->setQuery($query);
		$commentary = $db->loadObjectList();
        if ( $commentary )
        {
            foreach ( $commentary as $comment )
            {
                $temp = new stdClass();
                $temp->event_type_id = 0;
                $temp->event_sum = $comment->type;
                $temp->event_time = $comment->event_time;
                $temp->notes = $comment->notes;
                $events[] = $temp;
            }
        }
        $events = JArrayHelper::sortObjects($events,'event_time',$arrayobjectsort);
        return $events;
	}
	
	function hasEditPermission($task=null)
	{
		$option = JRequest::getCmd('option');
        $allowed = false;
		$user = JFactory::getUser();
		if($user->id > 0) {
			if(!is_null($task)) {
				if (!$user->authorise($task, $option)) {
					$allowed = false;
					error_log("no ACL permission for task " . $task);
				} else {
					$allowed = true;
				}
			}
			//if no ACL permission, check for overruling permission by project admin/editor (compatibility < 2.5)
			if(!$allowed) {
				// If not, then check if user is project admin or editor
				$project = self::getProject();
				if($this->isUserProjectAdminOrEditor($user->id, $project))
				{
					$allowed = true;
				} else {
					error_log("no isUserProjectAdminOrEditor");
				}
			}
		}
		return $allowed;
	}
}
?>