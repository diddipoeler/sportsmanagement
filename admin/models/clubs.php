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
//jimport('joomla.application.component.modellist');


/**
 * sportsmanagementModelClubs
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelClubs extends JSMModelList
{
	var $_identifier = "clubs";
	
	/**
	 * sportsmanagementModelClubs::__construct()
	 * 
	 * @param mixed $config
	 * @return void
	 */
	public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'a.name',
                        'a.website',
                        'a.logo_big',
                        'a.logo_middle',
                        'a.logo_small',
                        'a.country',
                        'a.alias',
                        'a.zipcode',
                        'a.location',
                        'a.address',
                        'a.latitude',
                        'a.longitude',
                        'a.id',
                        'a.published',
                        'a.unique_id',
                        'a.new_club_id',
                        'a.ordering',
                        'a.checked_out',
                        'a.checked_out_time'
                        );
                parent::__construct($config);
                //$getDBConnection = sportsmanagementHelper::getDBConnection();
                parent::setDbo($this->jsmdb);
                
//        // Reference global application object
//        $this->app = JFactory::getApplication();
//        $this->user	= JFactory::getUser();     
//        // JInput object
//        $this->jinput = $this->app->input;
//        $this->option = $this->jinput->getCmd('option');
//        $this->jsmdb = $this->getDbo();
//        $this->query = $this->jsmdb->getQuery(true);
        
        
        }
        
    /**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = 'a.name', $direction = 'asc')
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
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_nation', 'filter_search_nation', '');
		$this->setState('filter.search_nation', $temp_user_request);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.season', 'filter_season', '');
		$this->setState('filter.season', $temp_user_request);
        
        $value = $this->getUserStateFromRequest($this->context . '.list.limit', 'limit', $this->jsmapp->get('list_limit'), 'int');
		$this->setState('list.limit', $value);	
		// List state information.
		parent::populateState($ordering, $direction);
        $value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
		$this->setState('list.start', $value);
	}
    
    /**
     * sportsmanagementModelClubs::getListQuery()
     * 
     * @return
     */
    protected function getListQuery()
	{
	
		// Select some fields
        $this->jsmquery->clear();
		$this->jsmquery->select(implode(",",$this->filter_fields));
		// From the club table
		$this->jsmquery->from('#__sportsmanagement_club as a');
        
        // Join over the users for the checked out user.
		$this->jsmquery->select('uc.name AS editor');
		$this->jsmquery->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');
        
        if ($this->getState('filter.search'))
		{
        //$this->query->where('LOWER(a.name) LIKE '.$this->jsmdb->Quote('%'.$search.'%'));
        $this->jsmquery->where(' ( LOWER(a.name) LIKE '.$this->jsmdb->Quote('%'.$this->getState('filter.search').'%') .' OR LOWER(a.unique_id) LIKE '.$this->jsmdb->Quote('%'.$this->getState('filter.search').'%') .')' );
        }
        if ($this->getState('filter.search_nation'))
		{
        $this->jsmquery->where('a.country LIKE '.$this->jsmdb->Quote(''.$this->getState('filter.search_nation').'') );
        }
        
        if ($this->getState('filter.season'))
		{
        $this->jsmquery->join('LEFT','#__sportsmanagement_team AS t ON a.id = t.club_id');
        $this->jsmquery->join('LEFT','#__sportsmanagement_season_team_id as st ON t.id = st.team_id ');
        $this->jsmquery->where('st.season_id = '.$this->getState('filter.season'));
        }
        
        if (is_numeric($this->getState('filter.state')) )
		{
		$this->jsmquery->where('a.published = '.$this->getState('filter.state'));	
		}
        
        $this->jsmquery->order($this->jsmdb->escape($this->getState('list.ordering', 'a.name')).' '.
                $this->jsmdb->escape($this->getState('list.direction', 'ASC')));
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text = 'query <pre>'.print_r($this->jsmquery->dump(),true).'</pre>';    
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        }
        

		return $this->jsmquery;
	}

   
    /**
     * sportsmanagementModelClubs::getClubListSelect()
     * 
     * @return
     */
    public function getClubListSelect()
	{
	   
        $starttime = microtime(); 
        $results = array();
        // Select some fields
        $this->jsmquery->clear();
		$this->jsmquery->select('id,name,id AS value,name AS text,country,standard_playground');
        // From table
		$this->jsmquery->from('#__sportsmanagement_club');
        $this->jsmquery->order('name');

		$this->jsmdb->setQuery($this->jsmquery);
		if ( $results = $this->jsmdb->loadObjectList() )
		{
			return $results;
		}
		//return false;
        return $results;
	}

	
}
?>
