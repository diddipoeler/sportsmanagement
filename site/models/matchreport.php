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
jimport( 'joomla.filesystem.folder' );

/**
 * sportsmanagementModelMatchReport
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelMatchReport extends JModelLegacy
{

	var $matchid = 0;
	var $match = null;
    var $projectid = 0;
    
    //static $cfg_which_database = 0;

	/**
	 * caching for players events. Used in stats calculations
	 * @var unknown_type
	 */
	var $_playersevents = null;

	/**
	 * caching for players basic stats. Used in stats calculations
	 * @var unknown_type
	 */
	var $_playersbasicstats = null;

	/**
	 * caching for staff basic stats. Used in stats calculations
	 * @var unknown_type
	 */
	var $_staffsbasicstats = null;


	/**
	 * sportsmanagementModelMatchReport::__construct()
	 * 
	 * @return
	 */
	function __construct()
	{
	    // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        
		$this->matchid = $jinput->getInt('mid',0);
        $this->projectid = $jinput->getInt( 'p', 0 );
        sportsmanagementModelProject::$cfg_which_database = $jinput->getInt('cfg_which_database',0);
		sportsmanagementModelProject::$projectid = $this->projectid;
        sportsmanagementModelProject::$matchid = $this->matchid;
		parent::__construct();
	}

	
    /**
     * sportsmanagementModelMatchReport::checkMatchPlayerProjectPositionID()
     * 
     * @return void
     */
    function checkMatchPlayerProjectPositionID()
    {
    $option = JRequest::getCmd('option');
	$app = JFactory::getApplication();
    $starttime = microtime(); 
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, sportsmanagementModelProject::$cfg_which_database );
        $query = $db->getQuery(true);
        // Select some fields
		$query->select('id,match_id,teamplayer_id,project_position_id');
        // From table
		$query->from('#__sportsmanagement_match_player');
        $query->where('match_id = '.(int)$this->matchid);
        $query->where('project_position_id = 0');
        $db->setQuery($query);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
        $result = $db->loadObjectList();
        
        if ( $result )
        {
        
        foreach ($result as $row)
			{
			$query->clear();
            // Select some fields
		    $query->select('ppp.project_position_id');
            // From table
		    $query->from('#__sportsmanagement_person_project_position as ppp');
            $query->join('INNER','#__sportsmanagement_season_team_person_id AS tp ON tp.person_id = ppp.person_id');
            $query->join('INNER','#__sportsmanagement_person AS pe ON pe.id = tp.person_id');
            $query->where('ppp.project_id = '.(int)$this->projectid);
            $query->where('tp.id = '.(int)$row->teamplayer_id);
            $db->setQuery($query);
        
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
            $position = $db->loadResult();
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' position<br><pre>'.print_r($position,true).'</pre>'),'Notice');
            
            if ( $position )
            {
            $tblmatchplayer = JTable::getInstance("matchplayer", "sportsmanagementTable");
            $tblmatchplayer->load( (int) $row->id);
            $tblmatchplayer->project_position_id = $position;
            // Store the item to the database
		      if (!$tblmatchplayer->store())
		      {
			 //$this->setError($this->_db->getErrorMsg());
			 //return false;
		      }    
            
            }	
            
			}    
            
        }    
        
    }
    
    /**
	 * sportsmanagementModelMatchReport::getClubinfo()
	 * 
	 * @param mixed $clubid
	 * @return
	 */
	function getClubinfo($clubid)
	{
		//$this->club =& $this->getTable('Club','Table');
        $mdl = JModelLegacy::getInstance("club", "sportsmanagementModel");
        $this->club = $mdl->getTable();
		$this->club->load($clubid);

		return $this->club;
	}

	/**
	 * sportsmanagementModelMatchReport::getRound()
	 * 
	 * @return
	 */
	function getRound()
	{
		//$match=$this->getMatch();
        $match = sportsmanagementModelMatch::getMatchData($this->matchid,sportsmanagementModelProject::$cfg_which_database);

		//$round =& $this->getTable('Round','sportsmanagementTable');
		//$round->load($match->round_id);
        //$mdl = JModelLegacy::getInstance("round", "sportsmanagementModel");
        //$round = $mdl->getTable();
        //$round->load($match->round_id);
        
        $round = sportsmanagementModelround::getRound($match->round_id,sportsmanagementModelProject::$cfg_which_database);

		//if no match title set then set the default one
		if(is_null($round->name) || empty($round->name))
		{
			$round->name = JText::sprintf('COM_SPORTSMANAGEMENT_RESULTS_GAMEDAY_NB',$round->id);
		}

		return $round;
	}

  /**
   * sportsmanagementModelMatchReport::getMatchPictures()
   * 
   * @param mixed $folder
   * @return
   */
  function getMatchPictures($folder)
  {
  $basePath = JPATH_SITE.DS.'images'.DS.'com_sportsmanagement'.DS.'database'.DS.$folder;
  $sitePath = 'images'.DS.'com_sportsmanagement'.DS.'database'.DS.$folder;
  $images 	= array ();
  
  
  if( JFolder::exists($basePath) )
{
  // Get the list of files and folders from the given folder
	$fileList 	= JFolder::files($basePath);
  
  // Iterate over the files if they exist
		if ($fileList !== false) {
			foreach ($fileList as $file)
			{
				if (is_file($basePath.DS.$file) && substr($file, 0, 1) != '.'
					&& strtolower($file) !== 'index.html'
					&& strtolower($file) !== 'thumbs.db'
					&& strtolower($file) !== 'readme.txt'
					)

					{

					if ( $search == '') {
						$tmp = new JObject();
						$tmp->name = $file;
						$tmp->sitepath = $sitePath;
						$tmp->path = JPath::clean($basePath.DS.$file);

						$images[] = $tmp;

					} elseif(stristr( $file, $search)) {
						$tmp = new JObject();
						$tmp->name = $file;
						$tmp->sitepath = $sitePath;
						$tmp->path = JPath::clean($basePath.DS.$file);

						$images[] = $tmp;

					}
				}
			}
		}
		
	$list = $images;
  return $list;
  }
  else
  {
  return false;
  }
  	
  }
  
	
    
    /**
     * sportsmanagementModelMatchReport::getMatchPositions()
     * 
     * @param string $which
     * @return
     */
    function getMatchPositions($which = 'player')
	{
	$option = JRequest::getCmd('option');
	$app = JFactory::getApplication();
    $starttime = microtime(); 
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, sportsmanagementModelProject::$cfg_which_database );
        $query = $db->getQuery(true);
        // Select some fields
		$query->select('pos.id, pos.name');
		$query->select('ppos.position_id AS position_id, ppos.id as pposid');
        // From table
		$query->from('#__sportsmanagement_position AS pos');
        $query->join('INNER','#__sportsmanagement_project_position AS ppos ON pos.id = ppos.position_id');
        
        switch($which)
        {
            case 'player':
            $query->join('INNER','#__sportsmanagement_match_player AS mp ON ppos.id = mp.project_position_id');
            break;
            case 'staff':
            $query->join('INNER','#__sportsmanagement_match_staff AS mp ON ppos.id = mp.project_position_id');
            break;
            case 'referee':
            $query->join('INNER','#__sportsmanagement_match_referee AS mp ON ppos.id = mp.project_position_id');
            break;
        }
        
        $query->where('mp.match_id = '.(int)$this->matchid);
        $query->group('pos.id');
        $query->order('pos.ordering ASC');
        
        $db->setQuery($query);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
        $result = $db->loadObjectList();
		return $result;
        
    }    
    
    /**
     * sportsmanagementModelMatchReport::getMatchPersons()
     * 
     * @param string $which
     * @return
     */
    function getMatchPersons($which = 'player')
	{
	   $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $starttime = microtime(); 
        // Create a new query object.		
	   $db = sportsmanagementHelper::getDBConnection(TRUE, sportsmanagementModelProject::$cfg_which_database );
	   $query = $db->getQuery(true);
       
       // Select some fields
	   $query->select('pt.id,pt.id as ptid');
       $query->select('p.firstname,p.nickname,p.lastname,p.picture AS ppic');
       $query->select('ppos.position_id,ppos.id AS pposid');
       $query->select('st.team_id,st.id as stid');
       
       $query->select('tp.person_id,tp.picture');
       $query->select('CONCAT_WS(\':\',t.id,t.alias) AS team_slug');
       $query->select('CONCAT_WS(\':\',p.id,p.alias) AS person_slug');
       
       switch($which)
        {
            case 'player':
            $query->select('mp.trikot_number,mp.teamplayer_id,mp.captain');
            $query->select('tp.jerseynumber');
            $query->from('#__sportsmanagement_match_player AS mp');
            $query->join('INNER','#__sportsmanagement_season_team_person_id AS tp ON tp.id = mp.teamplayer_id ');
            $query->where('mp.came_in = 0');
            $query->order('mp.ordering, tp.jerseynumber, p.lastname');
            break;
            case 'staff':
            $query->select('mp.team_staff_id');
            $query->from('#__sportsmanagement_match_staff AS mp');
            $query->join('INNER','#__sportsmanagement_season_team_person_id AS tp ON tp.id = mp.team_staff_id ');
            break;
        }    
       
       $query->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
	   $query->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id ');
	   $query->join('INNER','#__sportsmanagement_team AS t ON t.id = st.team_id ');
	   $query->join('INNER','#__sportsmanagement_person AS p ON tp.person_id = p.id ');
//	   $query->join('LEFT','#__sportsmanagement_project_position AS ppos ON ppos.id = mp.project_position_id ');
//	   $query->join('LEFT','#__sportsmanagement_position AS pos ON ppos.position_id = pos.id ');
       
       $query->join('LEFT','#__sportsmanagement_person_project_position AS ppp on ppp.person_id = tp.person_id and ppp.persontype = tp.persontype');
       $query->join('LEFT','#__sportsmanagement_project_position AS ppos ON ppos.id = ppp.project_position_id ');
	   $query->join('LEFT','#__sportsmanagement_position AS pos ON ppos.position_id = pos.id ');
       
       $query->where('pt.project_id = '.$this->projectid);
       
       $query->where('mp.match_id = '.(int)$this->matchid);
       $query->where('p.published = 1');

              
       $db->setQuery($query);
       
       //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice'); 
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
//        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' matchid<br><pre>'.print_r($this->matchid,true).'</pre>'),'Notice');
//        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($db->loadObjectList(),true).'</pre>'),'Notice');
        
           $my_text = 'matchid<pre>'.print_r($this->matchid,true).'</pre>'; 
        $my_text .= 'loadObjectList<pre>'.print_r($db->loadObjectList(),true).'</pre>';
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text);
        
        }
        
        $result = $db->loadObjectList();
        if ( !$result )
	    {
	       if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $my_text = 'getErrorMsg<pre>'.print_r($db->getErrorMsg(),true).'</pre>'; 
        $my_text .= 'dump<pre>'.print_r($query->dump(),true).'</pre>';
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text);
		//$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
        }
        
	    }


		return $result;
   }    
    
    

    /**
     * sportsmanagementModelMatchReport::getMatchReferees()
     * 
     * @return
     */
    function getMatchReferees()
	{
		$option = JRequest::getCmd('option');
	$app = JFactory::getApplication();
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, sportsmanagementModelProject::$cfg_which_database );
        $query = $db->getQuery(true);
        
        $query->select('p.id,p.firstname,p.nickname,p.lastname,CONCAT_WS(\':\',p.id,p.alias) AS person_slug,p.picture');
        $query->select('ppos.position_id,ppos.id AS pposid');
        $query->select('pos.name AS position_name');
        $query->from('#__sportsmanagement_match_referee AS mr');
        $query->join('INNER','#__sportsmanagement_project_referee AS pref ON mr.project_referee_id=pref.id ');
        $query->join('INNER','#__sportsmanagement_season_person_id AS tp on tp.id = pref.person_id');
        
        $query->join('INNER','#__sportsmanagement_person AS p ON tp.person_id=p.id');
        $query->join('LEFT','#__sportsmanagement_project_position AS ppos ON ppos.id=mr.project_position_id');
        $query->join('LEFT','#__sportsmanagement_position AS pos ON ppos.position_id=pos.id');
        $query->where('mr.match_id='.(int)$this->matchid);
        $query->where('p.published = 1');
        $query->where('tp.persontype = 3');
        
        // $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
              
		$db->setQuery($query);
        $result = $db->loadObjectList();
		return $result;
	}



	/**
	 * sportsmanagementModelMatchReport::getEventTypes()
	 * 
	 * @return
	 */
	function getEventTypes()
	{
		$option = JRequest::getCmd('option');
	$app = JFactory::getApplication();
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, sportsmanagementModelProject::$cfg_which_database );
        $query = $db->getQuery(true);
        
        $query->select('et.id,et.name,et.icon');
        $query->from('#__sportsmanagement_eventtype AS et');
        $query->join('INNER','#__sportsmanagement_position_eventtype AS pet ON pet.eventtype_id = et.id ');
	    $query->join('LEFT','#__sportsmanagement_match_event AS me ON et.id = me.event_type_id ');
        $query->where('me.match_id = '.(int)$this->matchid);
        $query->group('et.id');
        $query->order('pet.ordering');
                    
		$db->setQuery($query);
        $result = $db->loadObjectList();
		return $result;
	}

    
    /**
     * sportsmanagementModelMatchReport::getMatchArticle()
     * 
     * @param mixed $article_id
     * @return
     */
    function getMatchArticle($article_id)
	{
		$option = JRequest::getCmd('option');
	$app = JFactory::getApplication();
    $starttime = microtime(); 
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, sportsmanagementModelProject::$cfg_which_database );
        $query = $db->getQuery(true);
        $query->select('c.id,c.title');
       $query->select('c.introtext');
       
        switch ( JComponentHelper::getParams($option)->get('which_article_component') )
    {
        case 'com_content':
        
        $query->from('#__content as c');
        break;
        case 'com_k2':
        $query->from('#__k2_items as c');
        break;
    }
    $query->where('id ='. $article_id );
       $db->setQuery($query); 
        $result = $db->loadObject();
        return $result;
        
    }
        
	/**
	 * sportsmanagementModelMatchReport::getMatchStats()
	 * 
	 * @return
	 */
	function getMatchStats()
	{
		$option = JRequest::getCmd('option');
	$app = JFactory::getApplication();
    $starttime = microtime(); 
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, sportsmanagementModelProject::$cfg_which_database );
        $query = $db->getQuery(true);
        
		$match = sportsmanagementModelMatch::getMatchData($this->matchid,sportsmanagementModelProject::$cfg_which_database);
        
         $query->select('*');
        $query->from('#__sportsmanagement_match_statistic');
        $query->where('match_id = '.$match->id );
        
        
//		$query=' SELECT * FROM #__sportsmanagement_match_statistic '
//		      .' WHERE match_id='. $this->_db->Quote($match->id);
              
		$db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		$res = $db->loadObjectList();

		$stats = array(	$match->projectteam1_id => array(),
						$match->projectteam2_id => array());
		if(count($stats)>0 && count($res)>0) {
			foreach ($res as $stat)
			{
				@$stats[$stat->projectteam_id][$stat->teamplayer_id][$stat->statistic_id]=$stat->value;
			}
		}
		return $stats;
	}

	
	/**
	 * sportsmanagementModelMatchReport::getPlayersStats()
	 * 
	 * @return
	 */
	function getPlayersStats()
	{
		$option = JRequest::getCmd('option');
	$app = JFactory::getApplication();
    $starttime = microtime(); 
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, sportsmanagementModelProject::$cfg_which_database );
        $query = $db->getQuery(true);
        
		if (!($this->_playersbasicstats))
		{
			$match = sportsmanagementModelProject::getMatch();
            
            $query->select('*');
        $query->from('#__sportsmanagement_match_statistic');
        $query->where('match_id = '.$match->id );

//			$query=' SELECT * FROM #__sportsmanagement_match_statistic '
//			      .' WHERE match_id='. $this->_db->Quote($match->id);
                  
                  
			$db->setQuery($query);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
			$res = $db->loadObjectList();

			$stats=array();
			if (count($res))
			{
				foreach ($res as $stat)
				{
					@$stats[$stat->teamplayer_id][$stat->statistic_id]=$stat->value;
				}
			}
			$this->_playersbasicstats=$stats;
		}

		return $this->_playersbasicstats;
	}

	
	/**
	 * sportsmanagementModelMatchReport::getPlayersEvents()
	 * 
	 * @return
	 */
	function getPlayersEvents()
	{
		$option = JRequest::getCmd('option');
	$app = JFactory::getApplication();
    $starttime = microtime(); 
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, sportsmanagementModelProject::$cfg_which_database );
        $query = $db->getQuery(true);
        
		if (!($this->_playersevents))
		{
			$match = sportsmanagementModelMatch::getMatchData($this->matchid,sportsmanagementModelProject::$cfg_which_database);
            // Select some fields
        $query->select('*');
        // From 
		$query->from('#__sportsmanagement_match_event');
        // Where
        $query->where('match_id = '. $match->id );
			
            $db->setQuery($query);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
			$res = $db->loadObjectList();

			$events = array();
			if (count($res))
			{
				foreach ($res as $event)
				{
					@$events[$event->teamplayer_id][$event->event_type_id] += $event->event_sum;
				}
			}
			$this->_playersevents = $events;
		}

		return $this->_playersevents;
	}

	
	/**
	 * sportsmanagementModelMatchReport::getMatchStaffStats()
	 * 
	 * @return
	 */
	function getMatchStaffStats()
	{
		$option = JRequest::getCmd('option');
	$app = JFactory::getApplication();
    $starttime = microtime(); 
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, sportsmanagementModelProject::$cfg_which_database );
        $query = $db->getQuery(true);
        
		if (!($this->_staffsbasicstats))
		{
			$match = sportsmanagementModelMatch::getMatchData($this->matchid,sportsmanagementModelProject::$cfg_which_database);
            
            // Select some fields
        $query->select('*');
        // From 
		$query->from('#__sportsmanagement_match_staff_statistic');
        // Where
        $query->where('match_id = '. $match->id );

//			$query=' SELECT * FROM #__sportsmanagement_match_staff_statistic '
//			      .' WHERE match_id='. $this->_db->Quote($match->id);
			
            $db->setQuery($query);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
			$res = $db->loadObjectList();

			$stats = array();
			if (count($res))
			{
				foreach ($res as $stat)
				{
					@$stats[$stat->team_staff_id][$stat->statistic_id]=$stat->value;
				}
			}
			$this->_staffsbasicstats=$stats;
		}
		return $this->_staffsbasicstats;
	}
   
    
    	/**
    	 * sportsmanagementModelMatchReport::getPlaygroundSchema()
    	 * 
    	 * @param mixed $schema
    	 * @param mixed $which
    	 * @return
    	 */
    	function getPlaygroundSchema($schema,$which)
  {
  $option = JRequest::getCmd('option');
	$app = JFactory::getApplication();
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, sportsmanagementModelProject::$cfg_which_database );
        $query = $db->getQuery(true);
        
  $bildpositionen = array();
  $position = 1;
  
  if ( $schema )
  {
  // Select some fields
        $query->select('extended');
        // From 
		$query->from('#__sportsmanagement_rosterposition');
        // Where
        $query->where('name LIKE '.  $db->Quote( '' . $schema . '' ) );
  
  switch ($which)
  {
    case 'heim':
    $query->where('short_name LIKE '.  $db->Quote( '' . 'HOME_POS' . '' ) );
    break;
    case 'gast':
    $query->where('short_name LIKE '.  $db->Quote( '' . 'AWAY_POS' . '' ) );
    break;
    
  }
  		
        $db->setQuery($query);
		$res = $db->loadResult();
		
		$xmlfile = JPATH_COMPONENT_ADMINISTRATOR.DS.'assets'.DS.'extended'.DS.'rosterposition.xml';
		$jRegistry = new JRegistry;
		if(version_compare(JVERSION,'3.0.0','ge')) 
        {
        $jRegistry->loadString($res); 
        }
        else
        {
		//$jRegistry->loadString($res, 'ini');
		$jRegistry->loadJSON($res);
		}
    
    for($a=0; $a < 11; $a++)
    {
    $bildpositionen[$schema][$a][$which]['oben'] = $jRegistry->get('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_TOP');
    $bildpositionen[$schema][$a][$which]['links'] = $jRegistry->get('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_LEFT');
    $position++;
    }
		
		return $bildpositionen;
  }
  else
  {
    return false;
  }
  
  }

}
?>
