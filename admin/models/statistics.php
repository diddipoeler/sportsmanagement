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
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.modellist' );
//require_once( JPATH_COMPONENT . DS . 'models' . DS . 'list.php' );


/**
 * sportsmanagementModelStatistics
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelStatistics extends JModelList
{
	var $_identifier = "statistics";
	
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
		$query->select('obj.*');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_statistic AS obj');
        // Join over the sportstype
		$query->select('st.name AS sportstype');
		$query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type AS st ON st.id = obj.sports_type_id	');
        // Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = obj.checked_out');
        
        
        if (self::_buildContentWhere())
		{
        $query->where(self::_buildContentWhere());
        }
        
		$query->order(self::_buildContentOrderBy());
        
        //$mainframe->enqueueMessage(JText::_('statistics query<br><pre>'.print_r($query,true).'</pre>'   ),'');
		return $query;
        
        
        
        
	}

	function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order','filter_order','obj.ordering','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order_Dir','filter_order_Dir','','word');
		
		if ( $filter_order == 'obj.ordering')
		{
			$orderby 	= ' obj.ordering ' . $filter_order_Dir;
		} else
		{
			$orderby 	= '  ' . $filter_order . ' ' . $filter_order_Dir . ' , obj.ordering ';
		}

		return $orderby;
	}

	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$filter_sports_type	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_sports_type','filter_sports_type','','int');
		$filter_state		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_state','filter_state','','word');
		$search				= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
		$search_mode		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search_mode','search_mode','','string');
		$search=JString::strtolower($search);
		
		$where = array();
		if ($filter_sports_type > 0)
		{
			$where[]='obj.sports_type_id='.$this->_db->Quote($filter_sports_type);
		}
		
		if ( $search )
		{
			$where[] = 'LOWER(obj.name) LIKE ' . $this->_db->Quote('%' . $search . '%');
		}
		if ( $filter_state )
		{
			if ( $filter_state == 'P' )
			{
				$where[] = 'obj.published = 1';
			}
			elseif ( $filter_state == 'U' )
			{
				$where[] = 'obj.published = 0';
			}
		}

		$where	= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );

		return $where;
	}
    
    /**
	* Method to return the position stats array (value,text)
	*
	* @access  public
	* @return  array
	* @since 0.1
	*/
	function getPositionStatsOptions($id)
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        //$search	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
        // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
        
		$query=' SELECT	s.id AS value,
				concat(s.name, " (" , st.name, ")") AS text
				FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_statistic AS s 
				INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position_statistic AS ps 
                ON ps.statistic_id=s.id 
				LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type AS st 
                ON st.id = s.sports_type_id 
				WHERE ps.position_id='.(int) $id . '
				ORDER BY ps.ordering ASC ';

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
    
    /**
	* Method to return the stats not yet assigned to position (value,text)
	*
	* @access  public
	* @return  array
	* @since 0.1
	*/
	function getAvailablePositionStatsOptions($id)
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        //$search	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
        // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
        
		$query=' SELECT	s.id AS value, 
				concat(s.name, " (" , st.name, ")") AS text 
				FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_statistic AS s 
				LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position_statistic AS ps 
                ON ps.statistic_id = s.id 
				AND ps.position_id !='.(int) $id . '
				LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type AS st 
                ON st.id = s.sports_type_id 
				WHERE ps.id IS NULL 
				ORDER BY s.ordering ASC ';

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
    
    
    public function getStatisticListSelect()
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        //$search	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
        // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
        
		$query='SELECT id,name,id AS value,name AS text,short,class,note FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_statistic ORDER BY name';
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
    
    
    

}
?>