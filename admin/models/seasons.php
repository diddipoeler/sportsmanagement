<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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
 * sportsmanagementModelSeasons
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelSeasons extends JSMModelList
{
	var $_identifier = "seasons";
	var $_order = "s.name";
    
    /**
     * sportsmanagementModelSeasons::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
        {   
        // Reference global application object
        $this->app = JFactory::getApplication();
        // JInput object
        $this->jinput = $this->app->input;
                
                $layout = $this->jinput->getVar('layout');
                
                //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($layout,true).'</pre>'),'Notice');
                
                switch ($layout)
        {
            case 'assignteams':
            $this->_order = 't.name';
            break;
            
            case 'assignpersons':
            $this->_order = 'p.lastname';
            break;
            
            default:
		    $this->_order = 's.name';
            break;
        }
                $config['filter_fields'] = array(
                        's.name',
                        's.alias',
                        's.id',
                        's.ordering',
                        's.published',
                        's.modified',
                        's.modified_by',
                        's.checked_out',
                        's.checked_out_time'
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
		
        $layout = $this->jsmjinput->getVar('layout');
        // Initialise variables.
		//$app = JFactory::getApplication('administrator');
        $order = '';
        
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
			$orderCol = 's.name';
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
	 * sportsmanagementModelSeasons::getListQuery()
	 * 
	 * @return
	 */
	protected function getListQuery()
	{

        $layout = $this->jsmjinput->getVar('layout');
        $season_id = $this->jsmjinput->getVar('id');
        
        $this->setState('list.ordering', $this->_order);
        
        switch ($layout)
        {
            case 'assignteams':
            	$this->jsmquery->clear();
            // Select some fields
		    $this->jsmquery->select('t.*');
		    // From the seasons table
		    $this->jsmquery->from('#__sportsmanagement_team as t');
            $this->jsmquery->join('LEFT', '#__sportsmanagement_club AS c ON c.id = t.club_id');
            $this->jsmsubquery1->select('stp.team_id');
            $this->jsmsubquery1->from('#__sportsmanagement_season_team_id AS stp  ');
            $this->jsmsubquery1->where('stp.season_id = '.$season_id);
            $this->jsmquery->where('t.id NOT IN ('.$this->jsmsubquery1.')');
            if ($this->getState('filter.search_nation'))
		    {
            $this->jsmquery->where('c.country LIKE '.$this->jsmdb->Quote(''.$this->getState('filter.search_nation').''));
            }
            if ($this->getState('filter.search'))
		    {
            $this->jsmquery->where(' LOWER(t.name) LIKE '.$this->jsmdb->Quote('%'.$this->getState('filter.search').'%'));
            }
            //$order = 't.name';
            break;
            
            case 'assignpersons':
            	$this->jsmquery->clear();
            // Select some fields
		    $this->jsmquery->select('p.*');
		    // From the seasons table
		    $this->jsmquery->from('#__sportsmanagement_person as p');
            $this->jsmsubquery1->select('stp.person_id');
            $this->jsmsubquery1->from('#__sportsmanagement_season_person_id AS stp  ');
            $this->jsmsubquery1->where('stp.season_id = '.$season_id);
            $this->jsmquery->where('p.id NOT IN ('.$this->jsmsubquery1.')');
            if ($this->getState('filter.search_nation'))
		    {
            $this->jsmquery->where('p.country LIKE '.$this->jsmdb->Quote(''.$this->getState('filter.search_nation').'') );
            }
            if ($this->getState('filter.search'))
		{
        $this->jsmquery->where('(LOWER(p.lastname) LIKE ' . $this->jsmdb->Quote( '%' . $this->getState('filter.search') . '%' ).
						   'OR LOWER(p.firstname) LIKE ' . $this->jsmdb->Quote( '%' . $this->getState('filter.search') . '%' ) .
						   'OR LOWER(p.nickname) LIKE ' . $this->jsmdb->Quote( '%' . $this->getState('filter.search') . '%' ) .
                           'OR LOWER(p.info) LIKE ' . $this->jsmdb->Quote( '%' . $this->getState('filter.search') . '%' ) .
                            ')');
        }
            //$order = 'p.lastname';
            break;
            
            default:
            $this->jsmquery->clear();
            // Select some fields
		    $this->jsmquery->select(implode(",",$this->filter_fields));
            $this->jsmquery->select('uc.name AS editor');
		    // From the seasons table
		    $this->jsmquery->from('#__sportsmanagement_season as s');
            $this->jsmquery->join('LEFT', '#__users AS uc ON uc.id = s.checked_out');
            
            if ($this->getState('filter.search'))
		    {
            $this->jsmquery->where(' LOWER(s.name) LIKE '.$this->jsmdb->Quote('%'.$this->getState('filter.search').'%'));
            }
            
            if (is_numeric($this->getState('filter.state')) )
		    {
		    $this->jsmquery->where('s.published = '.$this->getState('filter.state'));	
		    }
            break;
        }
		
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _order<br><pre>'.print_r($this->_order,true).'</pre>'),'Notice');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' list.ordering<br><pre>'.print_r($this->getState('list.ordering', $this->_order),true).'</pre>'),'Notice');
        
        $this->jsmquery->order($this->jsmdb->escape($this->getState('list.ordering', $this->_order)).' '.
                $this->jsmdb->escape($this->getState('list.direction', 'ASC')));
 
//        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->query->dump(),true).'</pre>'),'Notice');
 
if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text = ' <br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>';    
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        }
        
        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'Notice');

        return $this->jsmquery;
	}
	
  
  



	/**
	 * sportsmanagementModelSeasons::getSeasonTeams()
	 * 
	 * @param integer $season_id
	 * @return void
	 */
	public function getSeasonTeams($season_id=0)
    {
    $this->jsmquery->clear();
        // Select some fields
		    $this->jsmquery->select('t.id as value, t.name as text');
        // From the seasons table
		    $this->jsmquery->from('#__sportsmanagement_team as t');
        $this->jsmquery->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
        $this->jsmquery->where('st.season_id = '.$season_id);
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
     * Method to return a seasons array (id,name)
     *
     * @access	public
     * @return	array seasons
     * @since	1.5.0a
     */
    function getSeasons()
    {
        $this->jsmquery->clear();
        $this->jsmquery->select(array('id', 'name'))
        ->from('#__sportsmanagement_season')
        ->order('name DESC');

        try{
        $this->jsmdb->setQuery($this->jsmquery);
        $result = $this->jsmdb->loadObjectList();

        foreach ($result as $season)
        {
            $season->name = JText::_($season->name);
        }
        return $result;
        }
        catch (Exception $e)
        {
        $this->jsmapp->enqueueMessage(JText::_($e->getMessage()), 'error');
        return false;
        }
    }
	
	

	
}
?>
