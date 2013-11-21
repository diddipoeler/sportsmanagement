<?php defined( '_JEXEC' ) or die( 'Restricted access' ); // Check to ensure this file is included in Joomla!
/**
 * @copyright	Copyright (C) 2007 Joomteam.de. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once ( JPATH_COMPONENT . DS . 'models' . DS . 'list.php' );

/**
 * Joomleague Component person search Model
 *
 * @package	JoomLeague
 * @since	1.5
 */
class JoomleagueModelQuickAdd extends JoomleagueModelList
{

	var $_identifier = "quickadd";
	
	/*
	 * @param {string} query - the search string
	 * @param {int} projectteam_id - the projectteam_id
	 */
	function getNotAssignedPlayers($searchterm, $projectteam_id,$searchinfo = NULL )
	{
		$query  = "	SELECT pl.*, pl.id as id2 
					FROM #__joomleague_person AS pl
					WHERE	(	LOWER( CONCAT(pl.firstname, ' ', pl.lastname) ) LIKE " . $this->_db->Quote("%" . $searchterm . "%") . " OR
								alias LIKE " . $this->_db->Quote("%" . $searchterm . "%") . " OR
								nickname LIKE " . $this->_db->Quote("%" . $searchterm . "%") . " OR
								id = " . $this->_db->Quote($searchterm) . ")
								AND pl.published = '1'
								AND pl.id NOT IN ( SELECT person_id
								FROM #__joomleague_team_player AS tp
								WHERE	projectteam_id = ". $this->_db->Quote($projectteam_id) . " AND
										tp.person_id = pl.id ) ";

		if ( $searchinfo )
        {
            $query .= " AND pl.info LIKE '".$searchinfo . "' ";
        }
        
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
		$query = $query . $orderby;
		$this->_db->setQuery( $query );
		
		if ( !$this->_data = $this->_getList( 	$query, $this->getState( 'limitstart' ), $this->getState( 'limit' ) ) )
		{
			echo $this->_db->getErrorMsg();
		}
		$this->_total = $this->_getListCount( $query );
		return $this->_data;
	}

	/*
	 * @param {string} query - the search string
	 * @param {int} projectteam_id - the projectteam_id
	 */
	function getNotAssignedStaff($searchterm, $projectteam_id,$searchinfo = NULL)
	{
		$query  = "SELECT pl.* ";
		$query .= "FROM #__joomleague_person AS pl ";
		$query .= "WHERE (LOWER( CONCAT(pl.firstname, ' ', pl.lastname) ) LIKE ".$this->_db->Quote("%".$searchterm."%")." ";
		$query .= "   OR alias LIKE ".$this->_db->Quote("%".$searchterm."%")." ";
		$query .= "   OR nickname LIKE ".$this->_db->Quote("%".$searchterm."%")." ";
		$query .= "   OR pl.id = ".$this->_db->Quote($searchterm) . ") ";
		$query .= "   AND pl.published = '1'";
		$query .= "   AND pl.id NOT IN ( SELECT person_id ";
		$query .= "                     FROM #__joomleague_team_staff AS ts ";
		$query .= "                     WHERE projectteam_id = ". $this->_db->Quote($projectteam_id);
		$query .= "                     AND ts.person_id = pl.id ) ";
		
		if ( $searchinfo )
        {
            $query .= " AND pl.info LIKE '".$searchinfo . "' ";
        }
        
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
		$query = $query . $orderby;
		$this->_db->setQuery( $query );
		if ( !$this->_data = $this->_getList( 	$query,
												$this->getState( 'limitstart' ),
												$this->getState( 'limit' ) ) )
		{
			echo $this->_db->getErrorMsg();
		}
		$this->_total = $this->_getListCount( $query );
		return $this->_data;
	}

	/*
	 * @param {string} query - the search string
	 * @param {int} projectteam_id - the projectteam_id
	 */
	function getNotAssignedReferees($searchterm, $projectid,$searchinfo = NULL)
	{
		$query  = "SELECT pl.* ";
		$query .= "FROM #__joomleague_person AS pl ";
		$query .= "WHERE (LOWER( CONCAT(pl.firstname, ' ', pl.lastname) ) LIKE ".$this->_db->Quote("%".$searchterm."%")." ";
		$query .= "   OR alias LIKE ".$this->_db->Quote("%".$searchterm."%")." ";
		$query .= "   OR nickname LIKE ".$this->_db->Quote("%".$searchterm."%")." ";
		$query .= "   OR pl.id = ".$this->_db->Quote($searchterm) . ") ";
		$query .= "   AND pl.published = '1'";
		$query .= "   AND pl.id NOT IN ( SELECT person_id ";
		$query .= "                     FROM #__joomleague_project_referee AS pr ";
		$query .= "                     WHERE project_id = ". $this->_db->Quote($projectid);
		$query .= "                     AND pr.person_id = pl.id ) ";
		
        
        if ( $searchinfo )
        {
            $query .= " AND pl.info LIKE '".$searchinfo . "' ";
        }
        
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
		$query = $query . $orderby;
		
		$this->_db->setQuery( $query );
		
		if ( !$this->_data = $this->_getList( 	$query,
												$this->getState( 'limitstart' ),
												$this->getState( 'limit' ) ) )
		{
			echo $this->_db->getErrorMsg();
		}
		$this->_total = $this->_getListCount( $query );
		return $this->_data;
	}

	/*
	 * @param {string} query - the search string
	 * @param {int} projectteam_id - the projectteam_id
	 */
	function getNotAssignedTeams($searchterm, $projectid)
	{
		$query  = "SELECT t.* ";
		$query .= "FROM #__joomleague_team AS t ";
		$query .= "WHERE (LOWER( t.name ) LIKE ".$this->_db->Quote("%".$searchterm."%")." ";
		$query .= "   OR alias LIKE ".$this->_db->Quote("%".$searchterm."%")." ";
		$query .= "   OR LOWER( short_name ) LIKE ".$this->_db->Quote("%".$searchterm."%")." ";
		$query .= "   OR LOWER( middle_name ) LIKE ".$this->_db->Quote("%".$searchterm."%")." ";
		$query .= "   OR id = ".$this->_db->Quote($searchterm) . ") ";
		$query .= "   AND t.id NOT IN ( SELECT team_id ";
		$query .= "                     FROM #__joomleague_project_team AS pt ";
		$query .= "                     WHERE project_id = ". $this->_db->Quote($projectid);
		$query .= ") ";

		$this->_db->setQuery( $query);

		if ( !$this->_data = $this->_getList( 	$query,
												$this->getState( 'limitstart' ),
												$this->getState( 'limit' ) ) )
		{
			echo $this->_db->getErrorMsg();
		}
		$this->_total = $this->_getListCount( $query );
		return $this->_data;
	}
	
	function addPlayer($projectteam_id, $personid, $name = null)
	{		
		if ( !$personid && empty($name) ) {
			$this->setError(Jtext::_('COM_JOOMLEAGUE_ADMIN_QUICKADD_CTRL_ADD_PLAYER_REQUIRES_ID_OR_NAME'));
			return false;
		}
	
		// add the new individual as their name was sent through.
		if (!$personid)
		{
			$mdlPerson = JLGModel::getInstance('Person', 'JoomleagueModel');
			$name = explode(" ", $name);
			$firstname = ''; $nickname=''; $lastname='';
			if(count($name) == 1) {
				$firstname = ucfirst($name[0]);
				$nickname = $name[0];
				$lastname = ".";
			}
			if(count($name) == 2) {
				$firstname = ucfirst($name[0]);
				$nickname = $name[1];
				$lastname = ucfirst($name[1]);
			}
			if(count($name) == 3) {
				$firstname = ucfirst($name[0]);
				$nickname = $name[1];
				$lastname = ucfirst($name[2]);
			}
			$data = array(
					"firstname" => $firstname,
					"nickname" => $nickname,
					"lastname" => $lastname,
					"published" => 1
			);
			$mdlPerson->store($data);
			$personid = $mdlPerson->_db->insertid();
		}
	
		if (!$personid) {
			$this->setError(Jtext::_('COM_JOOMLEAGUE_ADMIN_QUICKADD_CTRL_FAILED_ADDING_PERSON'));
			return false;
		}
	
		// check if indivual belongs to project team already
		$query = ' SELECT person_id FROM #__joomleague_team_player '
		. ' WHERE projectteam_id = '. $this->_db->Quote($projectteam_id)
		. '   AND person_id = '. $this->_db->Quote($personid);
		$this->_db->setQuery($query);
		$res = $this->_db->loadResult();
		if (!$res)
		{
			$tblTeamplayer = JTable::getInstance( 'Teamplayer', 'Table' );
			$tblTeamplayer->person_id		= $personid;
			$tblTeamplayer->projectteam_id	= $projectteam_id;
				
			$tblProjectTeam = JTable::getInstance( 'Projectteam', 'Table' );
			$tblProjectTeam->load($projectteam_id);
	
			if ( !$tblTeamplayer->check() )
			{
				$this->setError( $tblTeamplayer->getError() );
				return false;
			}
			// Get data from player
			$query = "	SELECT picture, position_id
							FROM #__joomleague_person AS pl
							WHERE pl.id=". $this->_db->Quote($personid) . "
							AND pl.published = 1";
	
			$this->_db->setQuery( $query );
			$person = $this->_db->loadObject();
			if ( $person )
			{
				if ($person->position_id)
				{
					$query = "SELECT id FROM #__joomleague_project_position ";
					$query.= " WHERE position_id = " . $person->position_id;
					$query.= " AND project_id = " . $tblProjectTeam->project_id;
					$this->_db->setQuery($query);
					if ($resPrjPosition = $this->_db->loadObject())
					{
						$tblTeamplayer->project_position_id = $resPrjPosition->id;
					}
				}
	
				$tblTeamplayer->picture			= $person->picture;
				$tblTeamplayer->projectteam_id	= $projectteam_id;
	
			}
			$query = "	SELECT max(ordering) count
								FROM #__joomleague_team_player";
			$this->_db->setQuery( $query );
			$tp = $this->_db->loadObject();
			$tblTeamplayer->ordering = (int) $tp->count + 1;
				
			if ( !$tblTeamplayer->store() )
			{
				$this->setError( $tblTeamplayer->getError() );
				return false;
			}
		}
		return true;
	}
}
?>
