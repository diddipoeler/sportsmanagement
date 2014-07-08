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

//require_once( JLG_PATH_SITE . DS . 'models' . DS . 'project.php' );

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
    var $projectid= 0;

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
		$this->matchid = JRequest::getInt('mid',0);
        $this->projectid = JRequest::getInt( 'p', 0 );
		sportsmanagementModelProject::$projectid = $this->projectid;
		parent::__construct();
	}


/**
 * 	// Functions (some specific for Matchreport) below to be replaced to project.php when recoded to general functions
 * 	function &getMatch()
 * 	{
 * 		if (is_null($this->match))
 * 		{
 * 			$query=' SELECT m.*,DATE_FORMAT(m.time_present,"%H:%i") time_present,r.project_id '
 * 			      .' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m '
 * 			      .' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r on r.id=m.round_id '
 * 			      .' WHERE m.id='. $this->_db->Quote($this->matchid)
 * 			            ;
 * 			$this->_db->setQuery($query,0,1);
 * 			$this->match=$this->_db->loadObject();
 * 		}
 * 		return $this->match;
 * 	}
 */

/**
 * 	function &getProject()
 * 	{
 * 		if (empty($this->_project))
 * 		{
 * 			$match=&$this->getMatch();
 * 			$this->setProjectID($match->project_id);
 * 			parent::getProject();
 * 		}
 * 		return $this->_project;
 * 	}
 */

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
        $match = sportsmanagementModelMatch::getMatchData($this->matchid);

		//$round =& $this->getTable('Round','sportsmanagementTable');
		//$round->load($match->round_id);
        $mdl = JModelLegacy::getInstance("round", "sportsmanagementModel");
        $round = $mdl->getTable();
        $round->load($match->round_id);

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
	$mainframe = JFactory::getApplication();
    $starttime = microtime(); 
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        // Select some fields
		$query->select('pos.id, pos.name');
		$query->select('ppos.position_id AS position_id, ppos.id as pposid');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON pos.id = ppos.position_id');
        
        switch($which)
        {
            case 'player':
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp ON ppos.id = mp.project_position_id');
            break;
            case 'staff':
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff AS mp ON ppos.id = mp.project_position_id');
            break;
            case 'referee':
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee AS mp ON ppos.id = mp.project_position_id');
            break;
        }
        
        $query->where('mp.match_id = '.(int)$this->matchid);
        $query->group('pos.id');
        $query->order('pos.ordering ASC');
        
        $db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {        
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		return $db->loadObjectList();
        
    }    
    
//    /**
//	 * sportsmanagementModelMatchReport::getMatchPlayerPositions()
//	 * 
//	 * @return
//	 */
//	function getMatchPlayerPositions()
//	{
//	$option = JRequest::getCmd('option');
//	$mainframe = JFactory::getApplication();
//        // Get a db connection.
//        $db = JFactory::getDbo();
//        $query = $db->getQuery(true);
//        
//		$query='	SELECT	pos.id, pos.name, 
//							ppos.position_id AS position_id, ppos.id as pposid
//					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos
//					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON pos.id=ppos.position_id
//					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp ON ppos.id=mp.project_position_id
//					WHERE mp.match_id='.(int)$this->matchid.'
//					GROUP BY pos.id
//					ORDER BY pos.ordering ASC ';
//                    
//		$db->setQuery($query);
//		return $db->loadObjectList();
//	}

//	/**
//	 * sportsmanagementModelMatchReport::getMatchStaffPositions()
//	 * 
//	 * @return
//	 */
//	function getMatchStaffPositions()
//	{
//		$option = JRequest::getCmd('option');
//	$mainframe = JFactory::getApplication();
//        // Get a db connection.
//        $db = JFactory::getDbo();
//        $query = $db->getQuery(true);
//        
//		$query='	SELECT	pos.id, pos.name, 
//							ppos.position_id AS position_id, ppos.id as pposid
//					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos
//					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON pos.id=ppos.position_id
//					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff AS mp ON ppos.id=mp.project_position_id
//					WHERE mp.match_id='.(int)$this->matchid.'
//					GROUP BY pos.id
//					ORDER BY pos.ordering ASC ';
//		$this->_db->setQuery($query);
//		return $this->_db->loadObjectList();
//	}

//	/**
//	 * sportsmanagementModelMatchReport::getMatchRefereePositions()
//	 * 
//	 * @return
//	 */
//	function getMatchRefereePositions()
//	{
//		$option = JRequest::getCmd('option');
//	$mainframe = JFactory::getApplication();
//        // Get a db connection.
//        $db = JFactory::getDbo();
//        $query = $db->getQuery(true);
//        
//		$query='	SELECT	pos.id, pos.name, 
//							ppos.position_id AS position_id, ppos.id as pposid
//					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos
//					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON pos.id=ppos.position_id
//					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee AS mp ON ppos.id=mp.project_position_id
//					WHERE mp.match_id='.(int)$this->matchid.'
//					GROUP BY pos.id
//					ORDER BY pos.ordering ASC ';
//		$this->_db->setQuery($query);
//		return $this->_db->loadObjectList();
//	}

	
    
    /**
     * sportsmanagementModelMatchReport::getMatchPersons()
     * 
     * @param string $which
     * @return
     */
    function getMatchPersons($which = 'player')
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $starttime = microtime(); 
        // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
       // Select some fields
	   $query->select('pt.id');
       $query->select('p.firstname,p.nickname,p.lastname,p.picture AS ppic');
       $query->select('ppos.position_id,ppos.id AS pposid');
       $query->select('st.team_id,st.id as ptid');
       
       $query->select('tp.person_id,tp.picture');
       $query->select('CONCAT_WS(\':\',t.id,t.alias) AS team_slug');
       $query->select('CONCAT_WS(\':\',p.id,p.alias) AS person_slug');
       switch($which)
        {
            case 'player':
            $query->select('mp.trikot_number,mp.teamplayer_id');
            $query->select('tp.jerseynumber');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ON tp.id = mp.teamplayer_id ');
            $query->where('mp.came_in = 0');
            $query->order('mp.ordering, tp.jerseynumber, p.lastname');
            break;
            case 'staff':
            $query->select('ms.team_staff_id');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff AS ms');
            $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ON tp.id = ms.team_staff_id ');
            break;
        }    
       
       $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = tp.team_id ');
	   $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id ');
	   $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id = st.team_id ');
	   $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p ON tp.person_id = p.id ');
	   $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id = mp.project_position_id ');
	   $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON ppos.position_id = pos.id ');
       
       $query->where('mp.match_id = '.(int)$this->matchid);
       $query->where('p.published = 1');
       
       
       
              
       $db->setQuery($query);
        
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {        
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' matchid<br><pre>'.print_r($this->matchid,true).'</pre>'),'Notice');
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($db->loadObjectList(),true).'</pre>'),'Notice');
        }
        
        $result = $db->loadObjectList();
        if ( !$result )
	    {
	       if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
		$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
        }
        
	    }
        
		return $result;
   }    
    
    
//    /**
//	 * sportsmanagementModelMatchReport::getMatchPlayers()
//	 * 
//	 * @return
//	 */
//	function getMatchPlayers()
//	{
//	   $mainframe = JFactory::getApplication();
//        $option = JRequest::getCmd('option');
//        // Create a new query object.		
//	   $db = JFactory::getDBO();
//	   $query = $db->getQuery(true);
//       
//		$query=' SELECT	pt.id,'
//		      .' tp.person_id,'
//		      .' p.firstname,'
//		      .' p.nickname,'
//		      .' p.lastname,'
//		      .' tp.jerseynumber,'
//              .' mp.trikot_number,'
//		      .' ppos.position_id,'
//		      .' ppos.id AS pposid,'
//		      .' st.team_id,'
//		      //.' pt.id as ptid,'
//              .' st.id as ptid,'
//		      .' mp.teamplayer_id,'
//		      .' tp.picture,'
//			  .' p.picture AS ppic,'
//		      .' CASE WHEN CHAR_LENGTH(t.alias) THEN CONCAT_WS(\':\',t.id,t.alias) ELSE t.id END AS team_slug,'
//		      .' CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\',p.id,p.alias) ELSE p.id END AS person_slug '
//		      .' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp '
//		      .' INNER JOIN	#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ON tp.id = mp.teamplayer_id '
//              .' INNER JOIN	#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = tp.team_id '
//		      .' INNER JOIN	#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id '
//		      .' INNER JOIN	#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id = st.team_id '
//		      .' INNER JOIN	#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p ON tp.person_id = p.id '
//		      .' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id = mp.project_position_id '
//		      .' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON ppos.position_id = pos.id '
//		      .' WHERE mp.match_id = '.(int)$this->matchid
//		      .' AND mp.came_in = 0 '
//		      .' AND p.published = 1 '
//		      .' ORDER BY mp.ordering, tp.jerseynumber, p.lastname ';
//              
//		$db->setQuery($query);
//        
//        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
//        {
//        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' matchid<br><pre>'.print_r($this->matchid,true).'</pre>'),'Notice');
//        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($db->loadObjectList(),true).'</pre>'),'Notice');
//        }
//        
//        $result = $db->loadObjectList();
//        if ( !$result )
//	    {
//	       if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
//        {
//		$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
//        }
//        
//	    }
//        
//		return $result;
//	}

//	/**
//	 * sportsmanagementModelMatchReport::getMatchStaff()
//	 * 
//	 * @return
//	 */
//	function getMatchStaff()
//	{
//	   $mainframe = JFactory::getApplication();
//        $option = JRequest::getCmd('option');
//        // Create a new query object.		
//	   $db = JFactory::getDBO();
//	   $query = $db->getQuery(true);
//       
//		$query=' SELECT	p.id,'
//		      .' p.id AS person_id,'
//		      .' ms.team_staff_id,'
//		      .' p.firstname,'
//		      .' p.nickname,'
//		      .' p.lastname,'
//		      .' ppos.position_id,'
//		      .' ppos.id AS pposid,'
//		      .' pt.team_id,'
//		      //.' pt.id as ptid,'
//              .' st.id as ptid,'
//		      .' tp.picture,'
//			  .' CASE WHEN CHAR_LENGTH(t.alias) THEN CONCAT_WS(\':\',t.id,t.alias) ELSE t.id END AS team_slug,'
//			  .' CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\',p.id,p.alias) ELSE p.id END AS person_slug '
//		      .' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff AS ms '
//		      .' INNER JOIN	#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ON tp.id = ms.team_staff_id '
//              .' INNER JOIN	#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = tp.team_id '
//		      .' INNER JOIN	#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id '
//		      .' INNER JOIN	#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p ON tp.person_id = p.id '
//		      .' INNER JOIN	#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON t.id = st.team_id '
//		      .' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id = ms.project_position_id '
//		      .' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON ppos.position_id = pos.id '
//		      .' WHERE ms.match_id='.(int)$this->matchid
//		      .'  AND p.published = 1';
//              
//              
//              
//              
//		       $db->setQuery($query);
//               
//               if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
//        {
//               $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' matchid<br><pre>'.print_r($this->matchid,true).'</pre>'),'Notice');
//               $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($db->loadObjectList(),true).'</pre>'),'Notice');
//         }
//               
//		$result = $db->loadObjectList();
//        if ( !$result )
//	    {
//	       if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
//        {
//		$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');
//        }
//        
//	    }
//        
//        return $result;
//	}

//	/**
//	 * sportsmanagementModelMatchReport::getMatchCommentary()
//	 * 
//	 * @return
//	 */
//	function getMatchCommentary()
//    {
//    	$option = JRequest::getCmd('option');
//	$mainframe = JFactory::getApplication();
//    $starttime = microtime(); 
//        // Get a db connection.
//        $db = JFactory::getDbo();
//        $query = $db->getQuery(true);
//        
//        $query->select('*');
//        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_commentary');
//        $query->where('match_id = '.(int)$this->matchid);
//        $query->order('event_time DESC');
//        
////        $query = "SELECT *  
////    FROM #__".COM_SPORTSMANAGEMENT_TABLE."_match_commentary
////    WHERE match_id = ".(int)$this->matchid." 
////    ORDER BY event_time DESC";
//
//
//    $db->setQuery($query);
//    
//    if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
//        {        
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
//        }
//        
//		return $db->loadObjectList();
//    }
    
    
    /**
     * sportsmanagementModelMatchReport::getMatchReferees()
     * 
     * @return
     */
    function getMatchReferees()
	{
		$option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
		$query=' SELECT	p.id,'
		      .' p.firstname,'
		      .' p.nickname,'
		      .' p.lastname,'
		      .' ppos.position_id,'
		      .' ppos.id AS pposid,'
		      .' pos.name AS position_name ,'
		      .' CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\',p.id,p.alias) ELSE p.id END AS person_slug '
		      .' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee AS mr '
		      .' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS pref ON mr.project_referee_id=pref.id '
		      .' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p ON pref.person_id=p.id '
		      .' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id=mr.project_position_id '
		      .' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON ppos.position_id=pos.id '
		      .' WHERE mr.match_id='.(int)$this->matchid
		      .' AND p.published = 1';
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}



	/**
	 * sportsmanagementModelMatchReport::getEventTypes()
	 * 
	 * @return
	 */
	function getEventTypes()
	{
		$option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        $query->select('et.id,et.name,et.icon');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype AS et');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_position_eventtype AS pet ON pet.eventtype_id = et.id ');
	    $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event AS me ON et.id = me.event_type_id ');
        $query->where('me.match_id = '.(int)$this->matchid);
        $query->group('et.id');
        $query->order('pet.ordering');
        
//		$query='	SELECT	et.id,
//							et.name,
//							et.icon
//					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_eventtype AS et
//					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position_eventtype AS pet ON pet.eventtype_id=et.id					
//					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event AS me ON et.id=me.event_type_id
//					WHERE me.match_id='.(int)$this->matchid.'
//					GROUP BY et.id
//					ORDER BY pet.ordering ';
                    
		$db->setQuery($query);
		return $db->loadObjectList();
	}

//	/**
//	 * sportsmanagementModelMatchReport::getPlayground()
//	 * 
//	 * @param mixed $pgid
//	 * @return
//	 */
//	function getPlayground($pgid)
//	{
//		//$this->playground =& $this->getTable('Playground','Table');
//        $mdl = JModelLegacy::getInstance("Playground", "sportsmanagementModel");
//        $this->playground = $mdl->getTable();
//		$this->playground->load($pgid);
//
//		return $this->playground;
//	}

	
    
    function getMatchArticle($article_id)
	{
		$option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
    $starttime = microtime(); 
        // Get a db connection.
        $db = JFactory::getDbo();
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
	$mainframe = JFactory::getApplication();
    $starttime = microtime(); 
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
		$match = sportsmanagementModelMatch::getMatchData($this->matchid);
        
         $query->select('*');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_statistic');
        $query->where('match_id = '.$db->Quote($match->id));
        
        
//		$query=' SELECT * FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_statistic '
//		      .' WHERE match_id='. $this->_db->Quote($match->id);
              
		$db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {        
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
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
	$mainframe = JFactory::getApplication();
    $starttime = microtime(); 
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
		if (!($this->_playersbasicstats))
		{
			$match=&$this->getMatch();
            
            $query->select('*');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_statistic');
        $query->where('match_id = '.$db->Quote($match->id));

//			$query=' SELECT * FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_statistic '
//			      .' WHERE match_id='. $this->_db->Quote($match->id);
                  
                  
			$db->setQuery($query);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {        
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
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
	$mainframe = JFactory::getApplication();
    $starttime = microtime(); 
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
		if (!($this->_playersevents))
		{
			$match = sportsmanagementModelMatch::getMatchData($this->matchid);
            // Select some fields
        $query->select('*');
        // From 
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event');
        // Where
        $query->where('match_id = '. $db->Quote($match->id) );
			
            $db->setQuery($query);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {        
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
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
	$mainframe = JFactory::getApplication();
    $starttime = microtime(); 
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
		if (!($this->_staffsbasicstats))
		{
			$match = sportsmanagementModelMatch::getMatchData($this->matchid);
            
            // Select some fields
        $query->select('*');
        // From 
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff_statistic');
        // Where
        $query->where('match_id = '. $db->Quote($match->id) );

//			$query=' SELECT * FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff_statistic '
//			      .' WHERE match_id='. $this->_db->Quote($match->id);
			
            $db->setQuery($query);
            
            if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {        
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
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

//	/**
//	 * sportsmanagementModelMatchReport::getMatchText()
//	 * 
//	 * @param mixed $match_id
//	 * @return
//	 */
//	function getMatchText($match_id)
//	{
//		$option = JRequest::getCmd('option');
//	$mainframe = JFactory::getApplication();
//        // Get a db connection.
//        $db = JFactory::getDbo();
//        $query = $db->getQuery(true);
//        
//		$query="SELECT	m.*,
//						t1.name t1name,
//						t2.name t2name
//				FROM #__".COM_SPORTSMANAGEMENT_TABLE."_match AS m
//				INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_project_team AS pt1 ON m.projectteam1_id=pt1.id
//				INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_project_team AS pt2 ON m.projectteam2_id=pt2.id
//				INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_team AS t1 ON pt1.team_id=t1.id
//				INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_team AS t2 ON pt2.team_id=t2.id
//				WHERE m.id=".$match_id."
//				AND m.published=1
//				ORDER BY m.match_date,t1.short_name";
//                
//		$db->setQuery($query);
//		return $db->loadObject();
//	}
	
	/**
	 * sportsmanagementModelMatchReport::getSchemaHome()
	 * 
	 * @param mixed $schemahome
	 * @return
	 */
	function getSchemaHome($schemahome)
  {
  $option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
  $bildpositionen = array();
  $position = 1;
  
  if ( $schemahome )
  {
  $query =" SELECT extended"
				. " FROM #__".COM_SPORTSMANAGEMENT_TABLE."_rosterposition "
				. " WHERE name LIKE '" . $schemahome ."'"
				. " AND short_name = 'HOME_POS'";
		$db->setQuery($query);
		$res = $db->loadResult();
		
		$xmlfile = JPATH_COMPONENT_ADMINISTRATOR.DS.'assets'.DS.'extended'.DS.'rosterposition.xml';
		$jRegistry = new JRegistry;
		//$jRegistry->loadString($res, 'ini');
		$jRegistry->loadJSON($res);
    
    for($a=0; $a < 11; $a++)
    {
    $bildpositionen[$schemahome][$a]['heim']['oben'] = $jRegistry->get('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_TOP');
    $bildpositionen[$schemahome][$a]['heim']['links'] = $jRegistry->get('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_LEFT');
    $position++;
    }
		
		return $bildpositionen;
  }
  else
  {
    return false;
  }
  
  }
  
  /**
   * sportsmanagementModelMatchReport::getSchemaAway()
   * 
   * @param mixed $schemaaway
   * @return
   */
  function getSchemaAway($schemaaway)
  {
  $option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
  $bildpositionen = array();
  $position = 1;
    
  if ( $schemaaway )
  {
  $query =" SELECT extended"
				. " FROM #__".COM_SPORTSMANAGEMENT_TABLE."_rosterposition "
				. " WHERE name LIKE '" . $schemaaway ."'"
				. " AND short_name = 'AWAY_POS'";
		$db->setQuery($query);
		$res = $db->loadResult();

		$xmlfile = JPATH_COMPONENT_ADMINISTRATOR.DS.'assets'.DS.'extended'.DS.'rosterposition.xml';
		$jRegistry = new JRegistry;
		//$jRegistry->loadString($res, 'ini');
		$jRegistry->loadJSON($res);
    
    for($a=0; $a < 11; $a++)
    {
    $bildpositionen[$schemaaway][$a]['gast']['oben'] = $jRegistry->get('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_TOP');
    $bildpositionen[$schemaaway][$a]['gast']['links'] = $jRegistry->get('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_'.$position.'_LEFT');
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
