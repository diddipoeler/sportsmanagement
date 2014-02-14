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
 * sportsmanagementModelSeasons
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelSeasons extends JModelList
{
	var $_identifier = "seasons";
	
	protected function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $search	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
        $layout = JRequest::getVar('layout');
        $season_id = JRequest::getVar('id');
        
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        $Subquery = $db->getQuery(true);
        
        switch ($layout)
        {
            case 'assignteams':
            // Select some fields
		    $query->select('t.*');
		    // From the seasons table
		    $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team as t');
            $Subquery->select('stp.team_id');
            $Subquery->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS stp  ');
            $Subquery->where('stp.season_id = '.$season_id);
            
            $query->where('t.id NOT IN ('.$Subquery.')');
            break;
            
            case 'assignpersons':
            // Select some fields
		    $query->select('p.*');
		    // From the seasons table
		    $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person as p');
            
            $Subquery->select('stp.person_id');
            $Subquery->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_person_id AS stp  ');
            $Subquery->where('stp.season_id = '.$season_id);
            
            $query->where('p.id NOT IN ('.$Subquery.')');

                
            break;
            
            default:
            // Select some fields
		    $query->select('s.*');
		    // From the seasons table
		    $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season as s');
            if ($search)
		    {
            $query->where(self::_buildContentWhere());
            }
		    $query->order(self::_buildContentOrderBy());
            break;
        }
		
 
//		$mainframe->enqueueMessage(JText::_('seasons query<br><pre>'.print_r($query,true).'</pre>'   ),'');
        return $query;
	}
	
  
  
	function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order','filter_order','s.ordering','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order_Dir','filter_order_Dir','','word');

		if ($filter_order=='s.ordering')
		{
			$orderby=' s.ordering '.$filter_order_Dir;
		}
		else
		{
			$orderby=' '.$filter_order.' '.$filter_order_Dir.',s.ordering ';
		}
		return $orderby;
	}

	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		//$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order',		'filter_order',		's.ordering',	'cmd');
		//$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order_Dir',	'filter_order_Dir',	'',				'word');
		$search				= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
		$search=JString::strtolower($search);
		$where=array();
		if ($search)
		{
			$where[]=' LOWER(s.name) LIKE '.$this->_db->Quote('%'.$search.'%');
		}
		$where=(count($where) ? '  '.implode(' AND ',$where) : ' ');
		return $where;
	}
	
	/**
     * Method to return a seasons array (id,name)
     *
     * @access	public
     * @return	array seasons
     * @since	1.5.0a
     */
    function getSeasons()
    {
        // Get a db connection.
        $db = JFactory::getDBO();
        // Create a new query object.
        $query = $db->getQuery(true);
        $query->select(array('id', 'name'))
        ->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season')
        ->order('name ASC');

        $db->setQuery($query);
        if (!$result = $db->loadObjectList())
        {
            $this->setError($db->getErrorMsg());
            return array();
        }
        foreach ($result as $season)
        {
            $season->name = JText::_($season->name);
        }
        return $result;
    }
	
	

	
}
?>