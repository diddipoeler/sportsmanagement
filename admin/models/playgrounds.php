<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      playgrounds.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage models
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementModelPlaygrounds
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelPlaygrounds extends JSMModelList 
{
	var $_identifier = "playgrounds";
	
    /**
     * sportsmanagementModelPlaygrounds::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'v.name',
                        'v.alias',
                        'v.short_name',
                        'v.max_visitors',
                        'v.picture',
                        'v.country',
                        'club',
                        'v.id',
                        'v.ordering'
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
		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_nation', 'filter_search_nation', '');
		$this->setState('filter.search_nation', $temp_user_request);
        // List state information.
        $value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
		$this->setState('list.start', $value);       
		// Filter.order
		$orderCol = $this->getUserStateFromRequest($this->context. '.filter_order', 'filter_order', '', 'string');
		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'v.name';
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
	 * sportsmanagementModelPlaygrounds::getListQuery()
	 * 
	 * @return
	 */
	function getListQuery()
	{
        // Select some fields
		$this->jsmquery->select('v.*');
        // From table
		$this->jsmquery->from('#__sportsmanagement_playground as v');
        // Join over the clubs
		$this->jsmquery->select('c.name As club');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_club AS c ON c.id = v.club_id');
        // Join over the users for the checked out user.
		$this->jsmquery->select('uc.name AS editor');
		$this->jsmquery->join('LEFT', '#__users AS uc ON uc.id = v.checked_out');
                
        if ($this->getState('filter.search'))
		{
        $this->jsmquery->where('LOWER(v.name) LIKE '.$this->jsmdb->Quote('%'.$this->getState('filter.search').'%'));
        }
        if ($this->getState('filter.search_nation'))
		{
        $this->jsmquery->where("v.country LIKE '".$this->getState('filter.search_nation')."'");
        }
        
        $this->jsmquery->order($this->jsmdb->escape($this->getState('list.ordering', 'v.name')).' '.
                $this->jsmdb->escape($this->getState('list.direction', 'ASC')));
                
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text = ' <br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>';    
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        }
                
		return $this->jsmquery;
        
	}
    
    /**
	 * Method to return a playground/venue array (id,text)
		*
		* @access	public
		* @return	array
		* @since 0.1
		*/
	function getPlaygrounds()
	{
        $starttime = microtime(); 
        $results = array();
        $this->jsmquery->clear();
        $this->jsmquery->select('id AS value, name AS text');
        // From table
		$this->jsmquery->from('#__sportsmanagement_playground');
        $this->jsmquery->order('text ASC');
		try{
        $this->jsmdb->setQuery($this->jsmquery);
		$result = $this->jsmdb->loadObjectList();
		return $result;
         }
        catch (Exception $e)
        {
        $this->jsmapp->enqueueMessage(JText::_($e->getMessage()), 'error');
        return false;
        }
	}
    
    /**
     * sportsmanagementModelPlaygrounds::getPlaygroundListSelect()
     * 
     * @return
     */
    public function getPlaygroundListSelect()
	{
        $starttime = microtime(); 
        $results = array();
        
        // Select some fields
        $this->jsmquery->clear();
		$this->jsmquery->select('id,name,id AS value,name AS text,short_name,club_id');
        // From table
		$this->jsmquery->from('#__sportsmanagement_playground');
        $this->jsmquery->order('name');
        try{
		$this->jsmdb->setQuery($this->jsmquery);
		$results = $this->jsmdb->loadObjectList();
        return $results;
         }
        catch (Exception $e)
        {
        $this->jsmapp->enqueueMessage(JText::_($e->getMessage()), 'error');
        return false;
        }
	}
    
    

	
}
?>
