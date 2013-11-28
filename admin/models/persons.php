<?php
/**
 * @copyright	Copyright (C) 2013 fussballineuropa.de. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.modellist' );


/**
 * Sportsmanagement Component Persons Model
 *
 * @author	diddipoeler
 * @package	Sportsmanagement
 * @since	1.5
 */
class sportsmanagementModelPersons extends JModelList
{
	var $_identifier = "persons";

	function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $this->_type = JRequest::getInt('type');
        $this->_project_id	= $mainframe->getUserState( "$option.pid", '0' );
        $this->_team_id = $mainframe->getUserState( "$option.team_id", '0' );;
        $this->_project_team_id = $mainframe->getUserState( "$option.project_team_id", '0' );;
        $search	= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
        $search_nation		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search_nation','search_nation','','word');
        
//        $mainframe->enqueueMessage(JText::_('sportsmanagementModelPersons getListQuery layout<br><pre>'.print_r(JRequest::getVar('layout'),true).'</pre>'),'Notice');
//        $mainframe->enqueueMessage(JText::_('sportsmanagementModelPersons getListQuery _type<br><pre>'.print_r($this->_type,true).'</pre>'),'Notice');
//        $mainframe->enqueueMessage(JText::_('sportsmanagementModelPersons getListQuery search<br><pre>'.print_r($search,true).'</pre>'),'Notice');
//        $mainframe->enqueueMessage(JText::_('sportsmanagementModelPersons getListQuery _project_id<br><pre>'.print_r($this->_project_id,true).'</pre>'),'Notice');
//        $mainframe->enqueueMessage(JText::_('sportsmanagementModelPersons getListQuery _team_id<br><pre>'.print_r($this->_team_id,true).'</pre>'),'Notice');
//        $mainframe->enqueueMessage(JText::_('sportsmanagementModelPersons getListQuery _project_team_id<br><pre>'.print_r($this->_project_team_id,true).'</pre>'),'Notice');
        
        // Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
        $Subquery	= $db->getQuery(true);
		$user	= JFactory::getUser(); 
		
        // Select some fields
		$query->select('pl.*, pl.id as id2');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person as pl');
        // Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id = pl.checked_out');
        
        
        if ($search || $search_nation)
		{
        $query->where(self::_buildContentWhere());
        }
        
        if ( JRequest::getVar('layout') == 'assignplayers')
        {
            switch ($this->_type)
            {
                case 0:
                $Subquery->select('person_id');
                $Subquery->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tp ');
                $Subquery->where('projectteam_id = '.$this->_project_team_id.' AND tp.person_id = pl.id');
                $query->where('pl.id NOT IN ('.$Subquery.')');
                break;
                case 1:
                $Subquery->select('person_id');
                $Subquery->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_staff AS ts ');
                $Subquery->where('projectteam_id = '.$this->_project_team_id.' AND ts.person_id = pl.id');
                $query->where('pl.id NOT IN ('.$Subquery.')');
                break;
                case 2:
                $Subquery->select('person_id');
                $Subquery->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS pr ');
                $Subquery->where('project_id = '.$this->_project_id.' AND pr.person_id = pl.id');
                $query->where('pl.id NOT IN ('.$Subquery.')');
                break;
                
            }
            
            
        }
        
		$query->order(self::_buildContentOrderBy());
        
        
		return $query;
        
        
        
	}

	function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
		$filter_order		= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier. '.filter_order','filter_order','pl.lastname','cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier. '.filter_order_Dir','filter_order_Dir','','word' );

		if ( $filter_order == 'pl.lastname' )
		{
			$orderby 	= '  pl.lastname ' . $filter_order_Dir;
		}
		else
		{
			$orderby 	= '  ' . $filter_order . ' ' . $filter_order_Dir . ' , pl.lastname ';
		}

		return $orderby;
	}



	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
		$filter_state		= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier. '.filter_state','filter_state','','word');
        $search_nation		= $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.search_nation','search_nation','','word');
		//$filter_order		= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier. '.filter_order', 'filter_order', 'pl.lastname', 'cmd' );
		//$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier. '.filter_order_Dir',	'filter_order_Dir', '',	'word' );
		$search				= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier. '.search','search','','string');
		$search_mode		= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier. '.search_mode','search_mode','','string');
		$project_id			= $mainframe->getUserState( $option . 'project' );
		$team_id			= $mainframe->getUserState( $option . 'team_id' );
		$project_team_id	= $mainframe->getUserState( $option . 'project_team_id' );
		$search				= JString::strtolower( $search );
		$exludePerson		= '';

		$where = array();
		if ( $search )
		{
			if ( $search_mode )
			{
				$where[] = '(LOWER(pl.lastname) LIKE ' . $this->_db->Quote( $search . '%' ) .
						   'OR LOWER(pl.firstname) LIKE ' . $this->_db->Quote( $search . '%' ) .
						   'OR LOWER(pl.nickname) LIKE ' . $this->_db->Quote( $search . '%' ) . ')';
			}
			else
			{
				$where[] = '(LOWER(pl.lastname) LIKE ' . $this->_db->Quote( '%' . $search . '%' ).
						   'OR LOWER(pl.firstname) LIKE ' . $this->_db->Quote( '%' . $search . '%' ) .
						   'OR LOWER(pl.nickname) LIKE ' . $this->_db->Quote( '%' . $search . '%' ) . ')';
			}
		}

		if ( $search_nation )
		{
		  $where[] = "pl.country = '".$search_nation."'";
        }
          
        if ( $filter_state )
		{
			if ( $filter_state == 'P' )
			{
				$where[] = 'pl.published = 1';
			}
			elseif ($filter_state == 'U' )
			{
				$where[] = 'pl.published = 0';
			}
		}

		$where = ( count( $where ) ? '  ' . implode( ' AND ', $where ) : '' );
		return $where;
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
			$this->setError( $this->_db->getErrorMsg() );
			return false;
		}
		else
		{
			return $result;
		}
	}

	/**
	 * return persons list from ids contained in var cid
	 *
	 * @return array
	 */
	function getPersonsToAssign()
	{
		$cid = JRequest::getVar( 'cid' );

		if ( !count( $cid ) )
		{
			return array();
		}

		$query = '	SELECT	pl.id,
							pl.firstname, pl.nickname,
							pl.lastname
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pl
					WHERE pl.id IN (' . implode( ', ', $cid ) . ') AND pl.published = 1';
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}


	/**
	 * return list of project teams for select options
	 *
	 * @return array
	 */
	function getProjectTeamList()
	{
		$query = '	SELECT	t.id AS value,
							t.name AS text
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t
					INNER JOIN	#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS tt ON tt.team_id = t.id
					WHERE tt.project_id = ' . $this->_project_id . '
					ORDER BY text ASC ';
		#echo '<br />'.$query.'<br />';

		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}

	/**
	 * get team name
	 *
	 * @return string
	 */
	function getTeamName( $team_id )
	{
		if ( !$team_id )
		{
			return '';
		}
		$query = ' SELECT name FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team WHERE id = ' . $team_id;
		$this->_db->setQuery( $query );
		return $this->_db->loadResult();
	}

	/**
	 * get team name
	 *
	 * @return string
	 */
	function getProjectTeamName( $project_team_id )
	{
		if ( !$project_team_id )
		{
			return '';
		}
		$query = ' SELECT t.name
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt
					ON t.id=pt.team_id
					WHERE pt.id = '. $this->_db->Quote($project_team_id);
		$this->_db->setQuery( $query );
		return $this->_db->loadResult();
	}
    
    /**
	 * Method to return the players array (projectid,teamid)
	 *
	 * @access  public
	 * @return  array
	 * @since 0.1
	 */

	function getPersons()
	{
		$query='SELECT	id AS value,
				lastname,
				firstname,
				info,
				weight,
				height,
				picture,
				birthday,
				notes,
				nickname,
				knvbnr,
				country,
				phone,
				mobile,
				email
				FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_person
				WHERE published = 1
				ORDER BY lastname ASC ';
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return $result;
	}
    
    public function getPersonListSelect()
	{
		$query ="	SELECT id AS value,firstname,lastname,nickname,birthday
						FROM #__".COM_SPORTSMANAGEMENT_TABLE."_person
						WHERE firstname<>'!Unknown' AND lastname<>'!Player' AND nickname<>'!Ghost'
						ORDER BY lastname,firstname";
		$this->_db->setQuery($query);
		if ($results=$this->_db->loadObjectList())
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
		return false;
	}
    

}
?>