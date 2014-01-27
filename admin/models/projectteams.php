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

jimport('joomla.application.component.modellist');



/**
 * sportsmanagementModelProjectteams
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelProjectteams extends JModelList
{
	var $_identifier = "pteams";
    var $_project_id = 0;
    var $_season_id = 0;
    
    var $project_art_id  = 0;
    var $sports_type_id= 0;
    

	protected function getListQuery()
	{
	   $option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        $this->_season_id	= $mainframe->getUserState( "$option.season_id", '0' );
        $this->_project_id = $mainframe->getUserState( "$option.pid", '0' );
        $this->project_art_id = $mainframe->getUserState( "$option.project_art_id", '0' );
        $this->sports_type_id = $mainframe->getUserState( "$option.sports_type_id", '0' );
        
        $db	= $this->getDbo();
		$query = $db->getQuery(true);
        $subQuery= $db->getQuery(true);
        $subQuery2= $db->getQuery(true);
		$user = JFactory::getUser(); 
        //$show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0) ;
		
        
        // Select some fields
		$query->select('tl.id AS projectteamid,tl.*');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS tl');
        
        if ( $this->project_art_id == 3 )
        {
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_person_id AS st on tl.team_id = st.id'); 
        $query->select("concat(t.lastname,' - ',t.firstname,'' ) AS name");
		$query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS t on st.person_id = t.id');   
        }
        else
        {    
        if ( COM_SPORTSMANAGEMENT_USE_NEW_TABLE )
        {
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st on tl.team_id = st.id');    
        // count team player
        $subQuery->select('count(tp.id)');
        $subQuery->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp');
        $subQuery->where('tp.published = 1');
        //$subQuery->where('tp.team_id = tl.team_id');
        $subQuery->where('tp.team_id = st.team_id');
        $subQuery->where('tp.persontype = 1');
        $subQuery->where('tp.season_id = '.$this->_season_id);
        $query->select('(' . $subQuery . ') AS playercount');
        // count team staff
        $subQuery2->select('count(tp.id)');
        $subQuery2->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp');
        $subQuery2->where('tp.published = 1');
        //$subQuery2->where('tp.team_id = tl.team_id');
        $subQuery2->where('tp.team_id = st.team_id');
        $subQuery2->where('tp.persontype = 2');
        $subQuery2->where('tp.season_id = '.$this->_season_id);
        $query->select('(' . $subQuery2 . ') AS staffcount');    
        }
        else
        {    
        // count team player
        $subQuery->select('count(tp.id)');
        $subQuery->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tp');
        $subQuery->where('tp.published = 1 and tp.projectteam_id  = tl.id');
        $query->select('(' . $subQuery . ') AS playercount');
        // count team staff
        $subQuery2->select('count(ts.id)');
        $subQuery2->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_staff AS ts');
        $subQuery2->where('ts.published = 1 and ts.projectteam_id  = tl.id');
        $query->select('(' . $subQuery2 . ') AS staffcount');
        }
        
        //$query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st on tl.team_id = st.id');
        // Join over the team
		$query->select('t.name,t.club_id');
		$query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t on st.team_id = t.id');
        // Join over the club
		$query->select('c.email AS club_email');
		$query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_club AS c on t.club_id = c.id');
        
        // Join over the playground
		$query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_playground AS plg on plg.id = tl.standard_playground');
        // Join over the division
		$query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_division AS d on d.id = tl.division_id');
        }
        
        // Join over the users for the checked out user.
		$query->select('u.name AS editor,u.email AS email');
		$query->join('LEFT', '#__users AS u on tl.admin = u.id');
        
        if (self::_buildContentWhere())
		{
        $query->where(self::_buildContentWhere());
        }
		$query->order(self::_buildContentOrderBy());
/*
select
tl.id AS projectteamid,tl.*,
concat(t.lastname,' - ',t.firstname,'' ) AS name,
u.name AS editor,u.email AS email        
from jos_sportsmanagement_project_team AS tl
left join jos_sportsmanagement_season_person_id AS st on tl.team_id = st.id
left join jos_sportsmanagement_person AS t on st.person_id = t.id
left join jos_users AS u on tl.admin = u.id
where tl.project_id = 2

*/        
        
        //$mainframe->enqueueMessage(get_class($this).' '.__FUNCTION__.' query<br><pre>'.print_r($query , true).'</pre><br>','Notice');

		return $query;
	}

	function _buildContentOrderBy()
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        $this->project_art_id = $mainframe->getUserState( "$option.project_art_id", '0' );
		//$filter_order		= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier.'.tl_filter_order','filter_order','t.name','cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier.'.tl_filter_order_Dir','filter_order_Dir','','word' );
        
        //$mainframe->enqueueMessage(get_class($this).' '.__FUNCTION__.' tl_filter_order<br><pre>'.print_r($filter_order , true).'</pre><br>','Notice');
        
        if ( $this->project_art_id == 3 )
        {
        $filter_order		= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier.'.tl_filter_order','filter_order','t.lastname','cmd' );
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(get_class($this).' '.__FUNCTION__.' tl_filter_order<br><pre>'.print_r($filter_order , true).'</pre><br>','Notice');
        }
            
        if ( $filter_order == 't.lastname' || $filter_order == 't.name' )
		{
			$orderby 	= ' t.lastname ' . $filter_order_Dir;
		}
		else
		{
			$orderby 	= ' ' . $filter_order . ' ' . $filter_order_Dir . ' , t.lastname ';
		}    
        }
        else
        {
        $filter_order		= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier.'.tl_filter_order','filter_order','t.name','cmd' );
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(get_class($this).' '.__FUNCTION__.' tl_filter_order<br><pre>'.print_r($filter_order , true).'</pre><br>','Notice');
        }
            
		if ( $filter_order == 't.name' || $filter_order == 't.lastname' )
		{
			$orderby 	= ' t.name ' . $filter_order_Dir;
		}
		else
		{
			$orderby 	= ' ' . $filter_order . ' ' . $filter_order_Dir . ' , t.name ';
		}
        
        }

		return $orderby;
	}

	function _buildContentWhere()
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
		$where=array();
		
		$division	= (int) $mainframe->getUserStateFromRequest($option.'.'.$this->_identifier.'.tl_division', 'division', 0);
		$where[] 	= ' tl.project_id = ' . $this->_project_id;
		$division = JString::strtolower($division);
        
        //$mainframe->enqueueMessage('sportsmanagementModelProjectteams _buildContentWhere division<br><pre>'.print_r($division, true).'</pre><br>','Notice');
        
		if ( $division > 0)
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
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
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
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
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
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
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
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
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
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
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
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        $this->_season_id = $mainframe->getUserState( "$option.season_id", '0' );
        $this->project_art_id = $mainframe->getUserState( "$option.project_art_id", '0' );
        $this->sports_type_id = $mainframe->getUserState( "$option.sports_type_id", '0' );
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(get_class($this).' '.__FUNCTION__.' _season_id<br><pre>'.print_r($this->_season_id , true).'</pre><br>','Notice');
        $mainframe->enqueueMessage(get_class($this).' '.__FUNCTION__.' project_art_id<br><pre>'.print_r($this->project_art_id, true).'</pre><br>','Notice');
        $mainframe->enqueueMessage(get_class($this).' '.__FUNCTION__.' sports_type_id<br><pre>'.print_r($this->sports_type_id, true).'</pre><br>','Notice');
        }
        
        $db	= $this->getDbo();
		$query = $db->getQuery(true);
        
        if ( $this->project_art_id == 3 )
        {
        // Select some fields
		$query->select("st.id AS value,concat(t.lastname,' - ',t.firstname,'' ) AS text,t.info");
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS t');
        $query->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_person_id AS st on st.person_id = t.id');
        $query->where('st.season_id = ' . $this->_season_id);
        $query->where('t.sports_type_id = ' . $this->sports_type_id);
        $query->order('t.lastname ASC');    
        }
        else
        {
        // Select some fields
		$query->select('st.id AS value,t.name AS text,t.info');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t');
        $query->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st on st.team_id = t.id');
        $query->where('st.season_id = ' . $this->_season_id);
        $query->where('t.sports_type_id = ' . $this->sports_type_id);
        $query->order('t.name ASC');
        }
/*        
        $query = '	SELECT	id AS value,
							name AS text,
							info
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team
					ORDER BY text ASC ';
*/
		$db->setQuery( $query );
        
        //$mainframe->enqueueMessage(get_class($this).' '.__FUNCTION__.' query<br><pre>'.print_r($query, true).'</pre><br>','Notice');
        //$mainframe->enqueueMessage(get_class($this).' '.__FUNCTION__.' loadObjectList<br><pre>'.print_r($db->loadObjectList(), true).'</pre><br>','Notice');
        
		if ( !$result = $db->loadObjectList() )
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
			return false;
		}
		else
		{
			return $result;
		}
	}

	function setNewTeamID()
	{
//		global $mainframe;
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
		 $option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        $this->_season_id	= $mainframe->getUserState( "$option.season_id", '0' );
        $this->project_art_id = $mainframe->getUserState( "$option.project_art_id", '0' );
        $this->sports_type_id = $mainframe->getUserState( "$option.sports_type_id", '0' );
        $db	= $this->getDbo();
		$query = $db->getQuery(true);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(get_class($this).' '.__FUNCTION__.' project_id<br><pre>'.print_r($project_id, true).'</pre><br>','Notice');
        }
        
        if ( $this->project_art_id == 3 )
        {
        // Select some fields
		$query->select("pt.id AS value,concat(t.lastname,' - ',t.firstname,'' ) AS text,t.notes, pt.info");
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS t');
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_person_id AS st on st.person_id = t.id');
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
        $query->where('pt.project_id = ' . $project_id);
        $query->order('t.lastname ASC');    
        }
        else
        {    
        // Select some fields
		$query->select('st.id AS value,t.name AS text,t.notes, pt.info');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t');
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st on st.team_id = t.id');
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
        $query->where('pt.project_id = ' . $project_id);
        $query->order('t.name ASC');
        }
/*        
        $query = '	SELECT	t.id AS value,
							t.name AS text,
							t.notes, pt.info
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = t.id
					WHERE pt.project_id = ' . $project_id . '
					ORDER BY text ASC ';
*/
		$db->setQuery( $query );
		if ( !$result = $db->loadObjectList() )
		{
			
            //$mainframe->enqueueMessage(get_class($this).' '.__FUNCTION__.' query<br><pre>'.print_r($query, true).'</pre><br>','Error');
            $mainframe->enqueueMessage(get_class($this).' '.__FUNCTION__.' getErrorMsg<br><pre>'.print_r($db->getErrorMsg(), true).'</pre><br>','Error');
			return false;
		}
		else
		{
			return $result;
		}
	}
    
    
    
    function getAllProjectTeams($projectid=0,$divisionid=0)
	{
		$teams = array();

		$query = "SELECT
                    tl.id AS projectteamid,
                    tl.team_id,
                    tl.picture projectteam_picture,
                    tl.project_id,
                    t.id,
                    t.name as team_name,
                    t.short_name,
                    t.middle_name,
                    t.club_id,
                    t.website AS team_www,
                    t.picture team_picture,
                    c.name as club_name,
                    c.address as club_address,
                    c.zipcode as club_zipcode,
                    c.state as club_state,
                    c.location as club_location,
                    c.email as club_email,
                    c.logo_big,
                    c.unique_id,
                    c.logo_small,
                    c.logo_middle,
                    c.country as club_country,
                    c.website AS club_www,
                    
                    c.latitude AS latitude,
                    c.longitude AS longitude,
                    
				    CASE WHEN CHAR_LENGTH( t.alias ) THEN CONCAT_WS( ':', t.id, t.alias ) ELSE t.id END AS team_slug,
				    CASE WHEN CHAR_LENGTH( c.alias ) THEN CONCAT_WS( ':', c.id, c.alias ) ELSE c.id END AS club_slug
                  FROM #__".COM_SPORTSMANAGEMENT_TABLE."_project_team as tl
                  LEFT JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_team as t ON tl.team_id = t.id
                  LEFT JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_club as c ON t.club_id = c.id
                  LEFT JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_division as d ON d.id = tl.division_id
                  LEFT JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_playground as plg ON plg.id = tl.standard_playground
                  WHERE tl.project_id = " . $projectid;

		if ( $divisionid > 0 )
		{
			$query .= " AND tl.division_id = " . $divisionid;
		}
		$query .= " ORDER BY t.name";

		$this->_db->setQuery($query);
		if ( ! $teams = $this->_db->loadObjectList() )
		{
			echo $this->_db->getErrorMsg();
		}

		return $teams;
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
