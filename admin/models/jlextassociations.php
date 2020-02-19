<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      jlextassociations.php
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
 * sportsmanagementModeljlextassociations
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModeljlextassociations extends JSMModelList
{
	var $_identifier = "jlextassociations";
	
	/**
	 * sportsmanagementModeljlextassociations::__construct()
	 * 
	 * @param mixed $config
	 * @return void
	 */
	public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'objassoc.name',
                        'objassoc.alias',
                        'objassoc.short_name',
                        'objassoc.country',
                        'objassoc.id',
			'objassoc.website',
                        'objassoc.ordering',
                        'objassoc.published',
                        'objassoc.modified',
                        'objassoc.modified_by',
                        'objassoc.checked_out',
                        'objassoc.checked_out_time',
                        'objassoc.assocflag',
                        'objassoc.picture'
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
		if ( ComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend') )
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
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.federation', 'filter_federation', '');
        $this->setState('filter.federation', $temp_user_request);        
        $value = $this->getUserStateFromRequest($this->context . '.list.limit', 'limit', $this->jsmapp->get('list_limit'), 'int');
		$this->setState('list.limit', $value);	

		// List state information.
        $value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
		$this->setState('list.start', $value);
        
        // Filter.order
		$orderCol = $this->getUserStateFromRequest($this->context. '.filter_order', 'filter_order', '', 'string');
		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'objassoc.name';
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
   * sportsmanagementModeljlextassociations::getListQuery()
   * 
   * @return
   */
  protected function getListQuery()
	{
        // Create a new query object.		
		$this->jsmquery->clear();
		// Select some fields
		$this->jsmquery->select(implode(",",$this->filter_fields));
		// From the _associations table
		$this->jsmquery->from('#__sportsmanagement_associations as objassoc');
        // Join over the users for the checked out user.
		$this->jsmquery->select('uc.name AS editor');
		$this->jsmquery->join('LEFT', '#__users AS uc ON uc.id = objassoc.checked_out');

        if ($this->getState('filter.search') )
		{
        $this->jsmquery->where('LOWER(objassoc.name) LIKE '.$this->jsmdb->Quote('%'.$this->getState('filter.search').'%'));
        }
        
        if ( $this->getState('filter.search_nation') )
		{
        $this->jsmquery->where("objassoc.country LIKE '".$this->getState('filter.search_nation')."'");
        }
        
        if ($this->getState('filter.federation') )
		{
        $this->jsmquery->where("objassoc.parent_id = ".$this->getState('filter.federation') );
        }
        
        if (is_numeric($this->getState('filter.state')) )
		{
		$this->jsmquery->where('objassoc.published = '.$this->getState('filter.state'));	
		}

        
        $this->jsmquery->order($this->jsmdb->escape($this->getState('list.ordering', 'objassoc.name')).' '.
                $this->jsmdb->escape($this->getState('list.direction', 'ASC')));
 
		
        return $this->jsmquery;
	}
	
    
    
    /**
     * sportsmanagementModeljlextassociations::getAssociations()
     * 
     * @param integer $federation
     * @return
     */
    function getAssociations($federation=0)
    {
        $search_nation = '';
        
        if ( $this->jsmapp->isClient('administrator') )
        {
        $search_nation	= $this->getState('filter.search_nation');
        }
        /** Create a new query object */
        $this->jsmquery->clear();
        $this->jsmquery->select('id,name,id as value,name as text,country');
        $this->jsmquery->from('#__sportsmanagement_associations');
        if ($federation)
		{
        $this->jsmquery->where('parent_id = '.$federation.' OR id = '.$federation);
        }
        if ($search_nation)
		{
        $this->jsmquery->where('country LIKE '.$this->jsmdb->Quote(''.$search_nation.''));
        }
        
        $this->jsmquery->order('name ASC');

        try{
        $this->jsmdb->setQuery($this->jsmquery);
        $result = $this->jsmdb->loadObjectList();

        foreach ($result as $association)
        {
            $association->name = '( '.$association->country.' ) '.   Text::_($association->name);
            $association->text = $association->name;
        }
        return $result;
        }
        catch (Exception $e)
        {
        $this->jsmapp->enqueueMessage(Text::_($e->getMessage()), 'error');
        return false;
        }
    }


	
}
?>
