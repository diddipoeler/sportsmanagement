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

// import the Joomla modellist library
jimport('joomla.application.component.modellist');

//jimport('joomla.application.component.model');
//require_once (JPATH_COMPONENT.DS.'models'.DS.'list.php');

/**
 * Joomleague Component Clubs Model
 *
 * @package	JoomLeague
 * @since	0.1
 */
class sportsmanagementModelClubs extends JModelList
{
	var $_identifier = "clubs";
	
	
    protected function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		// Select some fields
		$query->select('a.*');
		// From the hello table
		$query->from('#__sportsmanagement_club as a');
        $query->where(self::_buildContentWhere());
		$query->order(self::_buildContentOrderBy());
        
$mainframe->enqueueMessage(JText::_('getListQuery _buildContentWhere<br><pre>'.print_r(self::_buildContentWhere(),true).'</pre>'   ),'');
$mainframe->enqueueMessage(JText::_('getListQuery _buildContentOrderBy<br><pre>'.print_r(self::_buildContentOrderBy(),true).'</pre>'   ),'');        


//        self::_buildContentWhere();
//        self::_buildContentOrderBy();
        
		return $query;
	}

/*    
    function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where=$this->_buildContentWhere();
		$orderby=$this->_buildContentOrderBy();
		$query='	SELECT a.*,u.name AS editor
					FROM #__sportsmanagement_club AS a
					LEFT JOIN #__users AS u ON u.id=a.checked_out '
					. $where
					. $orderby;
		return $query;
	}
*/

	function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$filter_order		= $mainframe->getUserStateFromRequest($option.'a_filter_order','filter_order','a.ordering','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'a_filter_order_Dir','filter_order_Dir','','word');
		if ($filter_order == 'a.ordering')
		{
			$orderby=' ORDER BY a.ordering '.$filter_order_Dir;
		}
		else
		{
			$orderby=' ORDER BY '.$filter_order.' '.$filter_order_Dir.',a.ordering ';
		}
		return $orderby;
	}

	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$filter_state		= $mainframe->getUserStateFromRequest($option.'a_filter_state',		'filter_state',		'',				'word');
		$filter_order		= $mainframe->getUserStateFromRequest($option.'a_filter_order',		'filter_order',		'a.ordering',	'cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'a_filter_order_Dir',	'filter_order_Dir',	'',				'word');
		$search				= $mainframe->getUserStateFromRequest($option.'a_search',			'search',			'',				'string');
		$search_mode		= $mainframe->getUserStateFromRequest($option.'a_search_mode',		'search_mode',		'',				'string');
		$search				= JString::strtolower($search);
		$where=array();
		if ($search)
		{
			if($search_mode)
			{
				$where[]='LOWER(a.name) LIKE '.$this->_db->Quote($search.'%');
			}
			else
			{
				$where[]='LOWER(a.name) LIKE '.$this->_db->Quote('%'.$search.'%');
			}
		}
		if ($filter_state)
		{
			if ($filter_state == 'P')
			{
				$where[]='a.published=1';
			}
			elseif ($filter_state == 'U')
			{
				$where[]='a.published=0';
			}
		}
		$where=(count($where) ? ' WHERE '. implode(' AND ',$where) : '');
		return $where;
	}

	/**
	 * Method to remove a club
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	/*
  function delete($cid=array())
	{
		return false;
		$result=false;
		if (count($cid))
		{
			JArrayHelper::toInteger($cid);
			$cids=implode(',',$cid);
			$query="DELETE FROM #__joomleague_club WHERE id IN ($cids)";
			$this->_db->setQuery($query);
			if(!$this->_db->query())
			{
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return true;
	}
  */
}
?>
