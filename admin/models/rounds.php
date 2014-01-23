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
 * sportsmanagementModelRounds
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelRounds extends JModelList
{
	var $_identifier = "rounds";
    var $_project_id = 0;
	
	protected function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info_'.$this->_identifier,0) ;
        //$search	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
        
        //$this->_project_id	= JRequest::getVar('pid');
        $this->_project_id	= $mainframe->getUserState( "$option.pid", '0' );
        
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        $subQuery1= $db->getQuery(true);
        $subQuery2= $db->getQuery(true);
        $subQuery3= $db->getQuery(true);
        
		// Select some fields
		$query->select('r.*');
		// From the rounds table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round as r');
        // join match
        $subQuery1->select('count(published)');
        $subQuery1->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match ');
        $subQuery1->where('round_id=r.id and published=0');
        // join match
        $subQuery2->select('count(*)');
        $subQuery2->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match ');
        $subQuery2->where('round_id=r.id AND cancel=0 AND (team1_result is null OR team2_result is null)');
        // join match
        $subQuery3->select('count(*)');
        $subQuery3->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match ');
        $subQuery3->where('round_id=r.id');
        
        $query->select('('.$subQuery1.') AS countUnPublished');
        $query->select('('.$subQuery2.') AS countNoResults');
        $query->select('('.$subQuery3.') AS countMatches');
        
        
       
        
       
        $query->where(self::_buildContentWhere());
		$query->order(self::_buildContentOrderBy());
        
        if ( $show_debug_info )
        {
 		$mainframe->enqueueMessage(JText::_('sportsmanagementModelRounds query<br><pre>'.print_r($query,true).'</pre>'   ),'');
        $mainframe->enqueueMessage(JText::_('sportsmanagementModelRounds project<br><pre>'.print_r($this->_project_id,true).'</pre>'   ),'');
        }
        
        return $query;
	}
	
  
  
	function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order','filter_order','r.ordering','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order_Dir','filter_order_Dir','','word');

		if ( $filter_order == 'r.ordering' )
		{
			$orderby = ' r.ordering '.$filter_order_Dir;
		}
		else
		{
			$orderby = ' '.$filter_order.' '.$filter_order_Dir.',r.ordering ';
		}
		return $orderby;
	}

	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		//$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order',		'filter_order',		'r.ordering',	'cmd');
		//$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.filter_order_Dir',	'filter_order_Dir',	'',				'word');
		$search				= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
		$search=JString::strtolower($search);
		$where=array();
        $where[]=' r.project_id = '.$this->_project_id;
		if ($search)
		{
			$where[]=' LOWER(r.name) LIKE '.$this->_db->Quote('%'.$search.'%');
		}
		$where=(count($where) ? '  '.implode(' AND ',$where) : ' ');
		return $where;
	}
	
	/**
	 * return count of  project rounds
	 *
	 * @param int project_id
	 * @return int
	 */
	function getRoundsCount($project_id)
	{
	   // Get a db connection.
        $db = JFactory::getDbo();
		$query='SELECT count(*) AS count
				  FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_round
				  WHERE project_id='.$project_id;
		$db->setQuery($query);
		return $db->loadResult();
	}
    
    /**
	 * 
	 * @param int $projectid
	 * @return assocarray
	 */
	function getFirstRound($projectid) 
    {
        // Get a db connection.
        $db = JFactory::getDbo();
		$query="	SELECT	id, roundcode
					FROM #__".COM_SPORTSMANAGEMENT_TABLE."_round
					WHERE project_id=".$projectid."
					ORDER BY roundcode ASC, id ASC ";
		$db->setQuery($query);
		if (!$result=$db->loadAssocList ())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
			return false;
		}
		return $result[0];
	}
	
	/**
	 * 
	 * @param int $projectid
	 * @return assocarray
	 */
	function getLastRound($projectid) 
    {
        // Get a db connection.
        $db = JFactory::getDbo();
		$query="	SELECT	id, roundcode
					FROM #__".COM_SPORTSMANAGEMENT_TABLE."_round
					WHERE project_id=".$projectid."
					ORDER BY roundcode DESC, id DESC ";
		$db->setQuery($query);
		if (!$result=$db->loadAssocList())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
			return false;
		}
		return $result[0];
	}
    
    /**
	 * 
	 * @param int $roundid
	 * @param int $projectid
	 * @return assocarray
	 */
	function getPreviousRound($roundid, $projectid) 
    {
        // Get a db connection.
        $db = JFactory::getDbo();
		$query="	SELECT	id, roundcode
					FROM #__".COM_SPORTSMANAGEMENT_TABLE."_round
					WHERE project_id=".$projectid."
					ORDER BY id ASC ";
		$db->setQuery($query);
		if (!$result=$db->loadAssocList())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
			return false;
		}
		for ($i=0,$n=count($result); $i < $n; $i++) {
			if(isset($result[$i-1])) {
				return $result[$i-1];
			} else {
				return $result[$i];
			}
		}
	}
    
    /**
	 * 
	 * @param int $roundid
	 * @param int $projectid
	 * @return assocarray
	 */
	function getNextRound($roundid, $projectid) 
    {
        // Get a db connection.
        $db = JFactory::getDbo();
		$query="	SELECT	id, roundcode
					FROM #__".COM_SPORTSMANAGEMENT_TABLE."_round
					WHERE project_id=".$projectid."
					ORDER BY id ASC ";
		$db->setQuery($query);
		if (!$result=$db->loadAssocList())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
			return false;
		}
		for ($i=0,$n=count($result); $i < $n; $i++) {
			if($result[$i]['id']==$roundid) {
				if(isset($result[$i+1])) {
					return $result[$i+1];
				} else {
					return $result[$i];
				}
			}
		}
	}
    
    /**
	 * Get the next round by todays date
	 * @param int $project_id
	 * @return assocarray
	 */
	function getNextRoundByToday($projectid)
	{
	   // Get a db connection.
        $db = JFactory::getDbo();
		$query = ' SELECT r.id, r.roundcode, r.round_date_first , r.round_date_last '
		       . ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_round AS r '
		       . ' WHERE project_id = '. $db->Quote($projectid)
		       . '   AND DATEDIFF(CURDATE(), DATE(r.round_date_first)) < 0'
		       . ' ORDER BY r.round_date_first ASC '
		            ;
		$db->setQuery($query);
		if (!$result=$db->loadAssocList())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
			return false;
		}
		return $result;		
	}
    
    /**
	 * return project rounds as array of objects(roundid as value, name as text)
	 *
	 * @param string $ordering
	 * @return array
	 */
	function getRoundsOptions($project_id, $ordering='ASC')
	{
	   $option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelRounds getRoundsOptions project_id<br><pre>'.print_r($project_id,true).'</pre>'   ),'');
        
       // Get a db connection.
        $db = JFactory::getDbo();
		$query="SELECT
					id as value,
				    CASE LENGTH(name)
				    	when 0 then CONCAT('".JText::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAY_NAME'). "',' ', id)
				    	else name
				    END as text, id, name, round_date_first, round_date_last, roundcode 
				  FROM #__".COM_SPORTSMANAGEMENT_TABLE."_round
				  WHERE project_id=".$project_id."
				  ORDER BY roundcode ".$ordering;

		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	

	
}
?>