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

// import the Joomla modellist library
jimport('joomla.application.component.modellist');

/**
 * Sportsmanagement Component Clubs Model
 *
 * @package	Sportsmanagement
 * @since	0.1
 */
class sportsmanagementModelClubs extends JModelList
{
	var $_identifier = "clubs";
	
	
    protected function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $search	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
        //$mainframe->enqueueMessage(JText::_('clubs getListQuery search<br><pre>'.print_r($search,true).'</pre>'   ),'');
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		// Select some fields
		$query->select('a.*');
		// From the club table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_club as a');
        if ($search)
		{
        $query->where(self::_buildContentWhere());
        }
		$query->order(self::_buildContentOrderBy());
        

        //$mainframe->enqueueMessage(JText::_('clubs query<br><pre>'.print_r($query,true).'</pre>'   ),'');
		return $query;
	}



	function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order','filter_order','a.ordering','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order_Dir','filter_order_Dir','','word');
		if ($filter_order == 'a.ordering')
		{
			$orderby=' a.ordering '.$filter_order_Dir;
		}
		else
		{
			$orderby=' '.$filter_order.' '.$filter_order_Dir.',a.ordering ';
		}
		return $orderby;
	}

	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$filter_state		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_state','filter_state','','word');
		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order','filter_order','a.ordering','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order_Dir','filter_order_Dir','','word');
		$search				= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
		$search_mode		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search_mode','search_mode','','string');
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
		$where=(count($where) ? ' '. implode(' AND ',$where) : ' ');
		return $where;
	}

	
}
?>
