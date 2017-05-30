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
		$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' context -> '.$this->context.''),'');
        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' identifier -> '.$this->_identifier.''),'');
        // Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_nation', 'filter_search_nation', '');
		$this->setState('filter.search_nation', $temp_user_request);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.federation', 'filter_federation', '');
        $this->setState('filter.federation', $temp_user_request);        
        $value = $this->getUserStateFromRequest($this->context . '.list.limit', 'limit', $this->jsmapp->get('list_limit'), 'int');
		$this->setState('list.limit', $value);	

		// List state information.
		parent::populateState('objassoc.name', 'asc');
        $value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
		$this->setState('list.start', $value);
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
		$query->where('objassoc.published = '.$this->getState('filter.state'));	
		}

        
        $this->jsmquery->order($this->jsmdb->escape($this->getState('list.ordering', 'objassoc.name')).' '.
                $this->jsmdb->escape($this->getState('list.direction', 'ASC')));
 
		if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text .= ' <br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>';    
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        }
        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'Notice');
        return $this->jsmquery;
	}
	
    
    /**
     * sportsmanagementModeljlextassociations::getAssociations()
     * 
     * @return
     */
    function getAssociations($federation=0)
    {
        $search_nation = '';
        
        if ( $this->jsmapp->isAdmin() )
        {
        $search_nation	= $this->getState('filter.search_nation');
        }
        // Create a new query object.
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

        $this->jsmdb->setQuery($this->jsmquery);
        if (!$result = $this->jsmdb->loadObjectList())
        {
            sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->jsmdb->getErrorMsg(), __LINE__);
            return array();
        }
        foreach ($result as $association)
        {
            $association->name = '( '.$association->country.' ) '.   JText::_($association->name);
            $association->text = $association->name;
        }
        return $result;
    }


	
}
?>
