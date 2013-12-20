<?php
/**
 * @copyright	Copyright (C) 2013 fussballineuropa.de. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');


/**
 * Sportsmanagement Component Matches Model
 *
 * @author	Marco Vaninetti <martizva@tiscali.it>
 * @package	Sportsmanagement
 * @since	0.1
 */

class sportsmanagementModelMatches extends JModelList
{
	var $_identifier = "matches";
    var $_rid = 0;
    var $_season_id = 0;

	protected function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $this->_season_id	= $mainframe->getUserState( "$option.season_id", '0' );
        $show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0) ;
        
        $this->_rid = JRequest::getvar('rid', 0);
        
        $mainframe->enqueueMessage(JText::_('sportsmanagementViewMatches _season_id<br><pre>'.print_r($this->_season_id,true).'</pre>'),'');
        
        if ( !$this->_rid )
        {
            $this->_rid	= $mainframe->getUserState( "$option.rid", '0' );
        }
        
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        $subQueryPlayerHome= $db->getQuery(true);
        $subQueryStaffHome= $db->getQuery(true);
        $subQueryPlayerAway= $db->getQuery(true);
        $subQueryStaffAway= $db->getQuery(true);
        $subQuery1= $db->getQuery(true);
        $subQuery2= $db->getQuery(true);
        $subQuery3= $db->getQuery(true);
        $subQuery4= $db->getQuery(true);
        $subQuery5= $db->getQuery(true);
		// Select some fields
		$query->select('mc.*');
		// From the match table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS mc');
        
        if ( COM_SPORTSMANAGEMENT_USE_NEW_TABLE )
        {
        // join player home
        $subQueryPlayerHome->select('tp.id');
        $subQueryPlayerHome->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ');
        $subQueryPlayerHome->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pthome ON pthome.team_id = tp.team_id');
        $subQueryPlayerHome->where('pthome.id = mc.projectteam1_id');
        $subQueryPlayerHome->where('tp.season_id = '.$this->_season_id);
        $subQueryPlayerHome->where('tp.persontype = 1'); 
        // count match homeplayers
        $subQuery1->select('count(mp.id)');
        $subQuery1->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp  ');
        $subQuery1->where('mp.match_id = mc.id AND (came_in=0 OR came_in=1) AND mp.teamplayer_id in ('.$subQueryPlayerHome.')');
        $query->select('('.$subQuery1.') AS homeplayers_count');
        
        
        // join staff home
        $subQueryStaffHome->select('tp.id');
        $subQueryStaffHome->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ');
        $subQueryStaffHome->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pthome ON pthome.team_id = tp.team_id');
        $subQueryStaffHome->where('pthome.id = mc.projectteam1_id');
        $subQueryStaffHome->where('tp.season_id = '.$this->_season_id);
        $subQueryStaffHome->where('tp.persontype = 2'); 
        // count match homestaff
        $subQuery2->select('count(ms.id)');
        $subQuery2->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff AS ms  ');
        $subQuery2->where('ms.match_id = mc.id AND ms.team_staff_id in ('.$subQueryStaffHome.')');
        $query->select('('.$subQuery2.') AS homestaff_count');
        
        
        // join player away
        $subQueryPlayerAway->select('tp.id');
        $subQueryPlayerAway->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ');
        $subQueryPlayerAway->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pthome ON pthome.team_id = tp.team_id');
        $subQueryPlayerAway->where('pthome.id = mc.projectteam2_id');
        $subQueryPlayerAway->where('tp.season_id = '.$this->_season_id);
        $subQueryPlayerAway->where('tp.persontype = 1'); 
        // count match awayplayers
        $subQuery3->select('count(mp.id)');
        $subQuery3->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp  ');
        $subQuery3->where('mp.match_id = mc.id AND (came_in=0 OR came_in=1) AND mp.teamplayer_id in ('.$subQueryPlayerAway.')');
        $query->select('('.$subQuery3.') AS awayplayers_count');
        
        
        // join staff away
        $subQueryStaffAway->select('tp.id');
        $subQueryStaffAway->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ');
        $subQueryStaffAway->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pthome ON pthome.team_id = tp.team_id');
        $subQueryStaffAway->where('pthome.id = mc.projectteam2_id');
        $subQueryStaffAway->where('tp.season_id = '.$this->_season_id);
        $subQueryStaffAway->where('tp.persontype = 2'); 
        // count match awaystaff
        $subQuery4->select('count(ms.id)');
        $subQuery4->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff AS ms  ');
        $subQuery4->where('ms.match_id = mc.id AND ms.team_staff_id in ('.$subQueryStaffAway.')');
        $query->select('('.$subQuery4.') AS awaystaff_count');
        
           
        }
        else
        {
        // join player home
        $subQueryPlayerHome->select('id');
        $subQueryPlayerHome->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tp ');
        $subQueryPlayerHome->where('tp.projectteam_id = mc.projectteam1_id');
        // join staff home
        $subQueryStaffHome->select('id');
        $subQueryStaffHome->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_staff AS ts ');
        $subQueryStaffHome->where('ts.projectteam_id = mc.projectteam1_id');
        // join player away
        $subQueryPlayerAway->select('id');
        $subQueryPlayerAway->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tp ');
        $subQueryPlayerAway->where('tp.projectteam_id = mc.projectteam2_id');
        // join staff away
        $subQueryStaffAway->select('id');
        $subQueryStaffAway->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_staff AS ts ');
        $subQueryStaffAway->where('ts.projectteam_id = mc.projectteam2_id');
        // count match homeplayers
        $subQuery1->select('count(mp.id)');
        $subQuery1->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp  ');
        $subQuery1->where('mp.match_id = mc.id AND (came_in=0 OR came_in=1) AND mp.teamplayer_id in ('.$subQueryPlayerHome.')');
        $query->select('('.$subQuery1.') AS homeplayers_count');
        // count match homestaffs
        $subQuery2->select('count(ms.id)');
        $subQuery2->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff AS ms  ');
        $subQuery2->where('ms.match_id = mc.id AND ms.team_staff_id in ('.$subQueryStaffHome.')');
        $query->select('('.$subQuery2.') AS homestaff_count');
        // count match awayplayers
        $subQuery3->select('count(mp.id)');
        $subQuery3->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player AS mp  ');
        $subQuery3->where('mp.match_id = mc.id AND (came_in=0 OR came_in=1) AND mp.teamplayer_id in ('.$subQueryPlayerAway.')');
        $query->select('('.$subQuery3.') AS awayplayers_count');
        // count match awaystaffs
        $subQuery4->select('count(ms.id)');
        $subQuery4->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff AS ms  ');
        $subQuery4->where('ms.match_id = mc.id AND ms.team_staff_id in ('.$subQueryStaffAway.')');
        $query->select('('.$subQuery4.') AS awaystaff_count');
        }
        
        // count match referee
        $subQuery5->select('count(mr.id)');
        $subQuery5->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee AS mr ');
        $subQuery5->where('mr.match_id = mc.id');
        $query->select('('.$subQuery5.') AS referees_count');
        
        // Join over the users for the checked out user.
		$query->select('u.name AS editor');
		$query->join('LEFT', '#__users AS u on mc.checked_out = u.id');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pthome ON pthome.id = mc.projectteam1_id');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS ptaway ON ptaway.id = mc.projectteam2_id');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON t1.id = pthome.id');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON t2.id = ptaway.id');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r ON r.id = mc.round_id ');
        $query->select('divaway.id as divawayid');
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_division AS divaway ON divaway.id = ptaway.division_id');
        $query->select('divhome.id as divhomeid'); 
        $query->join('LEFT','#__'.COM_SPORTSMANAGEMENT_TABLE.'_division AS divhome ON divhome.id = pthome.division_id');
                    
        if (self::_buildContentWhere())
		{
        $query->where(self::_buildContentWhere());
        }
		$query->order(self::_buildContentOrderBy());
 
 
         
        
        if ( $show_debug_info )
        {
        $mainframe->enqueueMessage(JText::_('matches query<br><pre>'.print_r($query,true).'</pre>'   ),'');
        $mainframe->enqueueMessage(JText::_('round_id project<br><pre>'.print_r($this->_rid,true).'</pre>'   ),'');
        }
		return $query;
        
	}

	function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
		$filter_order		= $mainframe->getUserStateFromRequest($option .'.'.$this->_identifier. '.mc_filter_order','filter_order','mc.match_date','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option .'.'.$this->_identifier. '.mc_filter_order_Dir','filter_order_Dir','','word');

		if ($filter_order == 'mc.match_number')
		{
			$orderby    = ' mc.match_number +0 '. $filter_order_Dir .', divhome.id, divaway.id ' ;
		}
		elseif ($filter_order == 'mc.match_date')
		{
			$orderby 	= ' mc.match_date '. $filter_order_Dir .', divhome.id, divaway.id ';
		}
		else
		{
			$orderby 	= ' ' . $filter_order . ' ' . $filter_order_Dir . ' , mc.match_date, divhome.id, divaway.id';
		}

		return $orderby;
	}

	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$where=array();
		$mainframe	= JFactory::getApplication();
		// $project_id = $mainframe->getUserState($option . 'project');
		$division	= (int) $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier. '.mc_division', 'division', 0);
		//$round_id = $mainframe->getUserState($option . 'round_id');

		$where[] = ' mc.round_id = ' . $this->_rid ;
		if ($division>0)
		{
			$where[]=' divhome.id = '.$this->_db->Quote($division);
		}
		$where=(count($where) ? ' '.implode(' AND ',$where) : '');
		
		return $where;
	}

  function checkMatchPicturePath($match_id)
  {
  $dest = JPATH_ROOT.'/images/com_sportsmanagement/database/matchreport/'.$match_id;
  $folder = 'matchreport/'.$match_id;
  $this->setState('folder', $folder);
  if(JFolder::exists($dest)) {
  }
  else
  {
  JFolder::create($dest);
  }
  
  }
  
	

	

	function getMatchesByRound($roundId)
	{
		$query = 'SELECT * FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match WHERE round_id='.$roundId;
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

}
?>
