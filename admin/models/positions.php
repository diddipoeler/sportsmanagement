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

jimport('joomla.application.component.modellist');


/**
 * Joomleague Component Teams Model
 *
 * @package	JoomLeague
 * @since	0.1
 */
class sportsmanagementModelPositions extends JModelList
{
	var $_identifier = "positions";
	
	
	
	function getListQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where = $this->_buildContentWhere();
		$orderby=$this->_buildContentOrderBy();
		$query='	SELECT	po.*,
							pop.name AS parent_name,
							st.name AS sportstype,
							u.name AS editor,

							(select count(*) FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_position_eventtype
							WHERE position_id=po.id) countEvents,
							(select count(*) FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_position_statistic
							WHERE position_id=po.id) countStats

					FROM	#__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS po
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type AS st ON st.id=po.sports_type_id
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pop ON pop.id=po.parent_id
					LEFT JOIN #__users AS u ON u.id=po.checked_out ' .
		$where.$orderby;
		return $query;
	}

	function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order',		'filter_order',		'po.ordering',	'cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order_Dir',	'filter_order_Dir',	'',				'word');
		if ($filter_order == 'po.ordering')
		{
			$orderby=' ORDER BY po.parent_id ASC,po.ordering '.$filter_order_Dir;
		}
		else
		{
			$orderby=' ORDER BY po.parent_id ASC,'.$filter_order.' '.$filter_order_Dir.',po.ordering ';
		}
		return $orderby;
	}

	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$filter_sports_type	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_sports_type',	'filter_sports_type','',			'int');
		$filter_state		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_state',		'filter_state',		'',				'word');
		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order',		'filter_order',		'po.ordering',	'cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order_Dir',	'filter_order_Dir',	'',				'word');
		$search				= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search',				'search',			'',				'string');
		$search_mode		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search_mode',		'search_mode',		'',				'string');
		$search=JString::strtolower($search);
		$where=array();
		if ($filter_sports_type> 0)
		{
			$where[]='po.sports_type_id='.$this->_db->Quote($filter_sports_type);
		}
		if ($search)
		{
			if ($search_mode)
			{
				$where[]='LOWER(po.name) LIKE '.$this->_db->Quote($search.'%');
			}
			else
			{
				$where[]='LOWER(po.name) LIKE '.$this->_db->Quote('%'.$search.'%');
			}
		}
		if ($filter_state)
		{
			if ($filter_state == 'P')
			{
				$where[]='po.published=1';
			}
			elseif ($filter_state == 'U')
			{
				$where[]='po.published=0';
			}
		}
		$where=(count($where) ? ' WHERE '.implode(' AND ',$where) : '');
		return $where;
	}

	/**
	 * Method to return the positions array (id,name) 
	 *
	 * @access	public
	 * @return	array
	 * @since 0.1
	 */
	function getParentsPositions()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$project_id=$mainframe->getUserState($option.'project');
		//get positions already in project for parents list
		//support only 2 sublevel, so parent must not have parents themselves
		$query='	SELECT	pos.id AS value,
							pos.name AS text
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos
					WHERE pos.parent_id=0
					ORDER BY pos.ordering ASC 
					';
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return $result;
	}

}
?>