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
jimport('joomla.application.component.modellist');


/**
 * sportsmanagementModelDivisions
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelDivisions extends JModelList
{
	var $_identifier = "divisions";
    var $_project_id = 0;
	
	protected function getListQuery()
	{
		$mainframe	= JFactory::getApplication();
		$option = JRequest::getCmd('option');
        $this->_project_id	= $mainframe->getUserState( "$option.pid", '0' );
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelDivisions _project_id<br><pre>'.print_r($this->_project_id,true).'</pre>'),'Notice');
        
        // Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();
        // Create a new query object.
        $query = $this->_db->getQuery(true);
        $query->select(array('dv.*', 'dvp.name AS parent_name','u.name AS editor'))
        ->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_division AS dv')
        ->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_division AS dvp ON dvp.id = dv.parent_id')
        ->join('LEFT', '#__users AS u ON u.id = dv.checked_out');

        //if ($where)
        //{
            $query->where($where);
        //}
        if ($orderby)
        {
            $query->order($orderby);
        }


		return $query;
	}

	function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
		$filter_order		= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier. 'dv_filter_order','filter_order','dv.ordering','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier. 'dv_filter_order_Dir','filter_order_Dir','','word');

		if ( $filter_order == 'dv.ordering' )
		{
			$orderby 	= 'dv.ordering ' . $filter_order_Dir;
		}
		else
		{
			$orderby 	= '' . $filter_order . ' '.$filter_order_Dir . ' , dv.ordering ';
		}

		return $orderby;
	}

	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
 		$mainframe	= JFactory::getApplication();
		//$project_id = $mainframe->getUserState( $option . 'project' );
		$where = array();

		$where[]	= ' dv.project_id = ' . $this->_project_id;

		$filter_order		= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier. 'dv_filter_order','filter_order','dv.ordering','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier. 'dv_filter_order_Dir','filter_order_Dir','','word');
		$search				= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier. 'dv_search','search','','string');
		$search				= JString::strtolower( $search );

		if ( $search )
		{
			$where[] = 'LOWER(dv.name) LIKE ' . $this->_db->Quote( '%' . $search . '%' );
		}


		$where = ( count( $where ) ? '' . implode( ' AND ', $where ) : '' );

		return $where;
	}
	
	/**
	* Method to return a divisions array (id, name)
	*
	* @param int $project_id
	* @access  public
	* @return  array
	* @since 0.1
	*/
	function getDivisions($project_id)
	{
		$query = '	SELECT	id AS value,
					name AS text
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_division
					WHERE project_id=' . $project_id .
					' ORDER BY name ASC ';

		$this->_db->setQuery( $query );
		if ( !$result = $this->_db->loadObjectList("value") )
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
			return array();
		}
		else
		{
			return $result;
		}
		
	}
	
	/**
	 * return count of project divisions
	 *
	 * @param int project_id
	 * @return int
	 */
	function getProjectDivisionsCount($project_id)
	{
		$query='SELECT count(*) AS count
		FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_division AS d
		JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p on p.id = d.project_id
		WHERE p.id='.$project_id;
		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}
	
}
?>