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
//require_once (JPATH_COMPONENT.DS.'models'.DS.'list.php');

/**
 * Sportsmanagement Component sportstypes Model
 *
 * @package	Sportsmanagement
 * @since	1.5
 */
class sportsmanagementModelSportsTypes extends JModelList
{
	var $_identifier = "sportstypes";
    //var $setError = '';

	function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        //$search	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
        // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser(); 
  
        // Select some fields
		$query->select('s.*');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type AS s');
        $query->join('LEFT', '#__users AS uc ON uc.id = s.checked_out');
        if (self::_buildContentWhere())
		{
        $query->where(self::_buildContentWhere());
        }
		$query->order(self::_buildContentOrderBy());
        
        //$mainframe->enqueueMessage(JText::_('sportstypes query<br><pre>'.print_r($query,true).'</pre>'   ),'');
		return $query;
        
	}

	function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order',		'filter_order',		's.ordering',	'cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order_Dir',	'filter_order_Dir',	'',				'word');
		if ($filter_order == 's.ordering')
		{
			$orderby='  s.ordering '.$filter_order_Dir;
		}
		else
		{
			$orderby='  '.$filter_order.' '.$filter_order_Dir.',s.ordering ';
		}
		return $orderby;
	}

	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order',		'filter_order',		's.ordering',	'cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order_Dir',	'filter_order_Dir',	'',				'word');
		$search				= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search',			'search',			'',				'string');
		$search=JString::strtolower($search);
		$where=array();
		if ($search)
		{
			$where[]='LOWER(s.name) LIKE '.$this->_db->Quote('%'.$search.'%');
		}
		$where=(count($where) ? ' WHERE '.implode(' AND ',$where) : '');
		return $where;
	}

	/**
	 * Method to return a sportsTypes array (id,name)
	 *
	 * @access	public
	 * @return	array
	 * @since	1.5.0a
	 */
	public static function getSportsTypes()
	{
		$db = JFactory::getDBO();
		$query='SELECT id, name FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type ORDER BY name ASC ';
		$db->setQuery($query);
		if (!$result=$db->loadObjectList())
		{
			$this->setError($db->getErrorMsg());
			return array();
		}
		foreach ($result as $sportstype){
			$sportstype->name=JText::_($sportstype->name);
		}
		return $result;
	}

}
?>