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

jimport('joomla.application.component.modellist');


/**
 * Sportsmanagement Component projectteam Model
 *
 * @package	Sportsmanagement
 * @since	0.1
 */
class sportsmanagementModelProjectteams extends JModelList
{
	var $_identifier = "pteams";
    var $_project_id = 0;

	protected function getListQuery()
	{
	   $option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        $db	= $this->getDbo();
		$query = $db->getQuery(true);
        $subQuery= $db->getQuery(true);
        $subQuery2= $db->getQuery(true);
		$user = JFactory::getUser(); 
        $show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0) ;
		$this->_project_id = $mainframe->getUserState( "$option.pid", '0' );
        
        // Select some fields
		$query->select('tl.id AS projectteamid,tl.*');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS tl');
        
        // count team player
        $subQuery->select('count(tp.id)');
        $subQuery->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player tp');
        $subQuery->where('tp.published = 1 and tp.projectteam_id  = tl.id');
        $query->select('(' . $subQuery . ') AS playercount');
        // count team staff
        $subQuery2->select('count(ts.id)');
        $subQuery2->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_staff ts');
        $subQuery->where('ts.published = 1 and ts.projectteam_id  = tl.id');
        $query->select('(' . $subQuery2 . ') AS staffcount');
        
        // Join over the team
		$query->select('t.name,t.club_id');
		$query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_team t on tl.team_id = t.id');
        // Join over the club
		$query->select('c.email AS club_email');
		$query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_club c on t.club_id = c.id');
        
        // Join over the playground
		$query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_playground plg on plg.id = tl.standard_playground');
        // Join over the division
		$query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_division d on d.id = tl.division_id');
        
        // Join over the users for the checked out user.
		$query->select('u.name AS editor,u.email AS email');
		$query->join('LEFT', '#__users AS u on tl.admin = u.id');
        
        if (self::_buildContentWhere())
		{
        $query->where(self::_buildContentWhere());
        }
		$query->order(self::_buildContentOrderBy());

        if ( $show_debug_info )
        {
        $mainframe->enqueueMessage('getListQuery _project_id<br><pre>'.print_r($this->_project_id, true).'</pre><br>','Notice');
        $mainframe->enqueueMessage('getListQuery query<br><pre>'.print_r($query, true).'</pre><br>','Notice');
        }

		return $query;
	}

	function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
		$filter_order		= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier.'.tl_filter_order',		'filter_order',		't.name',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier.'.tl_filter_order_Dir',	'filter_order_Dir',	'',			'word' );

		if ( $filter_order == 't.name' )
		{
			$orderby 	= ' t.name ' . $filter_order_Dir;
		}
		else
		{
			$orderby 	= ' ' . $filter_order . ' ' . $filter_order_Dir . ' , t.name ';
		}

		return $orderby;
	}

	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
		$where=array();
		
		$division	= (int) $mainframe->getUserStateFromRequest($option.'tl_division', 'division', 0);
		$where[] 	= ' tl.project_id = ' . $this->_project_id;
		$division=JString::strtolower($division);
		if ($division>0)
		{
			$where[]=' d.id = '.$this->_db->Quote($division);
		}
		$where=(count($where) ? ' '.implode(' AND ',$where) : '');
		
		return $where;
	}

	/**
	 * Method to update project teams list
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	function store( $data )
	{
		$result = true;
		$peid = $data['project_teamslist'];
		if ( $peid == null )
		{
			$query = "	DELETE
						FROM #__".COM_SPORTSMANAGEMENT_TABLE."_project_team
						WHERE project_id = '" . $data['id'] . "'";
			$this->_db->setQuery( $query );
			if ( !$this->_db->query() )
			{
				$this->setError( $this->_db->getErrorMsg() );
				$result = false;
			}
		}
		else
		{
			JArrayHelper::toInteger( $peid );
			$peids = implode( ',', $peid );
			$query = "	DELETE
						FROM #__".COM_SPORTSMANAGEMENT_TABLE."_project_team
						WHERE project_id = '" . $data['id'] . "' AND team_id NOT IN  (" . $peids . ")";
			$this->_db->setQuery( $query );
			if ( !$this->_db->query() )
			{
				$this->setError( $this->_db->getErrorMsg() );
				$result = false;
			}

			$query = "	UPDATE  #__".COM_SPORTSMANAGEMENT_TABLE."_match
						SET projectteam1_id = NULL 
						WHERE projectteam1_id in (select id from #__".COM_SPORTSMANAGEMENT_TABLE."_project_team 
												where project_id = '" . $data['id'] . "' 
												AND team_id NOT IN  (" . $peids . "))";
			$this->_db->setQuery( $query );
			if ( !$this->_db->query() )
			{
				$this->setError( $this->_db->getErrorMsg() );
				$result = false;
			}
			$query = "	UPDATE  #__".COM_SPORTSMANAGEMENT_TABLE."_match
						SET projectteam2_id = NULL 
						WHERE projectteam2_id in (select id from #__".COM_SPORTSMANAGEMENT_TABLE."_project_team 
												where project_id = '" . $data['id'] . "' 
												AND team_id NOT IN  (" . $peids . "))";
			$this->_db->setQuery( $query );
			if ( !$this->_db->query() )
			{
				$this->setError( $this->_db->getErrorMsg() );
				$result = false;
			}
				
		}

		for ( $x = 0; $x < count( $data['project_teamslist'] ); $x++ )
		{
			$query = "	INSERT IGNORE
						INTO #__".COM_SPORTSMANAGEMENT_TABLE."_project_team
						(project_id, team_id)
						VALUES ( '" . $data['id'] . "', '".$data['project_teamslist'][$x] . "')";

			$this->_db->setQuery( $query );
			if ( !$this->_db->query() )
			{
				$this->setError( $this->_db->getErrorMsg() );
				$result = false;
			}
		}
		return $result;
	}

	

	/**
	 * Method to return the teams array (id, name)
	 *
	 * @access  public
	 * @return  array
	 * @since 0.1
	 */
	function getTeams()
	{
		$query = '	SELECT	id AS value,
							name AS text,
							info
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team
					ORDER BY text ASC ';

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

	function setNewTeamID()
	{
		global $mainframe;
		$mainframe	= JFactory::getApplication();

		$post=JRequest::get('post');
		$oldteamid=JRequest::getVar('oldteamid',array(),'post','array');
		$newteamid=JRequest::getVar('newteamid',array(),'post','array');

		for ($a=0; $a < sizeof($oldteamid); $a++ )
		{
			$project_team_id = $oldteamid[$a];
			$project_team_id_new = $newteamid[$project_team_id];
			$query = 'SELECT t.name
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team as t
					inner join #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team as pt
					on t.id = pt.team_id 
					WHERE pt.id='.$project_team_id;
			$this->_db->setQuery($query);
			$old_team_name = $this->_db->loadResult();

			$query = 'SELECT t.name
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team as t
					WHERE t.id='.$project_team_id_new;
			$this->_db->setQuery($query);
			$new_team_name = $this->_db->loadResult();

			$mainframe->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAM_MODEL_ASSIGNED_OLD_TEAMNAME', $old_team_name, $new_team_name),'Notice');

			$tabelle = '#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team';
			// Objekt erstellen
			$wertneu = new StdClass();
			// Werte zuweisen
			$wertneu->id = $project_team_id;
			$wertneu->team_id = $project_team_id_new;

			// Neue Werte in den vorher erstellten Datenbankeintrag einf?gen
			$this->_db->updateObject($tabelle, $wertneu, 'id');

		}

	}





	/**
	 * Method to return a Teams array (id,name)
	 *
	 * @access	public
	 * @return	array seasons
	 * @since	1.5.0a
	 */
	function getAllTeams($pid)
	{
		$db = JFactory::getDBO();
		$mainframe = JFactory::getApplication();
		if ( $pid[0] )
		{
			// jetzt brauchen wir noch das land der liga !
			$querycountry = "SELECT l.country
							from #__".COM_SPORTSMANAGEMENT_TABLE."_league as l
							inner join #__".COM_SPORTSMANAGEMENT_TABLE."_project as p
							on p.league_id = l.id
							where p.id = '$pid'
							";

			$db->setQuery( $querycountry );
			$country = $db->loadResult();

      if ( $country )
      {
      $query="SELECT t.id as value, concat(t.name,' [',t.info,']' ) as text
					FROM #__".COM_SPORTSMANAGEMENT_TABLE."_team as t
					INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_club as c
					ON c.id = t.club_id
					WHERE c.country = '$country'  
					ORDER BY t.name ASC 
					";
      }
      else
      {
      $mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_NO_LEAGUE_COUNTRY'),'Error');
      $mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_SELECT_ALL_TEAMS'),'Notice');
      $query="SELECT t.id as value, concat(t.name,' [',t.info,']' ) as text
					FROM #__".COM_SPORTSMANAGEMENT_TABLE."_team as t
					INNER JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_club as c
					ON c.id = t.club_id
					ORDER BY t.name ASC 
					";
      }
      
      
			

		}
		else
		{
			$query="SELECT t.id as value, concat(t.name,' [',t.info,']' ) as text 
					FROM #__".COM_SPORTSMANAGEMENT_TABLE."_team as t 
					ORDER BY name ASC ";
		}

		$db->setQuery($query);
		if (!$result=$db->loadObjectList())
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
		foreach ($result as $teams){
			$teams->name=JText::_($teams->text);
		}
		return $result;
	}



	/**
	 * Method to return the project teams array (id, name)
	 *
	 * @param $project_id
	 * @access  public
	 * @return  array
	 * @since 0.1
	 */
	function getProjectTeams($project_id=0)
	{
		$query = '	SELECT	t.id AS value,
							t.name AS text,
							t.notes, pt.info
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = t.id
					WHERE pt.project_id = ' . $project_id . '
					ORDER BY text ASC ';

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
	 * copy teams to other projects
	 * 
	 * @param int $dest destination project id
	 * @param array $ptids teams to transfer
	 */
	function copy($dest, $ptids)
	{
		if (!$dest)
		{
			$this->setError(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_Destination_project_required'));
			return false;
		}
		
		if (!is_array($ptids) || !count($ptids))
		{
			$this->setError(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_no_teams_to_copy'));
			return false;
		}
		
		// first copy the teams
		$query = ' INSERT INTO #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team (team_id, project_id, info, picture, standard_playground, extended)' 
		       . ' SELECT team_id, '.$dest.', info, picture, standard_playground, extended '
		       . ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team '
		       . ' WHERE id IN (' . implode(',', $ptids).')';
		$this->_db->setQuery($query);
		$res = $this->_db->query();
		
		if (!$res) 
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		
		// now copy the players
		$query = ' INSERT INTO #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player (projectteam_id, person_id, jerseynumber, picture, extended, published) ' 
		       . ' SELECT dest.id AS projectteam_id, tp.person_id, tp.jerseynumber, tp.picture, tp.extended,tp.published '
		       . ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tp '
		       . ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.id = tp.projectteam_id '
		       . ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS dest ON pt.team_id = dest.team_id AND dest.project_id = '.$dest 
		       . ' WHERE pt.id IN (' . implode(',', $ptids).')';
		$this->_db->setQuery($query);
		$res = $this->_db->query();
				
		// and finally the staff
		$query = ' INSERT INTO #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_staff (projectteam_id, person_id, picture, extended, published) '
				       . ' SELECT dest.id AS projectteam_id, tp.person_id, tp.picture, tp.extended,tp.published '
				       . ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team_staff AS tp '
				       . ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.id = tp.projectteam_id '
				       . ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS dest ON pt.team_id = dest.team_id AND dest.project_id = '.$dest 
		. ' WHERE pt.id IN (' . implode(',', $ptids).')';
		$this->_db->setQuery($query);
		$res = $this->_db->query();
		
		if (!$res) 
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		
		
		return true;
	}

	/**
	 * return count of projectteams
	 *
	 * @param int project_id
	 * @return int
	 */
	function getProjectTeamsCount($project_id)
	{
		$query='SELECT count(*) AS count
				FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt
				JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p on p.id = pt.project_id
				WHERE p.id='.$project_id;
		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}
	
	
}
?>
