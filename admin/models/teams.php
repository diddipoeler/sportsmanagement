<?php
/**
 * @copyright	Copyright (C) 2006-2013 JoomLeague.net. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.modellist');
//require_once (JPATH_COMPONENT.DS.'models'.DS.'list.php');

/**
 * Joomleague Component Teams Model
 *
 * @package		Joomleague
 * @since 0.1
 */
class sportsmanagementModelTeams extends JModelList
{
	var $_identifier = "teams";
	
	function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $search	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
        // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser(); 
		
        // Select some fields
		$query->select('t.*');
        // From table
		$query->from('#__sportsmanagement_team AS t');
        // Join over the clubs
		$query->select('c.name As clubname');
		$query->join('LEFT', '#__sportsmanagement_club AS c ON c.id = t.club_id');
        // Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = t.checked_out');
        
        
        if ($search)
		{
        $query->where(self::_buildContentWhere());
        }
		$query->order(self::_buildContentOrderBy());
        
        //$mainframe->enqueueMessage(JText::_('teams query<br><pre>'.print_r($query,true).'</pre>'   ),'');
		return $query;
        
        
        
	}

	function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();

		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.'.$this->_identifier.'.filter_order','filter_order','t.ordering','cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.'.$this->_identifier.'.filter_order_Dir','filter_order_Dir','','word' );

		if ($filter_order == 't.ordering'){
			$orderby 	= '  t.ordering '.$filter_order_Dir;
		} else {
			$orderby 	= '  '.$filter_order.' '.$filter_order_Dir.' , t.ordering ';
		}

		return $orderby;
	}
	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();

		$filter_state		= $mainframe->getUserStateFromRequest( $option.'.'.$this->_identifier.'.filter_state',		'filter_state',		'',				'word' );
		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.'.$this->_identifier.'.filter_order',		'filter_order',		't.ordering',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.'.$this->_identifier.'.filter_order_Dir',	'filter_order_Dir',	'',				'word' );
		$search			= $mainframe->getUserStateFromRequest( $option.'.'.$this->_identifier.'.search',			'search',			'',				'string' );
		$search_mode		= $mainframe->getUserStateFromRequest( $option.'.'.$this->_identifier.'.search_mode',			'search_mode',			'',				'string' );
		$search			= JString::strtolower( $search );

		$where = array();

		if ($search) {
			if($search_mode)
				$where[] = 'LOWER(t.name) LIKE '.$this->_db->Quote($search.'%');
			else
				$where[] = 'LOWER(t.name) LIKE '.$this->_db->Quote('%'.$search.'%');
		}

                if ($cid    =   JRequest::getvar('cid', 0, 'GET', 'INT')) {
                    $where[] = 'club_id ='. $cid;
                }

                $where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
		return $where;
	}
}
?>
