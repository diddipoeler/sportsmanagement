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

jimport( 'joomla.application.component.modellist' );



/**
 * sportsmanagementModelPersons
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelPersons extends JModelList
{
	var $_identifier = "persons";
    
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
                        'pl.checked_out',
                        'pl.checked_out_time'
                        );
                   
                parent::__construct($config);
                parent::setDbo(sportsmanagementHelper::getDBConnection());
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
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Initialise variables.
		$app = JFactory::getApplication('administrator');
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' context<br><pre>'.print_r($this->context,true).'</pre>'),'');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_nation', 'filter_search_nation', '');
		$this->setState('filter.search_nation', $temp_user_request);

//		$image_folder = $this->getUserStateFromRequest($this->context.'.filter.image_folder', 'filter_image_folder', '');
//		$this->setState('filter.image_folder', $image_folder);
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' image_folder<br><pre>'.print_r($image_folder,true).'</pre>'),'');


//		// Load the parameters.
//		$params = JComponentHelper::getParams('com_sportsmanagement');
//		$this->setState('params', $params);

		// List state information.
		parent::populateState('pl.lastname', 'asc');
	}
    

	/**
	 * sportsmanagementModelPersons::getListQuery()
	 * 
	 * @return
	 */
	function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        //$this->_type = JRequest::getInt('type');
        $this->_type	= $mainframe->getUserState( "$option.persontype", '0' );
         
        $this->_project_id	= $mainframe->getUserState( "$option.pid", '0' );
        $this->_team_id = $mainframe->getUserState( "$option.team_id", '0' );
        $this->_season_id = $mainframe->getUserState( "$option.season_id", '0' );
        $this->_project_team_id = $mainframe->getUserState( "$option.project_team_id", '0' );
        
        $search	= $this->getState('filter.search');
        $search_nation	= $this->getState('filter.search_nation');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' layout<br><pre>'.print_r(JRequest::getVar('layout'),true).'</pre>'),'');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _type<br><pre>'.print_r($this->_type,true).'</pre>'),'');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' search<br><pre>'.print_r($search,true).'</pre>'),'');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _project_id<br><pre>'.print_r($this->_project_id,true).'</pre>'),'');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _team_id<br><pre>'.print_r($this->_team_id,true).'</pre>'),'');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _project_team_id<br><pre>'.print_r($this->_project_team_id,true).'</pre>'),'');
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' filter_fields<br><pre>'.print_r($this->filter_fields,true).'</pre>'),'');
        }
        



        // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
        $Subquery	= $db->getQuery(true);
		$user	= JFactory::getUser(); 
		
        // Select some fields
		$query->select(implode(",",$this->filter_fields));
        $query->select('pl.id as id2');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person as pl');
        // Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = pl.checked_out');
        
        // neue struktur wird genutzt
        if ( COM_SPORTSMANAGEMENT_USE_NEW_TABLE && JRequest::getVar('layout') == 'assignplayers' )
        {
            $query->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_person_id AS sp ON sp.person_id = pl.id');
            $query->where('sp.season_id = '.$this->_season_id);
        }
        
        if ($search)
		{
        $query->where('(LOWER(pl.lastname) LIKE ' . $db->Quote( '%' . $search . '%' ).
						   'OR LOWER(pl.firstname) LIKE ' . $db->Quote( '%' . $search . '%' ) .
						   'OR LOWER(pl.nickname) LIKE ' . $db->Quote( '%' . $search . '%' ) .
                           'OR LOWER(pl.info) LIKE ' . $db->Quote( '%' . $search . '%' ) .
                            ')');
        }
        if ($search_nation)
		{
        $query->where("pl.country = '".$search_nation."'");
        }
        
        if ( JRequest::getVar('layout') == 'assignplayers')
        {
            switch ($this->_type)
            {
                case 1:
                // player
                $Subquery->select('stp.person_id');
                $Subquery->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS stp  ');
                $Subquery->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = stp.team_id');  
                //$Subquery->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
                $Subquery->where('st.team_id = '.$this->_team_id);
                $Subquery->where('stp.season_id = '.$this->_season_id);
                $Subquery->where('stp.persontype = 1');
                $query->where('pl.id NOT IN ('.$Subquery.')');
                break;
                case 2:
                //staff
                $Subquery->select('stp.person_id');
                $Subquery->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS stp  ');
                $Subquery->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = stp.team_id');  
                //$Subquery->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
                $Subquery->where('st.team_id = '.$this->_team_id);
                $Subquery->where('stp.season_id = '.$this->_season_id);
                $Subquery->where('stp.persontype = 2');
                $query->where('pl.id NOT IN ('.$Subquery.')');
                
//                $Subquery->select('person_id');
//                $Subquery->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_staff AS ts ');
//                $Subquery->where('projectteam_id = '.$this->_project_team_id.' AND ts.person_id = pl.id');
//                $query->where('pl.id NOT IN ('.$Subquery.')');
                break;
                case 3:
                // referee
                $Subquery->select('stp.person_id');
                $Subquery->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS stp  ');
                $Subquery->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = stp.team_id');  
                //$Subquery->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
                $Subquery->where('st.id = '.$this->_team_id);
                $Subquery->where('stp.season_id = '.$this->_season_id);
                $Subquery->where('stp.persontype = 3');
                $query->where('pl.id NOT IN ('.$Subquery.')');
                
//                $Subquery->select('person_id');
//                $Subquery->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS pr ');
//                $Subquery->where('project_id = '.$this->_project_id.' AND pr.person_id = pl.id');
//                $query->where('pl.id NOT IN ('.$Subquery.')');
                break;
                
            }
            
            
        }
        
        
        $query->order($db->escape($this->getState('list.ordering', 'pl.lastname')).' '.
                $db->escape($this->getState('list.direction', 'ASC')));
                
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        }
                
        
		return $query;
        
        
        
	}


    
    
    
    /**
	 * get person history across all projects, with team, season, position,... info
	 *
	 * @param int $person_id
	 * @param int $order ordering for season and league
	 * @param string $filter e.g. "s.name = 2007/2008", default empty string
	 * @return array of objects
	 */
	function jl_getPersonHistory( $person_id, $order = 'ASC', $published = 1, $filter = "" )
	{
		if ( $published )
		{
			$filter .= " AND p.published = 1 ";
		}

		$query = "	SELECT	pt.id AS ptid,
							pt.person_id AS pid,
							pt.team_id, pt.project_id,
							t.name AS teamname,
							p.name AS pname,
							s.name AS sname,
							tt.id AS ttid,
							pos.name AS position
					FROM #__".COM_SPORTSMANAGEMENT_TABLE."_team_player AS pt
					INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_project AS p ON p.id = pt.project_id
					INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_season AS s ON s.id = p.season_id
					INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_league AS l ON l.id = p.league_id
					INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_team AS t ON t.id = pt.team_id
					INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_project_team AS tt ON pt.team_id = tt.team_id AND pt.project_id = tt.project_id
					INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_position AS pos ON pos.id = pt.project_position_id
					WHERE person_id='" . $person_id . "' " . $filter . "
					GROUP BY pt.id	ORDER BY	s.ordering " . $order . ",
												l.ordering " . $order . ",
												p.name
									ASC";
		$this->_db->setQuery( $query );
		$result = $this->_db->loadObjectList();
		return $result;
	}

	/**
	 * get person history across all projects, with team, season, position,... info
	 *
	 * @param int $person_id , linked to person_id from person object
	 * @param int $order ordering for season and league
	 * @param string $filter e.g. "s.name = 2007/2008", default empty string
	 * @return array of objects
	 */
	function jl_getStaffHistory( $person_id, $order = 'ASC', $published = 1, $filter = "" )
	{
		if ( $published )
		{
			$filter .= " AND p.published = 1 ";
		}

		$query = "	SELECT	ts.teamstaff_id AS tsid,
							ts.person_id AS pid,
							p.id AS project_id,
							t.name AS teamname,
							p.name AS pname,
							s.name AS sname,
							tt.id AS ttid,
							pos.name AS position
					FROM	#__".COM_SPORTSMANAGEMENT_TABLE."_team_staff AS ts
					INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_project_team AS tt ON tt.id = ts.projectteam_id
					INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_project AS p ON p.id = tt.project_id
					INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_season AS s ON s.id = p.season_id
					INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_league AS l ON l.id = p.league_id
					INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_team AS t ON t.id = tt.team_id
					INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_position AS pos ON pos.id = ts.project_position_id
					WHERE person_id= '" . $person_id . "' " . $filter . "
					GROUP BY ts.teamstaff_id	ORDER BY	s.ordering " . $order . ",
											l.ordering " . $order . ",
											p.name
											ASC";

		$this->_db->setQuery( $query );
		if ( !$result = $this->_db->loadObjectList() )
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
			return false;
		}
		else
		{
			return $result;
		}
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
	   $mainframe = JFactory::getApplication();
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
	   $mainframe = JFactory::getApplication();
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
	   $mainframe = JFactory::getApplication();
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
	   $mainframe = JFactory::getApplication();
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
	   $mainframe = JFactory::getApplication();
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
	   $mainframe = JFactory::getApplication();
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
