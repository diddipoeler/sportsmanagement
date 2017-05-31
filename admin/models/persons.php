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
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * sportsmanagementModelPersons
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelPersons extends JSMModelList
{
	var $_identifier = "persons";
    
    /**
     * sportsmanagementModelPersons::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'pl.lastname',
                        'pl.firstname',
                        'pl.nickname',
                        'pl.birthday',
                        'pl.country',
                        'pl.position_id',
                        'pl.id',
                        'pl.picture',
                        'pl.ordering',
                        'pl.published',
                        'pl.modified',
                        'pl.modified_by',
                        'pl.checked_out',
                        'pl.checked_out_time',
                        'pl.agegroup_id',
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
	protected function populateState($ordering = 'pl.lastname', $direction = 'asc')
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
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_agegroup', 'filter_search_agegroup', '');
		$this->setState('filter.search_agegroup', $temp_user_request);
        $value = $this->getUserStateFromRequest($this->context . '.list.limit', 'limit', $this->jsmapp->get('list_limit'), 'int');
		$this->setState('list.limit', $value);	

		// List state information.
		parent::populateState($ordering, $direction);
        $value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
		$this->setState('list.start', $value);
        
	}
    

	/**
	 * sportsmanagementModelPersons::getListQuery()
	 * 
	 * @return
	 */
	function getListQuery()
	{
	
        $this->_type = $this->jsmapp->getUserState( "$this->jsmoption.persontype", '0' );
        $this->_project_id = $this->jsmapp->getUserState( "$this->jsmoption.pid", '0' );
        $this->_team_id = $this->jsmapp->getUserState( "$this->jsmoption.team_id", '0' );
        $this->_season_id = $this->jsmapp->getUserState( "$this->jsmoption.season_id", '0' );
        $this->_project_team_id = $this->jsmapp->getUserState( "$this->jsmoption.project_team_id", '0' );
	
        // Create a new query object.		
		$this->jsmquery->clear();
        $this->jsmsubquery1->clear();
        // Select some fields
        $this->jsmquery->select('pl.*');
        $this->jsmquery->select('pl.id as id2');
        // From table
		$this->jsmquery->from('#__sportsmanagement_person as pl');
        $this->jsmquery->join('LEFT', '#__sportsmanagement_agegroup AS ag ON ag.id = pl.agegroup_id');
        // Join over the users for the checked out user.
		$this->jsmquery->select('uc.name AS editor');
		$this->jsmquery->join('LEFT', '#__users AS uc ON uc.id = pl.checked_out');
        
        // neue struktur wird genutzt
        if ( COM_SPORTSMANAGEMENT_USE_NEW_TABLE && JRequest::getVar('layout') == 'assignplayers' )
        {
            $this->jsmquery->join('INNER', '#__sportsmanagement_season_person_id AS sp ON sp.person_id = pl.id');
            $this->jsmquery->where('sp.season_id = '.$this->_season_id);
        }
        
        if ($this->getState('filter.search'))
		{
        $this->jsmquery->where('(LOWER(pl.lastname) LIKE ' . $this->jsmdb->Quote( '%' . $this->getState('filter.search') . '%' ).
						   'OR LOWER(pl.firstname) LIKE ' . $this->jsmdb->Quote( '%' . $this->getState('filter.search') . '%' ) .
						   'OR LOWER(pl.nickname) LIKE ' . $this->jsmdb->Quote( '%' . $this->getState('filter.search') . '%' ) .
                           'OR LOWER(pl.info) LIKE ' . $this->jsmdb->Quote( '%' . $this->getState('filter.search') . '%' ) .
                            ')');
        }
        
        if ($this->getState('filter.search_nation'))
		{
        $this->jsmquery->where('pl.country LIKE '.$this->jsmdb->Quote(''.$this->getState('filter.search_nation').''));
        }
        
        if ($this->getState('filter.search_agegroup'))
		{
        $this->jsmquery->where('pl.agegroup_id = ' . $this->getState('filter.search_agegroup'));
        }
        
        if (is_numeric($this->getState('filter.state')) )
		{
		$this->jsmquery->where('pl.published = '.$this->getState('filter.state'));	
		}
        
        if ( JRequest::getVar('layout') == 'assignplayers')
        {
            switch ($this->_type)
            {
                case 1:
                /**
                 * spieler
                 */
                $this->jsmsubquery1->select('stp.person_id');
                $this->jsmsubquery1->from('#__sportsmanagement_season_team_person_id AS stp  ');
                $this->jsmsubquery1->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = stp.team_id');  
                $this->jsmsubquery1->where('st.team_id = '.$this->_team_id);
                $this->jsmsubquery1->where('stp.season_id = '.$this->_season_id);
                $this->jsmsubquery1->where('stp.persontype = 1');
                $this->jsmquery->where('pl.id NOT IN ('.$this->jsmsubquery1.')');
                break;
                case 2:
                /**
                 * trainer
                 */
                $this->jsmsubquery1->select('stp.person_id');
                $this->jsmsubquery1->from('#__sportsmanagement_season_team_person_id AS stp  ');
                $this->jsmsubquery1->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = stp.team_id');  
                $this->jsmsubquery1->where('st.team_id = '.$this->_team_id);
                $this->jsmsubquery1->where('stp.season_id = '.$this->_season_id);
                $this->jsmsubquery1->where('stp.persontype = 2');
                $this->jsmquery->where('pl.id NOT IN ('.$this->jsmsubquery1.')');
                break;
                case 3:
                /**
                 * schiedsrichter
                 */
                $this->jsmsubquery1->select('stp.person_id');
                $this->jsmsubquery1->from('#__sportsmanagement_season_person_id AS stp ');
                $this->jsmsubquery1->join('INNER','#__sportsmanagement_project_referee AS prof ON prof.person_id = stp.id');  
                $this->jsmsubquery1->where('stp.season_id = '.$this->_season_id);
                $this->jsmsubquery1->where('stp.persontype = 3');
                $this->jsmsubquery1->where('prof.project_id = '.$this->_project_id);
                $this->jsmquery->where('pl.id NOT IN ('.$this->jsmsubquery1.')');
                $this->jsmquery->group('pl.id');

                break;
                
            }
            
            
        }
        
        
        $this->jsmquery->order($this->jsmdb->escape($this->getState('list.ordering', 'pl.lastname')).' '.
                $this->jsmdb->escape($this->getState('list.direction', 'ASC')));
                
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text = ' <br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>';    
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        }
                
        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'Notice');
		return $this->jsmquery;
        
        
        
	}
	
	/**
	 * sportsmanagementModelPersons::getPersonsToAssign()
	 * 
     * im augenblick keine verwendung
     * 
	 * @return
	 */
	function getPersonsToAssign()
	{
	   $app = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
        
		$cid = JRequest::getVar( 'cid' );

		if ( !count( $cid ) )
		{
			return array();
		}
        
        $query->select('pl.id,pl.firstname, pl.nickname,pl.lastname');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pl');
        $query->where('pl.id IN (' . implode( ', ', $cid ) . ')');
        $query->where('pl.published = 1');
		
        $db->setQuery( $query );
		return $db->loadObjectList();
	}


	/**
	 * return list of project teams for select options
	 *
     * im augenblick keine verwendung
     * 
	 * @return array
	 */
	function getProjectTeamList()
	{
	   $app = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
        
        $query->select('t.id AS value,t.name AS text');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = t.id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON st.id = pt.team_id');
        $query->where('tt.project_id = ' . $this->_project_id);
        $query->order('text ASC');

		$db->setQuery( $query );
		return $db->loadObjectList();
	}

	
	/**
	 * sportsmanagementModelPersons::getTeamName()
	 * 
     * im augenblick keine verwendung
     * 
	 * @param mixed $team_id
	 * @return
	 */
	function getTeamName( $team_id )
	{
	   $app = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
        
		if ( !$team_id )
		{
			return '';
		}
        
        $query->select('name');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team');
        $query->where('id = '. $team_id);
		
        $db->setQuery( $query );
		return $db->loadResult();
	}

	
	/**
	 * sportsmanagementModelPersons::getProjectTeamName()
	 * 
	 * @param mixed $project_team_id
	 * @return
	 */
	function getProjectTeamName( $project_team_id )
	{
	   $app = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
        
		if ( !$project_team_id )
		{
			return '';
		}
        
        $query->select('t.name');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = t.id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON st.id = pt.team_id');
        
        $query->where('pt.id = '. $db->Quote($project_team_id));

		$db->setQuery( $query );
		return $db->loadResult();
	}
    
    

	/**
	 * sportsmanagementModelPersons::getPersons()
	 * 
     * im augenblick keine verwendung
     * 
	 * @return
	 */
	function getPersons()
	{
	   $app = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
        
        $query->select('id AS value,lastname,firstname,info,weight,height,picture,birthday,notes,nickname,knvbnr,country,phone,mobile,email');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person');
        $query->where('published = 1');
        $query->order('lastname ASC');
		
        $db->setQuery($query);
		if (!$result = $db->loadObjectList())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
			return false;
		}
		return $result;
	}
    
    /**
     * sportsmanagementModelPersons::getPersonListSelect()
     * 
     * wird beim xmlimport aufgerufen
     * 
     * @return
     */
    public function getPersonListSelect()
	{
	   $app = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       $results = array();
       // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
        $query->select('id,id AS value,firstname,lastname,nickname,birthday,info');
        $query->select('LOWER(lastname) AS low_lastname');
        $query->select('LOWER(firstname) AS low_firstname');
        $query->select('LOWER(nickname) AS low_nickname');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person');
        $query->where("firstname<>'!Unknown' AND lastname<>'!Player' AND nickname<>'!Ghost'");
        $query->order('lastname,firstname');
		
        $db->setQuery($query);
		if ($results = $db->loadObjectList())
		{
			foreach ($results AS $person)
			{
				$textString=$person->lastname.','.$person->firstname;
				if (!empty($person->nickname))
				{
					$textString .= " '".$person->nickname."'";
				}
				if ($person->birthday!='0000-00-00')
				{
					$textString .= " (".$person->birthday.")";
				}
				$person->text=$textString;
			}
			return $results;
		}
		//return false;
        return $results;
	}
    

}
?>
