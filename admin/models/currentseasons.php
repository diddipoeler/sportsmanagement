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

//jimport('joomla.application.component.model');
//require_once (JPATH_COMPONENT.DS.'models'.DS.'list.php');
jimport('joomla.application.component.modellist');

/**
 * sportsmanagementModelcurrentseasons
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelcurrentseasons extends JModelList
{
	var $_identifier = "currentseasons";
    
    protected function getListQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where      = $this->_buildContentWhere();
		$orderby    = $this->_buildContentOrderBy();

		$query = '	SELECT	p.*,
							st.name AS sportstype,
							s.name AS season,
							l.name AS league,
                            l.country AS country,
							u.name AS editor
					FROM	#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_season AS s ON s.id = p.season_id
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_league AS l ON l.id = p.league_id
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type AS st ON st.id = p.sports_type_id
					LEFT JOIN #__users AS u ON u.id = p.checked_out ' .
					$where .
					$orderby;

		return $query;
	}
    
    function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();

        $orderby 	= ' ORDER BY p.name ';

		return $orderby;
	}
    
    function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        
        $where = array();
		$filter_season = JComponentHelper::getParams($option)->get('current_season',0);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($filter_season,true).'</pre>'),'');
		}
        
        if ( $filter_season )
        {
        $filter_season = implode(",",$filter_season);
		
		if($filter_season > 0) {
			$where[] = 'p.season_id IN (' . $filter_season .')';
		}

		$where = ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );
        
        }

		return $where;
	}
    
    
}

?>    