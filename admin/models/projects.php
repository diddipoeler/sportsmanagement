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
 * sportsmanagementModelProjects
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelProjects extends JSMModelList
{
	var $_identifier = "projects";
	
    /**
     * sportsmanagementModelProjects::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'p.name',
                        'l.name',
                        'l.country',
                        's.name',
                        'st.name',
                        'p.project_type',
                        'p.published',
                        'p.id',
                        'p.ordering',
                        'p.picture',
                        'ag.name',
                        'p.agegroup_id',
                        'ag.name'
                        );
                parent::__construct($config);
                $getDBConnection = sportsmanagementHelper::getDBConnection();
                parent::setDbo($getDBConnection);
        }
        
    /**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = 'p.name', $direction = 'asc')
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
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.league', 'filter_league', '');
		$this->setState('filter.league', $temp_user_request);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.sports_type', 'filter_sports_type', '');
		$this->setState('filter.sports_type', $temp_user_request);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.season', 'filter_season', '');
		$this->setState('filter.season', $temp_user_request);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_nation', 'filter_search_nation', '');
		$this->setState('filter.search_nation', $temp_user_request);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_association', 'filter_search_association', '');
		$this->setState('filter.search_association', $temp_user_request);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.project_type', 'filter_project_type', '');
		$this->setState('filter.project_type', $temp_user_request);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.userfields', 'filter_userfields', '');
		$this->setState('filter.userfields', $temp_user_request);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_agegroup', 'filter_search_agegroup', '');
		$this->setState('filter.search_agegroup', $temp_user_request);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.unique_id', 'filter_unique_id', '');
        $this->setState('filter.unique_id', $temp_user_request);
        $value = $this->getUserStateFromRequest($this->context . '.list.limit', 'limit', $this->jsmapp->get('list_limit'), 'int');
		$this->setState('list.limit', $value);
        
        $value = $this->getUserStateFromRequest($this->context . '.list.ordering', 'ordering', $ordering, 'string');
		$this->setState('list.ordering', $value);
		$value = $this->getUserStateFromRequest($this->context . '.list.direction', 'direction', $direction, 'string');
		$this->setState('list.direction', $value);
		// List state information.
		parent::populateState($ordering, $direction);
        $value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
		$this->setState('list.start', $value);
        
	}
    
	/**
	 * sportsmanagementModelProjects::getListQuery()
	 * 
	 * @return
	 */
	protected function getListQuery()
	{
	// Create a new query object.		
		$this->jsmquery->clear();
        $this->jsmsubquery1->clear();
        $this->jsmsubquery2->clear();

switch ( $this->getState('filter.unique_id') )
        {
        case 0:
        $this->jsmsubquery1->select('count(pt.id)');
        $this->jsmsubquery1->from('#__sportsmanagement_project_team AS pt');
        $this->jsmsubquery1->where('pt.project_id = p.id');
        break;
        case 1:
        $this->jsmsubquery1->select('count(pt.id)');
        $this->jsmsubquery1->from('#__sportsmanagement_project_team AS pt');
        $this->jsmsubquery1->join('INNER','#__sportsmanagement_season_team_id as st ON st.id = pt.team_id');
        $this->jsmsubquery1->join('INNER','#__sportsmanagement_team as t ON t.id = st.team_id');
        $this->jsmsubquery1->join('INNER','#__sportsmanagement_club as c ON c.id = t.club_id');
        $this->jsmsubquery1->where('pt.project_id = p.id');
        $this->jsmsubquery1->where('( c.unique_id IS NULL OR c.unique_id LIKE '.$this->jsmdb->Quote(''.'').' )');
        break;
        case 2:
        $this->jsmsubquery1->select('count(pt.id)');
        $this->jsmsubquery1->from('#__sportsmanagement_project_team AS pt');
        $this->jsmsubquery1->join('INNER','#__sportsmanagement_season_team_id as st ON st.id = pt.team_id');
        $this->jsmsubquery1->join('INNER','#__sportsmanagement_team as t ON t.id = st.team_id');
        $this->jsmsubquery1->join('INNER','#__sportsmanagement_club as c ON c.id = t.club_id');
        $this->jsmsubquery1->where('pt.project_id = p.id');
        $this->jsmsubquery1->where('( c.unique_id NOT LIKE '.$this->jsmdb->Quote(''.'').' )');
        break;
        }		
		
        $this->jsmsubquery2->select('ef.name');
        $this->jsmsubquery2->from('#__sportsmanagement_user_extra_fields_values as ev ');
        $this->jsmsubquery2->join('INNER','#__sportsmanagement_user_extra_fields as ef ON ef.id = ev.field_id');
        $this->jsmsubquery2->where('ev.jl_id = p.id');
        $this->jsmsubquery2->where('ef.template_backend LIKE '.$this->jsmdb->Quote(''.'project'.''));
        $this->jsmsubquery2->where('ev.fieldvalue != '.$this->jsmdb->Quote(''.''));
        

        $this->jsmquery->select('p.id,p.ordering,p.published,p.project_type,p.name,p.alias,p.checked_out,p.checked_out_time,p.sports_type_id,p.current_round,p.picture,p.agegroup_id ');
        $this->jsmquery->select('p.league_id');
        $this->jsmquery->select('p.modified,p.modified_by');
        $this->jsmquery->select('u1.username');
        
        $this->jsmquery->select('st.name AS sportstype');
        $this->jsmquery->select('s.name AS season');
        $this->jsmquery->select('l.name AS league,l.country');
        $this->jsmquery->select('u.name AS editor');
        $this->jsmquery->select('ag.name AS agegroup');
        $this->jsmquery->select('(' . $this->jsmsubquery1 . ') AS proteams');
        
    $this->jsmquery->from('#__sportsmanagement_project AS p');
    $this->jsmquery->join('LEFT', '#__sportsmanagement_season AS s ON s.id = p.season_id');
    $this->jsmquery->join('LEFT', '#__sportsmanagement_league AS l ON l.id = p.league_id');
    $this->jsmquery->join('LEFT', '#__sportsmanagement_sports_type AS st ON st.id = p.sports_type_id');
    $this->jsmquery->join('LEFT', '#__sportsmanagement_agegroup AS ag ON ag.id = p.agegroup_id');
    $this->jsmquery->join('LEFT', '#__users AS u ON u.id = p.checked_out');
    $this->jsmquery->join('LEFT', '#__users AS u1 ON u1.id = p.modified_by');
  
        if ($this->getState('filter.userfields'))
		{
			$this->jsmquery->select('ev.fieldvalue as user_fieldvalue,ev.id as user_field_id');  
		$this->jsmquery->join('INNER','#__sportsmanagement_user_extra_fields_values as ev ON ev.jl_id = p.id');  
		$this->jsmquery->join('INNER','#__sportsmanagement_user_extra_fields as ef ON ef.id = ev.field_id');  
        $this->jsmquery->where('ef.id = ' . $this->getState('filter.userfields') );
        }

        if ($this->getState('filter.search'))
		{
        $this->jsmquery->where('LOWER(p.name) LIKE ' . $this->jsmdb->Quote( '%' . $this->getState('filter.search') . '%' ));
        }
        
        if ($this->getState('filter.league'))
		{
        $this->jsmquery->where('p.league_id = ' . $this->getState('filter.league'));
        }
        
        if ($this->getState('filter.sports_type'))
		{
        $this->jsmquery->where('p.sports_type_id = ' . $this->getState('filter.sports_type') );
        }
        
        if ($this->getState('filter.season'))
		{
        $this->jsmquery->where('p.season_id = ' . $this->getState('filter.season'));
        }
        
        if (is_numeric($this->getState('filter.state')) )
		{
		$this->jsmquery->where('p.published = '.$this->getState('filter.state'));	
		}
        
        if ($this->getState('filter.search_agegroup'))
		{
        $this->jsmquery->where('p.agegroup_id = ' . $this->getState('filter.search_agegroup'));
        }
        
        if ($this->getState('filter.search_nation'))
		{
        $this->jsmquery->where('l.country LIKE ' . $this->jsmdb->Quote( '' . $this->getState('filter.search_nation') . '' ));
        }
        
        if ($this->getState('filter.search_association'))
		{
        $this->jsmquery->where("l.associations = ".$this->getState('filter.search_association') );
        }
        
        if ($this->getState('filter.project_type'))
		{
        $this->jsmquery->where('p.project_type LIKE ' . $this->jsmdb->Quote( '' . $this->getState('filter.project_type') . '' ));
        }
     
     $this->jsmquery->order($this->jsmdb->escape($this->getState('list.ordering', 'p.name')).' '.
                $this->jsmdb->escape($this->getState('list.direction', 'ASC')));

//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'Notice');
                
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'Notice');
        }
                
		return $this->jsmquery;
        
	}
	
	
}
?>
