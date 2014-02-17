<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/ 

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// import the Joomla modellist library
jimport('joomla.application.component.modellist');


/**
 * sportsmanagementModelProjects
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelProjects extends JModelList
{
	var $_identifier = "projects";
	
	protected function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        //$search	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
        
        
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