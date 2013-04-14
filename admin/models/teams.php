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
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();

		$query = ' SELECT c.name as clubname, t.*, u.name AS editor '
			. ' FROM #__sportsmanagement_team AS t '
			. ' LEFT JOIN #__sportsmanagement_club AS c '
			. ' ON t.club_id = c.id'
			. ' LEFT JOIN #__users AS u ON u.id = t.checked_out '
			. $where
			. $orderby
		;
		return $query;
	}

	function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();

		$filter_order		= $mainframe->getUserStateFromRequest( $option.'t_filter_order','filter_order','t.ordering','cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'t_filter_order_Dir','filter_order_Dir','','word' );

		if ($filter_order == 't.ordering'){
			$orderby 	= ' ORDER BY t.ordering '.$filter_order_Dir;
		} else {
			$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir.' , t.ordering ';
		}

		return $orderby;
	}
	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();

		$filter_state		= $mainframe->getUserStateFromRequest( $option.'t_filter_state',		'filter_state',		'',				'word' );
		$filter_order		= $mainframe->getUserStateFromRequest( $option.'t_filter_order',		'filter_order',		't.ordering',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'t_filter_order_Dir',	'filter_order_Dir',	'',				'word' );
		$search			= $mainframe->getUserStateFromRequest( $option.'t_search',			'search',			'',				'string' );
		$search_mode		= $mainframe->getUserStateFromRequest( $option.'t_search_mode',			'search_mode',			'',				'string' );
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
