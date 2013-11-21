<?php
/**
 * @copyright	Copyright (C) 2013 fussballineuropa.de. All rights reserved.
 * @license		GNU/GPL,see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License,and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');

/**
 * Sportsmanagement Component TeamStaffs Model
 *
 * @author	diddipoeler <kurtnorgaz@web.de>
 * @package	Sportsmanagement
 * @since	1.5.01a
 */
class sportsmanagementModelTeamStaffs extends JModelList
{
	var $_identifier = "teamstaffs";
    var $_project_id = 0;
    var $_team_id = 0;
    var $_project_team_id = 0;

	function getListQuery()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        
        $this->_project_id	= $mainframe->getUserState( "$option.pid", '0' );
        $this->_team_id        = JRequest::getVar('team_id');
        $this->_project_team_id        = JRequest::getVar('project_team_id');
        
        if ( !$this->_team_id )
        {
            $this->_team_id	= $mainframe->getUserState( "$option.team_id", '0' );
        }
        if ( !$this->_project_team_id )
        {
            $this->_project_team_id	= $mainframe->getUserState( "$option.project_team_id", '0' );
        }
        
        // Get the WHERE and ORDER BY clauses for the query
		$where = self::_buildContentWhere();
		$orderby = self::_buildContentOrderBy();
        
        $query->select(array('ppl.firstname',
							'ppl.lastname',
							'ppl.nickname',
							'ts.*',
							'u.name AS editor'))
        ->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS ppl')
        ->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_staff AS ts on ts.person_id = ppl.id')
        ->join('LEFT', '#__users AS u ON u.id = ts.checked_out');

        if ($where)
        {
            $query->where($where);
        }
        if ($orderby)
        {
            $query->order($orderby);
        }
        
        return $query;
	}

	function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		//$filter_order		= $mainframe->getUserStateFromRequest($option.'ts_filter_order',		'filter_order',		'ppl.ordering',	'cmd');
        $filter_order		= $mainframe->getUserStateFromRequest($option.'ts_filter_order','filter_order','ts.ordering','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'ts_filter_order_Dir','filter_order_Dir','','word');
		if ($filter_order=='ppl.lastname')
		{
			$orderby='ppl.lastname '.$filter_order_Dir;
		}
		else
		{
			$orderby=''.$filter_order.' '.$filter_order_Dir.', ppl.lastname ';
		}
		return $orderby;
	}

	function _buildContentWhere()
	{
		$option 		= $option = JRequest::getCmd('option');
		$mainframe		= JFactory::getApplication();
		//$project_id		= $mainframe->getUserState($option.'project');
		//$team_id		= $mainframe->getUserState($option.'project_team_id');
		$filter_state	= $mainframe->getUserStateFromRequest( $option . 'ts_filter_state','filter_state','','word');
		$search			= $mainframe->getUserStateFromRequest($option.'ts_search', 'search','','string');
		$search_mode	= $mainframe->getUserStateFromRequest($option.'ts_search_mode','search_mode','','string');
		$search			= JString::strtolower($search);
		$where=array();
		$where[]='ts.projectteam_id='.$this->_project_team_id;
		$where[]="ppl.published = '1'";
		if ($search)
		{
			if ($search_mode)
			{
				$where[]='LOWER(lastname) LIKE '.$this->_db->Quote($search.'%');
			}
			else
			{
				$where[]='LOWER(lastname) LIKE '.$this->_db->Quote('%'.$search.'%');
			}
		}

		if ( $filter_state )
		{
			if ( $filter_state == 'P' )
			{
				$where[] = 'ts.published = 1';
			}
			elseif ($filter_state == 'U' )
			{
				$where[] = 'ts.published = 0';
			}
		}

		$where=(count($where) ? ''.implode(' AND ',$where) : '');
		return $where;
	}

	

	/**
	 * Method to return the teams array (id,name)
	 *
	 * @access  public
	 * @return  array
	 * @since 0.1
	 */
/*
	function getPersons()
	{
		$query="	SELECT	id AS value,
							lastname,
							nickname,
							firstname,
							info,
							team_id,
							weight,
							height,
							picture,
							birthday,
							position_id,
							notes,
							nickname,
							knvbnr,
							nation
					FROM #__joomleague_person
					WHERE team_id = 0 AND published = '1'
					ORDER BY firstname ASC ";
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return $result;
	}
*/

	/**
	 * Method to return a divisions array (id,name)
	 *
	 * @access  public
	 * @return  array
	 * @since 0.1
	 */
/*
	function getDivisions()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$project_id=$mainframe->getUserState($option.'project');
		$query="SELECT id AS value, name AS text FROM #__joomleague_division WHERE project_id=$project_id ORDER BY name ASC ";
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return $result;
	}
*/
	/**
	 * Method to return a positions array (id,position)
		*
		* @access  public
		* @return  array
		* @since 0.1
		*/
/*
	function getPositions()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$project_id=$mainframe->getUserState($option.'project');
		$query="	SELECT ppos.id AS value, pos.name AS text
					FROM #__joomleague_position AS pos
					INNER JOIN #__joomleague_project_position AS ppos ON ppos.position_id=pos.id
					WHERE ppos.project_id=$project_id AND pos.persontype=2
					ORDER BY ordering ";
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		foreach ($result as $position){
			$position->text=JText::_($position->text);
		}
		return $result;
	}
*/
	/**
	 * return list of project teams for select options
	 *
	 * @return array
	 */
/*
	function getProjectTeamList()
	{
		$query='	SELECT	t.id AS value,
							t.name AS text
					FROM #__joomleague_team AS t
					INNER JOIN  #__joomleague_project_team AS tt ON tt.team_id=t.id
					WHERE tt.project_id='.$this->_project_id;
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
*/

	/**
	 * add the specified persons to team
	 *
	 * @param array int teamstaff ids
	 * @param int team id
	 * @return int number of row inserted
	 */
/*     
	function storeAssigned($cid,$projectteam_id)
	{
		if (!count($cid) || !$projectteam_id){return 0;}
		$query="	SELECT	pt.id
					FROM #__".COM_SPORTSMANAGEMENT_TABLE."_person AS pt
					INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_team_staff AS r ON r.person_id=pt.id
					WHERE r.projectteam_id=".(int)$projectteam_id." AND pt.published = '1'";
		$this->_db->setQuery($query);
		$current=$this->_db->loadResultArray();
		$added=0;
		foreach ($cid AS $pid)
		{
			if (!in_array($pid,$current))
			{
				$tblTeamstaff =& JTable::getInstance('Teamstaff','Table');
				$tblTeamstaff->person_id=$pid;
				$tblTeamstaff->projectteam_id=$projectteam_id;
                // diddipoeler published
                $tblTeamplayer->published		= 1;
                // diddipoeler picture
                $tblPerson =& JTable::getInstance( 'Person', 'Table' );
                $tblPerson->load($pid);

				$tblProjectTeam =& JTable::getInstance( 'Projectteam', 'Table' );
				$tblProjectTeam->load($projectteam_id);

				if (!$tblTeamstaff->check())
				{
					$this->setError($tblTeamstaff->getError());
					continue;
				}
				//Get data from person
				$query = "	SELECT picture, position_id
							FROM #__".COM_SPORTSMANAGEMENT_TABLE."_person AS pl
							WHERE pl.id=". $this->_db->Quote($pid)." pl.published = '1'";
				$this->_db->setQuery( $query );
				$person = $this->_db->loadObject();
				if ( $person )
				{
					$query = "SELECT id FROM #__".COM_SPORTSMANAGEMENT_TABLE."_project_position ";
					$query.= " WHERE position_id = " . $this->_db->Quote($person->position_id);
					$query.= " AND project_id = " . $this->_db->Quote($tblProjectTeam->project_id);
					$this->_db->setQuery($query);
					if ($resPrjPosition = $this->_db->loadObject())
					{
						$tblTeamstaff->project_position_id = $resPrjPosition->id;
					}
					// diddipoeler picture and notes	
					$tblTeamstaff->picture			= $person->picture;
                    $tblTeamstaff->notes			= $person->notes;
					$tblTeamstaff->projectteam_id	= $projectteam_id;
						
				}
				if (!$tblTeamstaff->store())
				{
					$this->setError($tblTeamstaff->getError());
					continue;
				}
				$added++;
			}
		}
		return $added;
	}
*/
	/**
	 * remove staffs from team
	 * @param $cids staff ids
	 * @return int count of staffs removed
	 */
	function remove($cids)
	{
		$count=0;
		foreach ($cids as $cid)
		{
			$object=&$this->getTable('teamstaff');
			if ($object->canDelete($cid) && $object->delete($cid))
			{
				$count++;
			}
			else
			{
				$this->setError(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TEAMSTAFFS_MODEL_ERROR_REMOVE_STAFF',$object->getError()));
			}
		}
		return $count;
	}

}
?>