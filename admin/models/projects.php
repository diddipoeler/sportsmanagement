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
 * Sportsmanagement Component Seasons Model
 *
 * @package	Sportsmanagement
 * @since	0.1
 */
class sportsmanagementModelProjects extends JModelList
{
	var $_identifier = "projects";
	
	protected function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $search	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
        
        
        // Create a new query object.
        $db = JFactory::getDBO();
		$query = $db->getQuery(true);
        $query->select( array('p.*', 'st.name AS sportstype', 's.name AS season', 'l.name AS league', 'u.name AS editor') )
    ->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p')
    ->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_season AS s ON s.id = p.season_id')
    ->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_league AS l ON l.id = p.league_id')
    ->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type AS st ON st.id = p.sports_type_id')
    ->join('LEFT', '#__users AS u ON u.id = p.checked_out');
  
  $where = self::_buildContentWhere();
  
    if ($where)
		{
        $query->where($where);
        }
		$query->order(self::_buildContentOrderBy());
     
//     $mainframe->enqueueMessage(JText::_('projects query<br><pre>'.print_r($query,true).'</pre>'   ),'');
     
		return $query;
        
        
	}
	
  
  
	function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order','filter_order','p.ordering','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order_Dir','filter_order_Dir','','word');

		if ($filter_order=='p.ordering')
		{
			$orderby=' p.ordering '.$filter_order_Dir;
		}
		else
		{
			$orderby=' '.$filter_order.' '.$filter_order_Dir.',p.ordering ';
		}
		return $orderby;
	}

	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$filter_league		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_league','filter_league','','int');
		$filter_sports_type	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_sports_type','filter_sports_type','','int');
		$filter_season		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_season','filter_season','','int');
		$filter_state		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_state','filter_state','','word');
		$search				= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
		$search_mode		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search_mode','search_mode','','string');
		$search=JString::strtolower($search);
		$where = array();
		
		if($filter_league > 0) {
			$where[] = 'p.league_id = ' . $filter_league;
		}
		if($filter_season > 0) {
			$where[] = 'p.season_id = ' . $filter_season;
		}
		if ($filter_sports_type > 0)
		{
			$where[] = 'p.sports_type_id = ' . $this->_db->Quote($filter_sports_type);
		}
		if ( $search )
		{
			$where[] = 'LOWER(p.name) LIKE ' . $this->_db->Quote( '%' . $search . '%' );
		}

		if ( $filter_state )
		{
			if ( $filter_state == 'P' )
			{
				$where[] = 'p.published = 1';
			}
			elseif ($filter_state == 'U' )
				{
					$where[] = 'p.published = 0';
				}
		}

		$where = ( count( $where ) ? '' . implode( ' AND ', $where ) : '' );

		return $where;
	}
	
	
	
	

	
}
?>