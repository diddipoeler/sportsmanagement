<?php
/** Joomla Sports Management ein Programm zur Verwaltung für alle Sportarten
* @version 1.0.26
* @file		administrator/components/sportsmanagement/models/divisions.php
* @author diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license This file is part of Joomla Sports Management.
*
* Joomla Sports Management is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Joomla Sports Management is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Joomla Sports Management. If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von Joomla Sports Management.
*
* Joomla Sports Management ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* Joomla Sports Management wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
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

/**
 * sportsmanagementModelDivisions
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelDivisions extends JSMModelList
{
	var $_identifier = "divisions";
    static  $_project_id = 0;
	
    /**
     * sportsmanagementModelDivisions::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'dv.name',
                        'dv.alias',
                        'dv.id',
                        'dv.ordering',
                        'dv.picture'
                        );
                parent::__construct($config);
                $getDBConnection = sportsmanagementHelper::getDBConnection();
                parent::setDbo($getDBConnection);
                self::$_project_id	= $this->jsmjinput->getInt('pid',0);
                if ( !self::$_project_id )
                {
                self::$_project_id	= $this->jsmapp->getUserState( "$this->jsmoption.pid", '0' );    
                }
                $this->jsmapp->setUserState( "$this->jsmoption.pid", self::$_project_id ); 
                
        }
        
    /**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
	   if ( JComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend') )
        {
		$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' context -> '.$this->context.''),'');
        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' identifier -> '.$this->_identifier.''),'');
        }
		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $published);
        $value = $this->getUserStateFromRequest($this->context . '.list.limit', 'limit', $this->jsmapp->get('list_limit'), 'int');
		$this->setState('list.limit', $value);	
		
        // List state information.
        $value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
		$this->setState('list.start', $value);
		// Filter.order
		$orderCol = $this->getUserStateFromRequest($this->context. '.filter_order', 'filter_order', '', 'string');
		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'dv.name';
		}
		$this->setState('list.ordering', $orderCol);
		$listOrder = $this->getUserStateFromRequest($this->context. '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}
		$this->setState('list.direction', $listOrder);
        
	}
    
	/**
	 * sportsmanagementModelDivisions::getListQuery()
	 * 
	 * @return
	 */
	protected function getListQuery()
	{
        // Create a new query object.		
		$this->jsmquery->clear();
        $this->jsmquery->select('dv.*,dvp.name AS parent_name,u.name AS editor');
        $this->jsmquery->from('#__sportsmanagement_division AS dv');
        $this->jsmquery->join('LEFT', '#__sportsmanagement_division AS dvp ON dvp.id = dv.parent_id');
        $this->jsmquery->join('LEFT', '#__users AS u ON u.id = dv.checked_out');

        $this->jsmquery->where(' dv.project_id = ' . self::$_project_id);
        
        if ($this->getState('filter.search') )
		{
        $this->jsmquery->where('LOWER(dv.name) LIKE ' . $this->jsmdb->Quote( '%' . $this->getState('filter.search') . '%' ));
        }
        
        if (is_numeric($this->getState('filter.state')) )
		{
		$this->jsmquery->where('dv.published = '.$this->getState('filter.state'));	
		}
        
        $this->jsmquery->order($this->jsmdb->escape($this->getState('list.ordering', 'dv.name')).' '.
                $this->jsmdb->escape($this->getState('list.direction', 'ASC')));

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text = ' <br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>';    
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        }

		return $this->jsmquery;
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
	   //$app = JFactory::getApplication();
        //$option = JFactory::getApplication()->input->getCmd('option');
        $starttime = microtime(); 
        // Create a new query object.		
	//	$db = sportsmanagementHelper::getDBConnection();
	//	$query = $db->getQuery(true);
        $this->jsmquery->select('id AS value,name AS text');
        $this->jsmquery->from('#__sportsmanagement_division');
        $this->jsmquery->where('project_id = ' . $project_id);
        $this->jsmquery->order('name ASC');
        
		$this->jsmdb->setQuery( $this->jsmquery );
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'Notice');
        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		if ( !$result = $this->jsmdb->loadObjectList("value") )
		{
			//sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
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
	   //$app = JFactory::getApplication();
        //$option = JFactory::getApplication()->input->getCmd('option');
        $starttime = microtime(); 
        // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
        
        $query->select('count(*) AS count');
        $query->from('#__sportsmanagement_division AS d');
        $query->join('INNER', '#__sportsmanagement_project AS p on p.id = d.project_id');
        $query->where('p.id = ' . $project_id);
                
		$db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        }
        
		return $db->loadResult();
	}
	
}
?>
