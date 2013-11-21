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
 * Sportsmanagement Component projectreferees Model
 *
 * @author	diddipoeler
 * @package	Sportsmanagement
 * @since	1.5.02a
*/
class sportsmanagementModelProjectReferees extends JModelList
{
	var $_identifier = "preferees";
    var $_project_id = 0;

	protected function getListQuery()
	{
		$mainframe	= JFactory::getApplication();
		$option = JRequest::getCmd('option');
        $this->_project_id	= $mainframe->getUserState( "$option.pid", '0' );
        // Get the WHERE and ORDER BY clauses for the query
		$where = $this->_buildContentWhere();
		$orderby = $this->_buildContentOrderBy();
        
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
        ->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p')
        ->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS pref on pref.person_id=p.id')
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
		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'p_filter_order','filter_order','p.lastname','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'p_filter_order_Dir','filter_order_Dir','','word');
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
		
		$search			= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'p_search','search','','string');
		$search_mode	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'p_search_mode','search_mode','','string');
		$search=JString::strtolower($search);
		$where=array();
		$where[]='pref.project_id='.$this->_project_id;
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
				FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pt
				INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS r ON r.person_id=pt.id
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
				$query="SELECT picture FROM #__".COM_SPORTSMANAGEMENT_TABLE."_person AS pl WHERE pl.id='$pid' AND pl.published = 1";
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
			$query='DELETE FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee WHERE id='.$cid[$x];
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