<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      clubnames.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage models
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

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
		if ( ComponentHelper::getParams($this->jsmoption)->get('show_debug_info') )
        {
		$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' context -> '.$this->context.''),'');
        $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' identifier -> '.$this->_identifier.''),'');
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
        $this->jsmapp->enqueueMessage(Text::_($e->getMessage()), 'error');
        return false;
        }
                
    }


}
?>