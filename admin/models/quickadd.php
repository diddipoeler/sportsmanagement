<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      seasons.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage seasons
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementModelQuickAdd
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2019
 * @version $Id$
 * @access public
 */
class sportsmanagementModelQuickAdd extends JSMModelList
{

	var $_identifier = "quickadd";
	

	/**
	 * sportsmanagementModelQuickAdd::getNotAssignedPlayers()
	 * 
	 * @param mixed $searchterm
	 * @param mixed $projectteam_id
	 * @param mixed $searchinfo
	 * @return
	 */
	function getNotAssignedPlayers($searchterm, $projectteam_id,$searchinfo = NULL )
	{
		$query  = "	SELECT pl.*, pl.id as id2 
					FROM #__sportsmanagement_person AS pl
					WHERE	(	LOWER( CONCAT(pl.firstname, ' ', pl.lastname) ) LIKE " . $this->_db->Quote("%" . $searchterm . "%") . " OR
								alias LIKE " . $this->_db->Quote("%" . $searchterm . "%") . " OR
								nickname LIKE " . $this->_db->Quote("%" . $searchterm . "%") . " OR
								id = " . $this->_db->Quote($searchterm) . ")
								AND pl.published = '1'
								AND pl.id NOT IN ( SELECT person_id
								FROM #__sportsmanagement_team_player AS tp
								WHERE	projectteam_id = ". $this->_db->Quote($projectteam_id) . " AND
										tp.person_id = pl.id ) ";

		if ( $searchinfo )
        {
            $query .= " AND pl.info LIKE '".$searchinfo . "' ";
        }
        
        $option = Factory::getApplication()->input->getCmd('option');
		$app	= Factory::getApplication();

		$filter_order		= $app->getUserStateFromRequest( $option . 'pl_filter_order', 'filter_order', 'pl.lastname', 'cmd' );
		$filter_order_Dir	= $app->getUserStateFromRequest( $option . 'pl_filter_order_Dir',	'filter_order_Dir', '',	'word' );

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

	
    
	/**
	 * sportsmanagementModelQuickAdd::getNotAssignedStaff()
	 * 
	 * @param mixed $searchterm
	 * @param mixed $projectteam_id
	 * @param mixed $searchinfo
	 * @return
	 */
	function getNotAssignedStaff($searchterm, $projectteam_id,$searchinfo = NULL)
	{
		$query  = "SELECT pl.* ";
		$query .= "FROM #__sportsmanagement_person AS pl ";
		$query .= "WHERE (LOWER( CONCAT(pl.firstname, ' ', pl.lastname) ) LIKE ".$this->_db->Quote("%".$searchterm."%")." ";
		$query .= "   OR alias LIKE ".$this->_db->Quote("%".$searchterm."%")." ";
		$query .= "   OR nickname LIKE ".$this->_db->Quote("%".$searchterm."%")." ";
		$query .= "   OR pl.id = ".$this->_db->Quote($searchterm) . ") ";
		$query .= "   AND pl.published = '1'";
		$query .= "   AND pl.id NOT IN ( SELECT person_id ";
		$query .= "                     FROM #__sportsmanagement_team_staff AS ts ";
		$query .= "                     WHERE projectteam_id = ". $this->_db->Quote($projectteam_id);
		$query .= "                     AND ts.person_id = pl.id ) ";
		
		if ( $searchinfo )
        {
            $query .= " AND pl.info LIKE '".$searchinfo . "' ";
        }
        
        $option = Factory::getApplication()->input->getCmd('option');
		$app	= Factory::getApplication();

		$filter_order		= $app->getUserStateFromRequest( $option . 'pl_filter_order', 'filter_order', 'pl.lastname', 'cmd' );
		$filter_order_Dir	= $app->getUserStateFromRequest( $option . 'pl_filter_order_Dir',	'filter_order_Dir', '',	'word' );

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

	
	/**
	 * sportsmanagementModelQuickAdd::getNotAssignedReferees()
	 * 
	 * @param mixed $searchterm
	 * @param mixed $projectid
	 * @param mixed $searchinfo
	 * @return
	 */
	function getNotAssignedReferees($searchterm, $projectid,$searchinfo = NULL)
	{
		$query  = "SELECT pl.* ";
		$query .= "FROM #__sportsmanagement_person AS pl ";
		$query .= "WHERE (LOWER( CONCAT(pl.firstname, ' ', pl.lastname) ) LIKE ".$this->_db->Quote("%".$searchterm."%")." ";
		$query .= "   OR alias LIKE ".$this->_db->Quote("%".$searchterm."%")." ";
		$query .= "   OR nickname LIKE ".$this->_db->Quote("%".$searchterm."%")." ";
		$query .= "   OR pl.id = ".$this->_db->Quote($searchterm) . ") ";
		$query .= "   AND pl.published = '1'";
		$query .= "   AND pl.id NOT IN ( SELECT person_id ";
		$query .= "                     FROM #__sportsmanagement_project_referee AS pr ";
		$query .= "                     WHERE project_id = ". $this->_db->Quote($projectid);
		$query .= "                     AND pr.person_id = pl.id ) ";
		
        
        if ( $searchinfo )
        {
            $query .= " AND pl.info LIKE '".$searchinfo . "' ";
        }
        
        $option = Factory::getApplication()->input->getCmd('option');
		$app	= Factory::getApplication();

		$filter_order		= $app->getUserStateFromRequest( $option . 'pl_filter_order', 'filter_order', 'pl.lastname', 'cmd' );
		$filter_order_Dir	= $app->getUserStateFromRequest( $option . 'pl_filter_order_Dir',	'filter_order_Dir', '',	'word' );

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

	
	/**
	 * sportsmanagementModelQuickAdd::getNotAssignedTeams()
	 * 
	 * @param mixed $searchterm
	 * @param mixed $projectid
	 * @return
	 */
	function getNotAssignedTeams($searchterm, $projectid)
	{
		$query  = "SELECT t.* ";
		$query .= "FROM #__sportsmanagement_team AS t ";
		$query .= "WHERE (LOWER( t.name ) LIKE ".$this->_db->Quote("%".$searchterm."%")." ";
		$query .= "   OR alias LIKE ".$this->_db->Quote("%".$searchterm."%")." ";
		$query .= "   OR LOWER( short_name ) LIKE ".$this->_db->Quote("%".$searchterm."%")." ";
		$query .= "   OR LOWER( middle_name ) LIKE ".$this->_db->Quote("%".$searchterm."%")." ";
		$query .= "   OR id = ".$this->_db->Quote($searchterm) . ") ";
		$query .= "   AND t.id NOT IN ( SELECT team_id ";
		$query .= "                     FROM #__sportsmanagement_project_team AS pt ";
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
	
	/**
	 * sportsmanagementModelQuickAdd::addPlayer()
	 * 
	 * @param mixed $projectteam_id
	 * @param mixed $personid
	 * @param mixed $name
	 * @return
	 */
	function addPlayer($projectteam_id, $personid, $name = null)
	{		
		if ( !$personid && empty($name) ) {
			$this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_QUICKADD_CTRL_ADD_PLAYER_REQUIRES_ID_OR_NAME'));
			return false;
		}
	
		// add the new individual as their name was sent through.
		if (!$personid)
		{
			$mdlPerson = BaseDatabaseModel::getInstance('Person', 'sportsmanagementModel');
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
			$this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_QUICKADD_CTRL_FAILED_ADDING_PERSON'));
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
			$tblTeamplayer = Table::getInstance( 'Teamplayer', 'Table' );
			$tblTeamplayer->person_id		= $personid;
			$tblTeamplayer->projectteam_id	= $projectteam_id;
				
			$tblProjectTeam = Table::getInstance( 'Projectteam', 'Table' );
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
