<?php
/**
 * @copyright	Copyright (C) 2006-2013 JoomLeague.net. All rights reserved.
 * @license		GNU/GPL,see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License,and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
require_once (JPATH_COMPONENT.DS.'models'.DS.'list.php');

/**
 * Joomleague Component projectreferees Model
 *
 * @author	Kurt Norgaz
 * @package	JoomLeague
 * @since	1.5.02a
*/
class sportsmanagementModelProjectReferees extends JModelList
{
	var $_identifier = "preferees";

	function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where=$this->_buildContentWhere();
		$orderby=$this->_buildContentOrderBy();
        
        // Create a new query object.
        $query = $this->_db->getQuery(true);
        $query->select(array('p.firstname',
				'p.lastname',
				'p.nickname',
				'p.phone',
				'p.email',
				'p.mobile',
				'pref.*',
				'pref.project_position_id',
				'u.name AS editor',
				'pref.picture'))
        ->from('#__joomleague_person AS p')
        ->join('INNER', '#__joomleague_project_referee AS pref on pref.person_id=p.id')
        ->join('LEFT', '#__users AS u ON u.id = pref.checked_out');

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
		$filter_order		= $mainframe->getUserStateFromRequest($option.'p_filter_order',		'filter_order',		'p.lastname',	'cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'p_filter_order_Dir',	'filter_order_Dir',	'',				'word');
		if ($filter_order=='p.lastname')
		{
			$orderby='p.lastname '.$filter_order_Dir;
		}
		else
		{
			$orderby=''.$filter_order.' '.$filter_order_Dir.', p.lastname ';
		}
		return $orderby;
	}

	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$project_id=$mainframe->getUserState($option.'project');
		$search			= $mainframe->getUserStateFromRequest($option.'p_search',		'search',		'',		'string');
		$search_mode	= $mainframe->getUserStateFromRequest($option.'p_search_mode',	'search_mode',	'',		'string');
		$search=JString::strtolower($search);
		$where=array();
		$where[]='pref.project_id='.$project_id;
		$where[]='p.published = 1';
		if ($search)
		{
			if ($search_mode)
			{
				$where[] =	'(LOWER(p.lastname) LIKE '.$this->_db->Quote($search.'%') .
				'OR LOWER(p.firstname) LIKE '.$this->_db->Quote($search.'%') .
				'OR LOWER(p.nickname) LIKE '.$this->_db->Quote($search.'%').')';
			}
			else
			{
				$where[] =	'(LOWER(p.lastname) LIKE '.$this->_db->Quote('%'.$search.'%').
				'OR LOWER(p.firstname) LIKE '.$this->_db->Quote('%'.$search.'%') .
				'OR LOWER(p.nickname) LIKE '.$this->_db->Quote('%'.$search.'%').')';
			}
		}
		$where=(count($where) ? ''.implode(' AND ',$where) : '');
		return $where;
	}

	/**
	 * Method to update checked project teams
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	function saveshort($cid,$data)
	{
		$result=true;
		$record = JTable::getInstance('ProjectReferee', 'Table');
		for ($x=0; $x < count($cid); $x++)
		{
			$record->id = $cid[$x];
			$record->project_position_id = $data['project_position_id'.$cid[$x]];
			$record->store();
			if (!$record->check())
			{
				$this->setError($record->getError());
				$result=false;
			}
			if (!$record->store())
			{
				$this->setError($record->getError());
				$result=false;
			}
		}
		return $result;
	}

	/**
	 * Method to return the players array (projectid,teamid)
	 *
	 * @access  public
	 * @return  array
	 * @since 0.1
	 */
	function getPersons()
	{
		$query='	SELECT	id AS value,
				lastname,
				firstname,
				info,
				weight,
				height,
				picture,
				birthday,
				notes,
				nickname,
				knvbnr,
				country,
				phone,
				mobile,
				email
				FROM #__joomleague_person
				WHERE published = 1
				ORDER BY lastname ASC ';
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return $result;
	}

	/**
	 * Method to return a positions array (id,position)
		*
		* @access  public
		* @return  array
		* @since 0.1
		*/
	function getPositions()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$project_id=$mainframe->getUserState($option.'project');
		$query='	SELECT	pp.id AS value,
				name AS text

				FROM #__joomleague_position AS p
				LEFT JOIN #__joomleague_project_position AS pp ON pp.position_id=p.id
				WHERE pp.project_id='.$project_id.'
						ORDER BY ordering ';
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		else
		{
			foreach ($result as $position) {
				$position->text=JText::_($position->text);
			}
			return $result;
		}
	}

	/**
	 * Method to return a positions array of referees (id,position)
	 *
	 * @access	public
	 * @return	array
	 *
	 */
	function getRefereePositions()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$project_id=$mainframe->getUserState($option.'project');
		$query='	SELECT	ppos.id AS value,
				pos.name AS text
				FROM #__joomleague_position AS pos
				INNER JOIN #__joomleague_project_position AS ppos ON pos.id=ppos.position_id
				WHERE ppos.project_id='. $this->_db->Quote($project_id).' AND pos.persontype=3';
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		else
		{
			foreach ($result as $position) {
				$position->text=JText::_($position->text);
			}
			return $result;
		}
	}

	/**
	 * add the specified persons to team
	 *
	 * @param array int person ids
	 * @return int number of row inserted
	 */
	function storeAssigned($cid,$project_id)
	{
		if (!count($cid)){
			return 0;
		}
		$query='	SELECT	pt.id
				FROM #__joomleague_person AS pt
				INNER JOIN #__joomleague_project_referee AS r ON r.person_id=pt.id
				WHERE r.project_id='.$this->_db->Quote($this->_project_id).'
						AND pt.published = 1';
		$this->_db->setQuery($query);
		$current=$this->_db->loadResultArray();
		$added=0;
		foreach ($cid AS $pid)
		{
			if ((!isset($current)) || (!in_array($pid,$current)))
			{
				$new =& JTable::getInstance('ProjectReferee','Table');
				$new->person_id=$pid;
				$new->project_id=$this->_project_id;
				if (!$new->check())
				{
					$this->setError($new->getError());
					continue;
				}
				//Get data from person
				$query="SELECT picture FROM #__joomleague_person AS pl WHERE pl.id='$pid' AND pl.published = 1";
				$this->_db->setQuery($query);
				$player=$this->_db->loadObject();
				if ($player)
				{
					$new->picture=$player->picture;
				}
				if (!$new->store())
				{
					$this->setError($new->getError());
					continue;
				}
				$added++;
			}
		}
		return $added;
	}

	/**
	 * remove the specified projectreferees from project
	 *
	 * @param array projectreferee ids
	 * @return int number of row removed
	 */
	function unassign($cid)
	{
		if (!count($cid)){
			return 0;
		}
		$removed=0;
		for ($x=0; $x < count($cid); $x++)
		{
			$query='DELETE FROM #__joomleague_project_referee WHERE id='.$cid[$x];
			$this->_db->setQuery($query);
			if(!$this->_db->query())
			{
				$this->setError($this->_db->getErrorMsg());
				continue;
			}
			$removed++;
		}
		return $removed;
	}

	/**
	 * return count of projectreferees
	 *
	 * @param int project_id
	 * @return int
	 */
	function getProjectRefereesCount($project_id)
	{
		$query='SELECT count(*) AS count
				FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS pr
				JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p on p.id = pr.project_id
				WHERE p.id='.$project_id;
		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}


}
?>