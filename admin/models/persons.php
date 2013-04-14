<?php
/**
 * @copyright	Copyright (C) 2006-2013 JoomLeague.net. All rights reserved.
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
//require_once ( JPATH_COMPONENT . DS . 'models' . DS . 'list.php' );

/**
 * Joomleague Component Persons Model
 *
 * @author	Kurt Norgaz
 * @package	JoomLeague
 * @since	1.5
 */
class sportsmanagementModelPersons extends JModelList
{
	var $_identifier = "persons";

	function getListQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();

		$query = '	SELECT	pl.*, u.name AS editor
					FROM #__sportsmanagement_person AS pl
					LEFT JOIN #__users AS u
					 ON u.id = pl.checked_out ';

		$query .= $where;
		$query .= $orderby;
		//echo '<br />~' . $query . '~<br />';
		return $query;
	}

	function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();

		$filter_order		= $mainframe->getUserStateFromRequest( $option . 'pl_filter_order', 'filter_order', 'pl.lastname', 'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option . 'pl_filter_order_Dir',	'filter_order_Dir', '',	'word' );

		if ( $filter_order == 'pl.lastname' )
		{
			$orderby 	= ' ORDER BY pl.lastname ' . $filter_order_Dir;
		}
		else
		{
			$orderby 	= ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir . ' , pl.lastname ';
		}

		return $orderby;
	}

	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();

		$filter_state		= $mainframe->getUserStateFromRequest( $option . 'pl_filter_state', 'filter_state', '', 'word' );
		$filter_order		= $mainframe->getUserStateFromRequest( $option . 'pl_filter_order', 'filter_order', 'pl.lastname', 'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option . 'pl_filter_order_Dir',	'filter_order_Dir', '',	'word' );
		$search				= $mainframe->getUserStateFromRequest( $option . 'pl_search', 'search',	'', 'string');
		$search_mode		= $mainframe->getUserStateFromRequest( $option . 'pl_search_mode', 'search_mode', '', 'string');
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

		$where = ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );
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
					FROM #__sportsmanagement_team_player AS pt
					INNER JOIN #__sportsmanagement_project AS p ON p.id = pt.project_id
					INNER JOIN #__sportsmanagement_season AS s ON s.id = p.season_id
					INNER JOIN #__sportsmanagement_league AS l ON l.id = p.league_id
					INNER JOIN #__sportsmanagement_team AS t ON t.id = pt.team_id
					INNER JOIN #__sportsmanagement_project_team AS tt ON pt.team_id = tt.team_id AND pt.project_id = tt.project_id
					INNER JOIN #__sportsmanagement_position AS pos ON pos.id = pt.project_position_id
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
					FROM	#__sportsmanagement_team_staff AS ts
					INNER JOIN #__sportsmanagement_project_team AS tt ON tt.id = ts.projectteam_id
					INNER JOIN #__sportsmanagement_project AS p ON p.id = tt.project_id
					INNER JOIN #__sportsmanagement_season AS s ON s.id = p.season_id
					INNER JOIN #__sportsmanagement_league AS l ON l.id = p.league_id
					INNER JOIN #__sportsmanagement_team AS t ON t.id = tt.team_id
					INNER JOIN #__sportsmanagement_position AS pos ON pos.id = ts.project_position_id
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
					FROM #__sportsmanagement_person AS pl
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
					FROM #__sportsmanagement_team AS t
					INNER JOIN	#__sportsmanagement_project_team AS tt ON tt.team_id = t.id
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
		$query = ' SELECT name FROM #__sportsmanagement_team WHERE id = ' . $team_id;
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
					FROM #__sportsmanagement_team AS t
					INNER JOIN #__sportsmanagement_project_team AS pt
					ON t.id=pt.team_id
					WHERE pt.id = '. $this->_db->Quote($project_team_id);
		$this->_db->setQuery( $query );
		return $this->_db->loadResult();
	}

}
?>