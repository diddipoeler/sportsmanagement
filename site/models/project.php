<?php 
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k�nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
* ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n�tzlich sein wird, aber
* OHNE JEDE GEW�HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f�r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
jimport( 'joomla.utilities.arrayhelper' );

/**
 * sportsmanagementModelProject
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelProject extends JModelLegacy
{
	static $_project = null;
	static $projectid = 0;
    static $matchid = 0;
    static $_round_from;
    static $_round_to;

	/**
	 * project league country
	 * @var string
	 */
	var $country = null;
	/**
	 * data array for teams
	 * @var array
	 */
	static $_teams = null;

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
	static $_stats = null;

	/**
	 * data project positions
	 * @var array
	 */
	static $_positions = null;

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
	static $_current_round;
    
    static $seasonid = 0;


	/**
	 * sportsmanagementModelProject::__construct()
	 * 
	 * @return void
	 */
	function __construct()
	{
		$mainframe = JFactory::getApplication();
        
        self::$projectid = JRequest::getInt('p',0);
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projectid<br><pre>'.print_r(self::$projectid,true).'</pre>'),'');
        
		parent::__construct();
	}


	/**
	 * sportsmanagementModelProject::getProject()
	 * 
	 * @return
	 */
	function getProject()
	{
		$option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $starttime = microtime(); 

      // $this->projectid = JRequest::getInt('p',0);
    
//    $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projectid<br><pre>'.print_r(self::$projectid,true).'</pre>'),'');
//    $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _current_round<br><pre>'.print_r(self::$_current_round,true).'</pre>'),'');
    
    if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
    $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projectid<br><pre>'.print_r(self::$projectid,true).'</pre>'),'');
    }
    
        if (is_null(self::$_project) && self::$projectid > 0)
		{
			//fs_sport_type_name = sport_type folder name
            $query->select('p.*, l.country, st.id AS sport_type_id, st.name AS sport_type_name');
            $query->select('st.icon AS sport_type_picture, l.picture as leaguepicture');
            $query->select('LOWER(SUBSTR(st.name, CHAR_LENGTH( "COM_SPORTSMANAGEMENT_ST_")+1)) AS fs_sport_type_name');
            $query->select('CONCAT_WS( \':\', p.id, p.alias ) AS slug');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type AS st ON p.sports_type_id = st.id ');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_league AS l ON p.league_id = l.id ');
            $query->where('p.id ='. $db->Quote(self::$projectid));
            

			$db->setQuery($query,0,1);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projectid<br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        

    
			self::$_project = $db->loadObject();
            self::$seasonid = self::$_project->season_id;
            
            if ( !self::$_project && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' seasonid<br><pre>'.print_r(self::$seasonid,true).'</pre>'),'Error');
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
            
            
		}
		return self::$_project;
	}

	/**
	 * sportsmanagementModelProject::setProjectID()
	 * 
	 * @param integer $id
	 * @return void
	 */
	function setProjectID($id=0)
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        
		self::$projectid = $id;
		self::$_project = null;
        self::$_current_round = 0;
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projectid<br><pre>'.print_r(self::$projectid,true).'</pre>'),'');
        }
        
	}

	/**
	 * sportsmanagementModelProject::getSportsType()
	 * 
	 * @return
	 */
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
	function getCurrentRound($view=NULL)
	{
	   $mainframe = JFactory::getApplication();
		$round = self::increaseRound();
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' view: '.$view.' round<br><pre>'.print_r($round,true).'</pre>'),'');
        $this->_current_round = $round;
        
        switch ($view)
        {
            case 'result':
            sportsmanagementModelResults::$roundid = $this->_current_round;
            break;
        }
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' view: '.$view.' _current_round<br><pre>'.print_r($this->_current_round,true).'</pre>'),'');
        
		return ($round ? $round->id : 0);
	}

	/**
	 * returns project current round code
	 * 
	 * @return int
	 */
	function getCurrentRoundNumber()
	{
	   $mainframe = JFactory::getApplication();
		$round = self::increaseRound();
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' round<br><pre>'.print_r($round,true).'</pre>'),'');
        
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

        if (!self::$_current_round)
		{
			if (!$project = self::getProject()) 
            {
				$this->setError(0, Jtext::_('COM_SPORTSMANAGEMENT_ERROR_PROJECTMODEL_PROJECT_IS_REQUIRED'));
				return false;
			}
			
			$current_date = strftime("%Y-%m-%d %H:%M:%S");
            $query->clear();
            $query->select('r.id, r.roundcode');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r ');
            
	
			// determine current round according to project settings
			switch ($project->current_round_auto)
			{
				case 0 :	 // manual mode
                $query->where('r.id = '.$project->current_round);
					break;
	
				case 1 :	 // get current round from round_date_first
                $query->where('r.project_id = '.$project->id);
                $query->where("(r.round_date_first - INTERVAL ".($project->auto_time)." MINUTE < '".$current_date."')");
                $query->order('r.round_date_first DESC LIMIT 1');
					break;
	
				case 2 : // get current round from round_date_last
                $query->where('r.project_id = '.$project->id);
                $query->where("(r.round_date_last - INTERVAL ".($project->auto_time)." MINUTE < '".$current_date."')");
                $query->order('r.round_date_first DESC LIMIT 1');
					break;
	
				case 3 : // get current round from first game of the round
                $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m ON m.round_id = r.id');
                $query->where('r.project_id = '.$project->id);
                $query->where("(m.match_date - INTERVAL ".($project->auto_time)." MINUTE < '".$current_date."')");
                $query->order('m.match_date DESC LIMIT 1');
					break;
	
				case 4 : // get current round from last game of the round
                $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m ON m.round_id = r.id');
                $query->where('r.project_id = '.$project->id);
                $query->where("(m.match_date + INTERVAL ".($project->auto_time)." MINUTE < '".$current_date."')");
                $query->order('m.match_date ASC LIMIT 1');
					break;
			}
            
//            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' round<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            
			$db->setQuery($query);
			$result = $db->loadObject();
				
			// If result is empty, it probably means either this is not started, either this is over, depending on the mode. 
			// Either way, do not change current value
			if (!$result)
			{
			 
//            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
//            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
             
			$query->clear();
            $query->select('r.id, r.roundcode');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r ');
            $query->where('r.id = '.$project->current_round);

				$db->setQuery($query);
				$result = $db->loadObject();
				
				if (!$result)
				{
				    $query->clear();
                    $query->select('r.id, r.roundcode');
                    $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r ');
                    $query->where('r.project_id = '. $project->id);
                        
					if ($project->current_round_auto == 2) 
                    {
					    // the current value is invalid... saison is over, just take the last round
                        $query->order('r.roundcode DESC');

					    $db->setQuery($query);
					    $result = $db->loadObject();					
					} 
                    else 
                    {
					    // the current value is invalid... just take the first round
                        $query->order('r.roundcode ASC');

					    $db->setQuery($query);
					    $result = $db->loadObject();
					}
					    
				}
			}
			
			// Update the database if determined current round is different from that in the database
//            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result<br><pre>'.print_r($result,true).'</pre>'),'');
//            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' current_round<br><pre>'.print_r($project->current_round,true).'</pre>'),'');
//            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' id<br><pre>'.print_r($result->id,true).'</pre>'),'');
            
			if ($result && ($project->current_round <> $result->id))
			{
			 // Must be a valid primary key value.
             $object = new stdClass();
             $object->id = $project->id;
             $object->current_round = $result->id;
             // Update their details in the users table using id as the primary key.
             $resultupdate = JFactory::getDbo()->updateObject('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project', $object, 'id');

				if (!$resultupdate) 
                {
//                    $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' resultupdate<br><pre>'.print_r($resultupdate,true).'</pre>'),'Error');
//                    $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' object<br><pre>'.print_r($object,true).'</pre>'),'Error');
                    JError::raiseWarning(500, JText::_('COM_SPORTSMANAGEMENT_ERROR_CURRENT_ROUND_UPDATE_FAILED'));
				}
                else
                {
//                    $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' resultupdate<br><pre>'.print_r($resultupdate,true).'</pre>'),'');
//                    $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' object<br><pre>'.print_r($object,true).'</pre>'),'');
                }
			}
            
            if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result<br><pre>'.print_r($result,true).'</pre>'),'');
        }
       
            
			self::$_current_round = $result;
		}
        
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _current_round<br><pre>'.print_r($this->_current_round,true).'</pre>'),'');
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result<br><pre>'.print_r($result,true).'</pre>'),'');
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project->id<br><pre>'.print_r($project->id,true).'</pre>'),'');
        
        
		return self::$_current_round;
	}

	/**
	 * sportsmanagementModelProject::getColors()
	 * 
	 * @param string $configcolors
	 * @return
	 */
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

	/**
	 * sportsmanagementModelProject::getDivisionsId()
	 * 
	 * @param integer $divLevel
	 * @return
	 */
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
        $query->where('project_id = '.self::$projectid);
        
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

	/**
	 * sportsmanagementModelProject::getDivision()
	 * 
	 * @param mixed $id
	 * @return
	 */
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

	/**
	 * sportsmanagementModelProject::getDivisions()
	 * 
	 * @param integer $divLevel
	 * @return
	 */
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
                $query->where('project_id = '.self::$projectid);

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
        $starttime = microtime(); 
        
        if (empty($this->_rounds))
		{
			// Select some fields
                $query->select('id,round_date_first,round_date_last,CASE LENGTH(name) when 0 then roundcode	else name END as name,roundcode');
                // From 
		          $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round');
                // Where
                $query->where('project_id = '.self::$projectid);
                // order
                $query->order('roundcode ASC');

			$db->setQuery($query);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
			$this->_rounds = $db->loadObjectList();
		}
		
        if ( !$this->_rounds && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
	    {
	    $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');   
		$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projectid'.'<pre>'.print_r(self::$projectid,true).'</pre>' ),'Error');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query'.'<pre>'.print_r($query->dump(),true).'</pre>' ),'Error');
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
        $starttime = microtime(); 
        
        // Select some fields
        $query->select("id as value, CASE LENGTH(name) when 0 then CONCAT('".JText::_('COM_SPORTSMANAGEMENT_MATCHDAY_NAME'). "',' ', id) else name END as text");
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round ');
        $query->where('project_id = '.(int)self::$projectid);
        $query->order('roundcode '.$ordering);

		$db->setQuery($query);
        $result = $db->loadObjectList();
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
        if ( !$result && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
	    {
	    $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');   
		$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projectid'.'<pre>'.print_r(self::$projectid,true).'</pre>' ),'Error');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query'.'<pre>'.print_r($query->dump(),true).'</pre>' ),'Error');
	    }
        
		return $result;
	}

	/**
	 * sportsmanagementModelProject::getTeaminfo()
	 * 
	 * @param mixed $projectteamid
	 * @return
	 */
	function getTeaminfo($projectteamid)
	{
		$option = JRequest::getCmd('option');
	   $mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $starttime = microtime(); 
        
        // Select some fields
        $query->select('t.*,t.id as team_id,t.picture as team_picture,t.extended as teamextended ');
        $query->select('pt.division_id,pt.picture AS projectteam_picture');
        $query->select('c.logo_small,c.logo_middle,c.logo_big');
        $query->select('IF((ISNULL(pt.picture) OR (pt.picture="")),(IF((ISNULL(t.picture) OR (t.picture="")), c.logo_big , t.picture)) , pt.picture) as picture');
        // From 
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id as st ON st.id = pt.team_id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON st.team_id = t.id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_club AS c ON t.club_id = c.id  ');
        // Where
        $query->where('pt.id = '. $db->Quote($projectteamid));
         
		$db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
        $result = $db->loadObject();
        
            if ( !$result && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
		    {
			$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
		    }
            
            if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
       {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' team_id'.'<pre>'.print_r($result,true).'</pre>' ),'');
        }
        
        
        
		return $result;
	}

	
	/**
	 * sportsmanagementModelProject::_getTeams()
	 * 
	 * @param string $teamname
	 * @return
	 */
	function & _getTeams($teamname='name')
	{
	   $option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $starttime = microtime(); 

		if (empty(self::$_teams))
		{
		  // Select some fields
          $query->select('tl.id AS projectteamid,tl.division_id,tl.standard_playground,tl.admin,tl.start_points,tl.points_finally,tl.neg_points_finally,tl.matches_finally,tl.won_finally,tl.draws_finally,tl.lost_finally');
          $query->select('tl.homegoals_finally,tl.guestgoals_finally,tl.diffgoals_finally,tl.info,tl.reason,tl.team_id as project_team_team_id,tl.checked_out,tl.checked_out_time,tl.is_in_score,tl.picture AS projectteam_picture');
          $query->select('IF((ISNULL(tl.picture) OR (tl.picture="")),(IF((ISNULL(t.picture) OR (t.picture="")), c.logo_small , t.picture)) , t.picture) as picture,tl.project_id');
          $query->select('t.picture as team_picture,t.id,t.name,t.short_name,t.middle_name,t.notes,t.club_id');
          $query->select('u.username,u.email');
          $query->select('st.team_id');
          $query->select('c.email as club_email,c.logo_small,c.logo_middle,c.logo_big,c.country,c.website');
          $query->select('d.name AS division_name,d.shortname AS division_shortname,d.parent_id AS parent_division_id');
          $query->select('plg.name AS playground_name,plg.short_name AS playground_short_name');
          $query->select('CONCAT_WS(\':\',p.id,p.alias) AS project_slug');
          $query->select('CONCAT_WS(\':\',t.id,t.alias) AS team_slug');
          $query->select('CONCAT_WS(\':\',d.id,d.alias) AS division_slug');
          $query->select('CONCAT_WS(\':\',c.id,c.alias) AS club_slug');
          
          // f�r die anzeige der teams im frontend
          $query->select('t.name as team_name,t.short_name,t.middle_name,t.club_id,t.website AS team_www,t.picture team_picture,c.name as club_name,c.address as club_address');
          $query->select('c.zipcode as club_zipcode,c.state as club_state,c.location as club_location,c.email as club_email,c.unique_id,c.country as club_country,c.website AS club_www');
          
          
          $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS tl ');
          $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id st ON st.id = tl.team_id ');
          $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_team t ON st.team_id = t.id ');
          $query->join('LEFT',' #__users u ON tl.admin=u.id ');
          $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_club c ON t.club_id = c.id ');
          $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_division d ON d.id = tl.division_id ');
          $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_playground plg ON plg.id = tl.standard_playground ');
          $query->join('LEFT',' #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id = tl.project_id ');
          $query->where('tl.project_id = '.(int)self::$projectid);

			$db->setQuery($query);
            
            
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
            
			self::$_teams = $db->loadObjectList();
            
            //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' teams<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'');
            //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' teams<br><pre>'.print_r($this->_teams,true).'</pre>'),'');
		}
		return self::$_teams;
	}

	
	/**
	 * sportsmanagementModelProject::getTeams()
	 * 
	 * @param integer $division
	 * @param string $teamname
	 * @return
	 */
	function getTeams($division=0,$teamname='name')
	{
		$teams = array();
		if ($division != 0)
		{
			$divids = self::getDivisionTreeIds($division);
			foreach ((array)self::_getTeams() as $t)
			{
				if (in_array($t->division_id,$divids))
				{
					$teams[]=$t;
				}
			}
		}
		else
		{
			$teams = self::_getTeams($teamname);
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
		$teams = array();
		foreach ((array)self::_getTeams() as $t)
		{
			if (!$division || $t->division_id == $division) {
				$teams[] = $t->id;
			}
		}
		return $teams;
	}

	
	/**
	 * sportsmanagementModelProject::getTeamsIndexedById()
	 * 
	 * @param integer $division
	 * @param string $teamname
	 * @return
	 */
	function getTeamsIndexedById($division=0,$teamname='name')
	{
		$result = self::getTeams($division,$teamname);
		$teams = array();
		if (count($result))
		{
			foreach($result as $r)
			{
				$teams[$r->id] = $r;
			}
		}

		return $teams;
	}

	
	/**
	 * sportsmanagementModelProject::getTeamsIndexedByPtid()
	 * 
	 * @param integer $division
	 * @param string $teamname
	 * @return
	 */
	function getTeamsIndexedByPtid($division=0,$teamname='name')
	{
		$result = self::getTeams($division,$teamname);
		$teams = array();

		if (count($result))
		{
			foreach($result as $r)
			{
				$teams[$r->projectteamid] = $r;
			}
		}
		return $teams;
	}

	/**
	 * sportsmanagementModelProject::getFavTeams()
	 * 
	 * @return
	 */
	function getFavTeams()
	{
		$project = self::getProject();
		if(!is_null($project))
		return explode(",",$project->fav_team);
		else
		return array();
	}

	/**
	 * sportsmanagementModelProject::getEventTypes()
	 * 
	 * @param integer $evid
	 * @return
	 */
	function getEventTypes($evid=0)
	{
	   $option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        $query->select('et.id AS etid,et.id AS etid,');
        $query->select('me.event_type_id AS id');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype AS et');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event AS me ON et.id = me.event_type_id');

//		$query="SELECT	et.id AS etid,
//							me.event_type_id AS id,
//							et.id AS etid,
//							FROM #__".COM_SPORTSMANAGEMENT_TABLE."_eventtype AS et
//							LEFT JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_match_event AS me ON et.id=me.event_type_id";
		if ($evid != 0)
		{
//			if ($this->projectid > 0)
//			{
//				$query .= " AND";
//			}
//			else
//			{
//				$query .= " WHERE";
//			}
//			$query .= " me.event_type_id=".(int)$evid;
            $query->where('me.event_type_id = '.(int)$evid);
		}

		$db->setQuery($query);
		return $db->loadObjectList('etid');
	}



	/**
	 * sportsmanagementModelProject::getprojectteamID()
	 * 
	 * @param mixed $teamid
	 * @return
	 */
	function getprojectteamID($teamid)
	{
	   $option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        $query->select('id');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team');
        $query->where('team_id = '.(int)$teamid);
        $query->where('project_id = '.(int)self::$projectid);
		$db->setQuery($query);
		$result = $db->loadResult();

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
        $query->select('id AS value,name AS text');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_playground');
        $query->order('text ASC');

		$db->setQuery($query);
		if (!$result = $db->loadObjectList())
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
		else
		{
			return $result;
		}
	}
    
    
	/**
	 * sportsmanagementModelProject::getProjectGameRegularTime()
	 * 
	 * @param mixed $project_id
	 * @return
	 */
	function getProjectGameRegularTime($project_id)
	{
	   $option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $gameprojecttime = 0;
        $query->select('game_regular_time');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project');
        $query->where('id = '.$project_id);

		$db->setQuery($query);
		$result = $db->loadObject();
		
        $gameprojecttime += $result->game_regular_time;
        if ( $result->allow_add_time )
        {
            $gameprojecttime += $result->add_time;
        }
        
        return $gameprojecttime;
	}

	/**
	 * sportsmanagementModelProject::getReferees()
	 * 
	 * @return
	 */
	function getReferees()
	{
	   $option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
		$project = self::getProject();
		if ($project->teams_as_referees)
		{
		  $query->select('id AS value,name AS text');
          $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team');
          $query->order('name');

			$db->setQuery($query);
			$refs = $db->loadObjectList();
		}
		else
		{
		  $query->select('id AS value,firstname,lastname');
          $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee');
          $query->order('lastname');

			$db->setQuery($query);
			$refs = $db->loadObjectList();
			foreach($refs as $ref)
			{
				$ref->text = $ref->lastname.",".$ref->firstname;
			}
		}
		return $refs;
	}

	/**
	 * sportsmanagementModelProject::getTemplateConfig()
	 * 
	 * @param mixed $template
	 * @return
	 */
	function getTemplateConfig($template)
	{
		$option = JRequest::getCmd('option');
        $mainframe	= JFactory::getApplication();
        //self::$projectid = JRequest::getInt('p',0);
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        //$query2 = $db->getQuery(true);
        
        //first load the default settings from the default <template>.xml file
		$paramsdata = "";
		$arrStandardSettings = array();
        
        $xmlfile = JPATH_COMPONENT_SITE.DS.'settings'.DS.'default'.DS.$template.'.xml';
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' template<br><pre>'.print_r($template,true).'</pre>'),'');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projectid<br><pre>'.print_r(self::$projectid,true).'</pre>'),'');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' xmlfile<br><pre>'.print_r($xmlfile,true).'</pre>'),'');
        }
       

		if( self::$projectid == 0) return $arrStandardSettings;

$query->select('t.params');
$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_template_config AS t');
$query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id=t.project_id');
$query->where('t.template = '.$db->Quote($template));
$query->where('p.id = '.$db->Quote(self::$projectid));

$starttime = microtime(); 
		$db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		if (! $result = $db->loadResult())
		{
			$project = self::getProject();
			if ( !empty($project) && $project->master_template > 0 )
			{
			 $query->clear();
				$query->select('t.params');
                $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_template_config AS t');
                $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id=t.project_id');
                $query->where('t.template = '.$db->Quote($template));
                $query->where('p.id = '.$db->Quote($project->master_template));

$starttime = microtime(); 
				$db->setQuery($query);
                
                if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
                $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
                }
                
                if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
				if (! $result = $db->loadResult())
				{
					JError::raiseNotice(500,JText::_('COM_SPORTSMANAGEMENT_MASTER_TEMPLATE_MISSING')." ".$template);
					JError::raiseNotice(500,JText::_('COM_SPORTSMANAGEMENT_MASTER_TEMPLATE_MISSING_PID'). $project->master_template);
					JError::raiseNotice(500,JText::_('COM_SPORTSMANAGEMENT_TEMPLATE_MISSING_HINT'));
                    
                    if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
                    $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' arrStandardSettings<br><pre>'.print_r($arrStandardSettings,true).'</pre>'),'');
                    }
                    
					return $arrStandardSettings;
				}
			}
			else
			{
				//JError::raiseNotice(500,'project ' . $this->projectid . '  setting not found');
				//there are no saved settings found, use the standard xml file default values
                
                if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
                $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' arrStandardSettings<br><pre>'.print_r($arrStandardSettings,true).'</pre>'),'');
                }
                
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
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' settings<br><pre>'.print_r($settings,true).'</pre>'),'');
        }
        
        return $settings;
		
		//return $extended;
	}

	/**
	 * sportsmanagementModelProject::getOverallConfig()
	 * 
	 * @return
	 */
	function getOverallConfig()
	{
		return self::getTemplateConfig('overall');
	}

	/**
	 * sportsmanagementModelProject::getMapConfig()
	 * 
	 * @return
	 */
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
        
        $query->select('l.country');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_league as l');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project as pro ON pro.league_id = l.id ');
        $query->where('pro.id = '. $db->Quote(self::$projectid));

		  $db->setQuery( $query );
		  $this->country = $db->loadResult();
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
        
        $query->select('et.id,et.name,et.icon');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype AS et');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position_eventtype AS pet ON pet.eventtype_id = et.id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.position_id = pet.position_id ');
        $query->where('ppos.project_id = '. $db->Quote(self::$projectid));

		if ($position_id)
		{
		  $query->where('ppos.position_id = '. $db->Quote($position_id));
		}
        $query->group('et.id');
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

		if (empty(self::$_stats))
		{
			require_once (JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'statistics'.DS.'base.php');
			$project = self::getProject();
			$project_id = $project->id;
            $query->select('ppos.id as pposid,ppos.position_id AS position_id');
            $query->select('stat.id,stat.name,stat.short,stat.class,stat.icon,stat.calculated,stat.params, stat.baseparams');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_statistic AS stat');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position_statistic AS ps ON ps.statistic_id = stat.id ');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.position_id = ps.position_id
						  AND ppos.project_id='.$project_id);
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id = ps.position_id');
            $query->where('stat.published = 1');
            $query->where('pos.published = 1');
            $query->order('pos.ordering,ps.ordering');

			$db->setQuery($query);
			self::$_stats = $db->loadObjectList();

		}
		// sort into positions
		$positions = self::getProjectPositions();
		$stats = array();
		// init
		foreach ($positions as $pos)
		{
			$stats[$pos->id]=array();
		}
		if (count(self::$_stats) > 0)
		{
			foreach (self::$_stats as $k => $row)
			{
				if (!$statid || $statid == $row->id || (is_array($statid) && in_array($row->id, $statid)))
				{
					$stat = SMStatistic::getInstance($row->class);
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

	/**
	 * sportsmanagementModelProject::getProjectPositions()
	 * 
	 * @return
	 */
	function getProjectPositions()
	{
	   $option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

		if (empty(self::$_positions))
		{
		  $query->select('pos.id,pos.persontype,pos.name,pos.ordering,pos.published');
          $query->select('ppos.id AS pposid');
          $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos');
          $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON ppos.position_id = pos.id');
          $query->where('ppos.project_id = '.$db->Quote(self::$projectid));

			$db->setQuery($query);
			self::$_positions = $db->loadObjectList('id');
		}
		return self::$_positions;
	}

	/**
	 * sportsmanagementModelProject::getClubIconHtml()
	 * 
	 * @param mixed $team
	 * @param integer $type
	 * @param integer $with_space
	 * @return
	 */
	function getClubIconHtml(&$team,$type=1,$with_space=0,$club_icon='logo_small')
	{
		$small_club_icon = $team->$club_icon;
        $title = $team->name;
		if ( $type == 1 )
		{
			$params = array();
			$params['align'] = "top";
			$params['border'] = 0;
			$params['width'] = 21;
			if ( $with_space == 1 )
			{
				$params['style']='padding:1px;';
			}
            
            switch ($small_club_icon)
            {
                case 'logo_small':
                $small_club_icon = sportsmanagementHelper::getDefaultPlaceholder("clublogosmall");
                break;
                case 'logo_middle':
                $small_club_icon = sportsmanagementHelper::getDefaultPlaceholder("clublogomedium");
                break;
                case 'logo_big':
                $small_club_icon = sportsmanagementHelper::getDefaultPlaceholder("clublogobig");
                break;
            }
//			if ($small_club_icon=='')
//			{
//				$small_club_icon = sportsmanagementHelper::getDefaultPlaceholder("clublogosmall");
//			}


			$image = "<a href=\"".JURI::root().$team->$club_icon."\" title=\"".$title."\" class=\"modal\">";
			$image.=JHtml::image($team->$club_icon,$title,$params);
			$image.="</a>";
                
            //return JHtml::image($small_club_icon,'',$params);
            return $image;
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

	/**
	 * sportsmanagementModelProject::isUserProjectAdminOrEditor()
	 * 
	 * @param integer $userId
	 * @param mixed $project
	 * @return
	 */
	public static function isUserProjectAdminOrEditor($userId=0, $project)
	{
		$result = false;
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
        $starttime = microtime(); 
        
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
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		
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
        //$query->where('match_id = '. (int)$this->matchid );
        $query->where('match_id = '. (int)$match_id );
    
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
	
	/**
	 * sportsmanagementModelProject::hasEditPermission()
	 * 
	 * @param mixed $task
	 * @return
	 */
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