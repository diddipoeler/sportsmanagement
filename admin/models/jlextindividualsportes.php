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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');
jimport('joomla.application.component.modellist');
//require_once(JPATH_COMPONENT.DS.'models'.DS.'list.php');



/**
 * sportsmanagementModeljlextindividualsportes
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModeljlextindividualsportes extends JModelList
{
	var $_identifier = "jlextindividualsportes";
    
    
    protected function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $project_id			= $mainframe->getUserState( "$option.pid", '0' );
		$match_id		= JRequest::getvar('id', 0);;
		$projectteam1_id		= JRequest::getvar('team1', 0);;
		$projectteam2_id		= JRequest::getvar('team2', 0);;
        //$search	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
        //$search_nation		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search_nation','search_nation','','word');
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        
        // Select some fields
		$query->select('mc.*');
		// From the hello table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_single AS mc');
        $query->where('mc.match_id = '.$match_id);

/*
		// Select some fields
		$query->select('obj.*');
        $query->select('st.name AS sportstype');
		// From the hello table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_league as obj');
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type AS st ON st.id = obj.sports_type_id');
        // Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = obj.checked_out');
        
        if ($search || $search_nation)
		{
        $query->where(self::_buildContentWhere());
        }
		$query->order(self::_buildContentOrderBy());
 */
 
		//$mainframe->enqueueMessage(JText::_('leagues query<br><pre>'.print_r($query,true).'</pre>'   ),'');
        return $query;
	}
    

/*
	function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();

		$query = '	SELECT	mc.*, 
						CASE mc.time_present 
						when "00:00:00" then NULL
						else DATE_FORMAT(mc.time_present, "%H:%i")
						END AS time_present, IFNULL(divhome.shortname, divhome.name) divhome, 
						divhome.id divhomeid,
						divaway.id divawayid,
						t1.name AS team1,
							t2.name AS team2,
							u.name AS editor, 
							(Select count(mp.id) 
							 FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp 
							 WHERE mp.match_id = mc.id
							   AND (came_in=0 OR came_in=1) 
							   AND mp.teamplayer_id in (
							     SELECT id 
							     FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tp
							     WHERE tp.projectteam_id = mc.projectteam1_id
							   )
							 ) AS homeplayers_count, 
							(Select count(ms.id) 
							 FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff AS ms
							 WHERE ms.match_id = mc.id
							   AND ms.team_staff_id in (
							     SELECT id 
							     FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_staff AS ts
							     WHERE ts.projectteam_id = mc.projectteam1_id
							   )
							) AS homestaff_count, 
							(Select count(mp.id) 
							 FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp 
							 WHERE mp.match_id = mc.id
							   AND (came_in=0 OR came_in=1) 
							   AND mp.teamplayer_id in (
							     SELECT id 
							     FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tp
							     WHERE tp.projectteam_id = mc.projectteam2_id
							   )
							 ) AS awayplayers_count, 
							(Select count(ms.id) 
							 FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff AS ms
							 WHERE ms.match_id = mc.id
							   AND ms.team_staff_id in (
							     SELECT id 
							     FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_staff AS ts
							     WHERE ts.projectteam_id = mc.projectteam2_id
							   )
							) AS awaystaff_count,
							(Select count(mr.id) 
							  FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee AS mr 
							  WHERE mr.match_id = mc.id
							) AS referees_count 
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_single AS mc
					LEFT JOIN #__users u ON u.id = mc.checked_out
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pthome ON pthome.id = mc.projectteam1_id
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS ptaway ON ptaway.id = mc.projectteam2_id
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON t1.id = pthome.id
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON t2.id = ptaway.id
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r ON r.id = mc.round_id 
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_division AS divaway ON divaway.id = ptaway.division_id 
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_division AS divhome ON divhome.id = pthome.division_id ' .
		
		$where . $orderby;
		return $query;
	}
*/

	function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');

		$mainframe	=& JFactory::getApplication();
		$filter_order		= $mainframe->getUserStateFromRequest($option . 'mc_filter_order', 'filter_order', 'mc.match_date', 'cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option . 'mc_filter_order_Dir', 'filter_order_Dir', '', 'word');

		if ($filter_order == 'mc.match_number')
		{
			$orderby    = ' ORDER BY mc.match_number +0 '. $filter_order_Dir .', divhome.id, divaway.id ' ;
		}
		elseif ($filter_order == 'mc.match_date')
		{
			$orderby 	= ' ORDER BY mc.match_date '. $filter_order_Dir .', divhome.id, divaway.id ';
		}
		else
		{
			$orderby 	= ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir . ' , mc.match_date, divhome.id, divaway.id';
		}

		return $orderby;
	}

	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$where=array();
		
		$mainframe	=& JFactory::getApplication();
		// $project_id = $mainframe->getUserState($option . 'project');
		$division	= (int) $mainframe->getUserStateFromRequest($option.'mc_division', 'division', 0);
		$round_id = $mainframe->getUserState($option . 'round_id');
		$match_id = $mainframe->getUserState($option . 'match_id');

		$where[] = ' mc.round_id = ' . $round_id;
		$where[] = ' mc.match_id = ' . $match_id;
		if ($division>0)
		{
			$where[]=' divhome.id = '.$this->_db->Quote($division);
		}
		$where=(count($where) ? ' WHERE '.implode(' AND ',$where) : '');
		
		return $where;
	}

	/**
	 * Method to return the project teams array (id, name)
	 *
	 * @access  public
	 * @return  array
	 * @since 0.1
	 */
	function getProjectTeams($project_id)
	{
		$option = JRequest::getCmd('option');

		$mainframe	= JFactory::getApplication();
		//$project_id = $mainframe->getUserState($option . 'project');
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
//$projectteam1_id		= JRequest::getvar('team1', 0);

// Select some fields
		$query->select('pt.id AS value');
        $query->select('t.name AS text,t.short_name AS short_name,t.notes');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = t.id');  
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
        $query->where('pt.project_id = ' . $project_id);
        //$query->where('pl.published = 1');
        $query->order('text ASC');
        
/*
		$query = '	SELECT	pt.id AS value,
							t.name AS text,
							t.short_name AS short_name,
							t.notes

					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = t.id
					WHERE pt.project_id = ' . $project_id . '
					ORDER BY text ASC ';
*/
		$db->setQuery($query);

		if (!$result = $db->loadObjectList())
		{
			$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
			return false;
		}
		else
		{
			return $result;
		}
	}

	/**
	 * @param int iDivisionId
	 * return project teams as options
	 * @return unknown_type
	 */
	function getProjectTeamsOptions($iDivisionId=0)
	{
		$option = JRequest::getCmd('option');

		$mainframe	=& JFactory::getApplication();
		$project_id = $mainframe->getUserState($option . 'project');

		$query = ' SELECT	pt.id AS value, '
		. ' CASE WHEN CHAR_LENGTH(t.name) < 25 THEN t.name ELSE t.middle_name END AS text '
		. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t '
		. ' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = t.id '
		. ' WHERE pt.project_id = ' . $project_id;
		if($iDivisionId>0)  {
			$query .=' AND pt.division_id = ' .$iDivisionId;
		}
		$query .= ' ORDER BY text ASC ';

		$this->_db->setQuery($query);
		$result = $this->_db->loadObjectList();
		if ($result === FALSE)
		{
			JError::raiseError(0, $this->_db->getErrorMsg());
			return false;
		}
		else
		{
			return $result;
		}
	}

	function getMatchesByRound($roundId)
	{
		$query = 'SELECT * FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_single WHERE round_id='.$roundId;
		$this->_db->setQuery($query);
		//echo($this->_db->getQuery());
		$result = $this->_db->loadObjectList();
		if ($result === FALSE)
		{
			JError::raiseError(0, $this->_db->getErrorMsg());
			return false;
		}
		return $result;
	}
	
	
	function getPlayer($teamid,$project_id)
	{
  $option = JRequest::getCmd('option');
	$mainframe	= JFactory::getApplication();
    // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        $season_id	= $mainframe->getUserState( "$option.season_id", '0' );

// Select some fields
		$query->select('tp.id AS value');
        $query->select('concat(pl.firstname," - ",pl.nickname," - ",pl.lastname) as text');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pl');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ON tp.person_id = pl.id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = tp.team_id');  
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
        $query->where('pt.id ='. $db->Quote($teamid));
        $query->where('pt.project_id ='. $project_id);
        
        $query->where('tp.season_id ='. $season_id);
        $query->where('st.season_id ='. $season_id);
        
        $query->where('pl.published = 1');
        $query->order('pl.lastname ASC');
        
		$db->setQuery($query);
        $result = $db->loadObjectList(); 
        
        if ( !$result && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
		return $result;
  
  }
 
  function getSportType($id)
  {
  $option = JRequest::getCmd('option');
	$mainframe	=& JFactory::getApplication();
  $query='SELECT name
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type
					WHERE id='. $this->_db->Quote($id);
		$this->_db->setQuery($query);
		$sporttype = $this->_db->loadResult();
		$mainframe->setUserState($option.'sporttype',$sporttype);
		$mainframe->enqueueMessage(JText::_('Sporttype: '.$sporttype ),'');
		
		switch ( strtolower($sporttype) )
		{
    case 'ringen':
    $this->_getSinglefile();
    break;
    }
		
		
		return $sporttype;
		
  }
  
  function _getSinglefile()
  {
  $option = JRequest::getCmd('option');
	$mainframe	=& JFactory::getApplication();
	
	$match_id		= $mainframe->getUserState( $option . 'match_id' );
	$query='SELECT match_number
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match
					WHERE id='. $this->_db->Quote($match_id);
		$this->_db->setQuery($query);
		$match_number = $this->_db->loadResult();
	
	$dir = JPATH_SITE.DS.'tmp'.DS.'ringerdateien';
  $files = JFolder::files($dir, '^MKEinzelkaempfe_Data_'.$match_number, false, false, array('^Termine_Schema') );
  
  $mainframe->enqueueMessage(JText::_('_getSinglefile: '.print_r($files,true) ),'');
  
  if ( $files )
  {
  $mainframe->enqueueMessage(JText::_('Einzelk&auml;mpfe '.$match_number.' vorhanden' ),'Notice');
  }
  else
  {
  $mainframe->enqueueMessage(JText::_('Einzelk&auml;mpfe '.$match_number.' nicht vorhanden' ),'Error');
  }
  
  }

}
?>