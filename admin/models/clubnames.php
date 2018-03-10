<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroups.php
* @author                diddipoeler, stony und svdoldie (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
*        SportsManagement is free software: you can redistribute it and/or modify
*        it under the terms of the GNU General Public License as published by
*  the Free Software Foundation, either version 3 of the License, or
*  (at your option) any later version.
*
*  SportsManagement is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  You should have received a copy of the GNU General Public License
*  along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
*  Diese Datei ist Teil von SportsManagement.
*
*  SportsManagement ist Freie Software: Sie können es unter den Bedingungen
*  der GNU General Public License, wie von der Free Software Foundation,
*  Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
*  veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
*  SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
*  OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
*  Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
*  Siehe die GNU General Public License für weitere Details.
*
*  Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
*  Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementModelclubnames
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class sportsmanagementModelclubnames extends JSMModelList
{
	var $_identifier = "clubnames";
	
	
	/**
	 * sportsmanagementModelclubnames::__construct()
	 * 
	 * @param mixed $config
	 * @return void
	 */
	public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'obj.name',
                        'obj.name_long',
                        'obj.country',
                        'obj.id',
                        'obj.published',
                        'obj.modified',
                        'obj.modified_by',
                        'obj.ordering',
                        'obj.checked_out',
                        'obj.checked_out_time'
                        );
                parent::__construct($config);
                parent::setDbo($this->jsmdb);
        
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
		if ( JComponentHelper::getParams($this->jsmoption)->get('show_debug_info') )
        {
		$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' context -> '.$this->context.''),'');
        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' identifier -> '.$this->_identifier.''),'');
        }

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $published);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_nation', 'filter_search_nation', '');
		$this->setState('filter.search_nation', $temp_user_request);
        $value = $this->getUserStateFromRequest($this->context . '.list.limit', 'limit', $this->jsmapp->get('list_limit'), 'int');
		$this->setState('list.limit', $value);

		// List state information.
        $value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
		$this->setState('list.start', $value);
        // Filter.order
		$orderCol = $this->getUserStateFromRequest($this->context. '.filter_order', 'filter_order', '', 'string');
		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'obj.name';
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
	 * sportsmanagementModelagegroups::getListQuery()
	 * 
	 * @return
	 */
	function getListQuery()
	{
		// Create a new query object.		
		$this->jsmquery->clear();		
        // Select some fields
		$this->jsmquery->select(implode(",",$this->filter_fields));
        // From table
		$this->jsmquery->from('#__sportsmanagement_club_names as obj');
        $this->jsmquery->join('LEFT', '#__users AS uc ON uc.id = obj.checked_out');

        if ($this->getState('filter.search') )
		{
        $this->jsmquery->where('LOWER(obj.name) LIKE '.$this->jsmdb->Quote('%'.$this->getState('filter.search').'%') );
        }
        if ($this->getState('filter.search_nation'))
		{
        $this->jsmquery->where('obj.country LIKE '.$this->jsmdb->Quote('%'.$this->getState('filter.search_nation').'%') );
        }
       
        if (is_numeric($this->getState('filter.state')) )
		{
		$this->jsmquery->where('obj.published = '.$this->getState('filter.state'));	
		}

        $this->jsmquery->order($this->jsmdb->escape($this->getState('list.ordering', 'obj.name')).' '.
                $this->jsmdb->escape($this->getState('list.direction', 'ASC')));
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text = ' <br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>';    
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        }
        
		return $this->jsmquery;
        
	}
    
    
    /**
     * sportsmanagementModelclubnames::getClubNames()
     * 
     * @param string $country
     * @return void
     */
    function getClubNames($country = '')
    {
    // Create a new query object.		
	$this->jsmquery->clear();
    
    // Select some fields
	$this->jsmquery->select('name,name_long');
    // From table
	$this->jsmquery->from('#__sportsmanagement_club_names');
    
    /**
     * wenn das land mitegegeben wurde
     */
    if ( $country )
	{
    $this->jsmquery->where('country LIKE '.$this->jsmdb->Quote('%'.$country.'%') );
    }
    
    try{
    $this->jsmdb->setQuery($this->jsmquery);
    return $this->jsmdb->loadObjectList();
	}
        catch (Exception $e)
        {
        $this->jsmapp->enqueueMessage(JText::_($e->getMessage()), 'error');
        return false;
        }
                
    }


}
?>