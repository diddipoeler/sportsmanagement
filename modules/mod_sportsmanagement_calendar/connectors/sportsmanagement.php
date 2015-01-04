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

defined('_JEXEC') or die('Restricted access');


/**
 * SportsmanagementConnector
 * 
 * @package 
 * @author diddi
 * @copyright 2015
 * @version $Id$
 * @access public
 */
class SportsmanagementConnector extends JSMCalendar
{
	//var $database = JFactory::getDbo();
	static $xparams;
    static $params;
	static $prefix;
    static $favteams;

	/**
	 * SportsmanagementConnector::getEntries()
	 * 
	 * @param mixed $caldates
	 * @param mixed $params
	 * @param mixed $matches
	 * @return
	 */
	public static function getEntries ( &$caldates, &$params, &$matches )
	{
		$m = array();
		$b = array();
        SportsmanagementConnector::$params = $params;
		SportsmanagementConnector::$xparams = $params;
		SportsmanagementConnector::$prefix = $params->prefix;
		if(SportsmanagementConnector::$xparams->get('sportsmanagement_use_favteams', 0) == 1)
		{
			SportsmanagementConnector::$favteams = SportsmanagementConnector::getFavs();
		}
		if (SportsmanagementConnector::$xparams->get('jlmatches', 0) == 1)
		{
			$rows = SportsmanagementConnector::getMatches($caldates);
			$m = SportsmanagementConnector::formatMatches($rows, $matches);
		}
		if (SportsmanagementConnector::$xparams->get('jlbirthdays', 1) == 1)
		{
			$birthdays = SportsmanagementConnector::getBirthdays (  $caldates, SportsmanagementConnector::$params, JSMCalendar::$matches  );
			$b = SportsmanagementConnector::formatBirthdays($birthdays, $matches, $caldates);
		}


		return array_merge($m, $b);
	}

	/**
	 * SportsmanagementConnector::getFavs()
	 * 
	 * @return
	 */
	function getFavs()
	{


		$query = "SELECT id, fav_team FROM #__joomleague_project
      where fav_team != '' ";

		$projectid		= self::$xparams->get('project_ids') ;

		if ($projectid)
		{
			$projectids = (is_array($projectid)) ? implode(",", $projectid) : $projectid;

			$query .= " AND id IN(".$projectids.")";
		}

		$query = (self::$prefix != '') ? str_replace('#__', self::$prefix, $query) : $query;
		$database = JFactory::getDbo();
		$database->setQuery($query);
		$fav=$database->loadObjectList();


		// echo '<pre>';
		// print_r($fav);
		// echo '</pre>';
		//	exit(0);
		return $fav;
		//	return implode(',', $fav);
	}

	/**
	 * SportsmanagementConnector::getBirthdays()
	 * 
	 * @param mixed $caldates
	 * @param string $ordering
	 * @return
	 */
	static function getBirthdays ( $caldates, $ordering='ASC' )
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
		$teamCondition = '';
		$clubCondition = '';
		$favCondition = '';
		$limitingcondition = '';
		$limitingconditions = array();

		$customteam = $jinput->getVar('jlcteam',0,'default','POST');
		$teamid	= SportsmanagementConnector::$xparams->get('team_ids') ;

		if($customteam != 0)
		{
			$limitingconditions[] = "( m.projectteam1_id = ".$customteam." OR m.projectteam2_id = ".$customteam.")";
		}

		if ($teamid && $customteam == 0)
		{
			$teamids = (is_array($teamid)) ? implode(",", $teamid) : $teamid;
			if($teamids > 0) 	
            {
				$limitingconditions[] = "pt.team_id IN (".$teamids.")";
			}
		}

		if(SportsmanagementConnector::$xparams->get('joomleague_use_favteams', 0) == 1 && $customteam == 0)
		{
			foreach ($this->favteams as $projectfavs)
			{
				$favConds[] = "(pt.team_id IN (". $projectfavs->fav_team.") AND p.id =".$projectfavs->id.")";
			}
			$limitingconditions[] = implode(' OR ', $favConds);
		}


		// new insert for user select a club
		$clubid	= SportsmanagementConnector::$xparams->get('club_ids') ;

		if ($clubid && $customteam == 0)
		{
			$clubids = (is_array($clubid)) ? implode(",", $clubid) : $clubid;
			if($clubids > 0) 	$limitingconditions[] = "team.club_id IN (".$clubids.")";
		}

		if (count($limitingconditions) > 0)
		{
//			$limitingcondition .=' AND (';
//			$limitingcondition .= implode(' OR ', $limitingconditions);
//			$limitingcondition .=')';
            $query->where(" ( ".implode(' OR ', $limitingconditions)." ) ");
		}
        
        
        $query->select("p.id, p.firstname, p.lastname, p.picture, p.country,
                     DATE_FORMAT(p.birthday, '%m-%d') AS month_day,
                     YEAR( CURRENT_DATE( ) ) as year,
                     DATE_FORMAT('".$caldates['start']."', '%Y') - YEAR( p.birthday ) AS age,
                     DATE_FORMAT(p.birthday,'%Y-%m-%d') AS date_of_birth,
                     pt.project_id as project_id,
                     'showPlayer' AS func_to_call,
                     'pid' AS id_to_append,
                     team.short_name, team.id as teamid");
        $query->from('#__sportsmanagement_person AS p'); 
        $query->join('INNER','#__sportsmanagement_season_team_person_id AS tp ON tp.person_id = p.id');
        $query->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id'); 
        $query->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $query->join('INNER','#__sportsmanagement_team AS team ON team.id = pt.team_id ');
        $query->join('INNER','#__sportsmanagement_club AS club ON club.id = team.club_id ');
        
        $query->where("p.published = 1");
        $query->where("p.birthday != '0000-00-00'");
        $query->where("DATE_FORMAT(p.birthday, '%m') = DATE_FORMAT('".$caldates['start']."', '%m')");

/*
		$query="SELECT p.id, p.firstname, p.lastname, p.picture, p.country,
                     DATE_FORMAT(p.birthday, '%m-%d') AS month_day,
                     YEAR( CURRENT_DATE( ) ) as year,
                     DATE_FORMAT('".$caldates['start']."', '%Y') - YEAR( p.birthday ) AS age,
                     DATE_FORMAT(p.birthday,'%Y-%m-%d') AS date_of_birth,
                     pt.project_id as project_id,
                     'showPlayer' AS func_to_call,
                     'pid' AS id_to_append,
                     team.short_name, team.id as teamid
              FROM #__joomleague_person AS p
              inner JOIN #__joomleague_team_player AS tp ON (p.id = tp.person_id)
              inner JOIN #__joomleague_project_team AS pt ON (pt.id = tp.projectteam_id)
              inner JOIN #__joomleague_team AS team ON (team.id = pt.team_id )
              inner JOIN #__joomleague_club AS club ON (club.id = team.club_id )
              WHERE p.published = 1 AND p.birthday != '0000-00-00' and DATE_FORMAT(p.birthday, '%m') = DATE_FORMAT('".$caldates['start']."', '%m')";
*/
		$projectid = SportsmanagementConnector::$xparams->get('project_ids') ;


		if ($projectid)
		{
			$projectids = (is_array($projectid)) ? implode(",", $projectid) : $projectid;
			if($projectids > 0)
            {
            //$query .= " AND (pt.project_id IN (".$projectids.") )";   
            $query->where("(pt.project_id IN (".$projectids.") )"); 
            } 	

		}

//		$query .= $limitingcondition;
//		$query .= "  GROUP BY p.id ORDER BY p.birthday";

		$query->group('p.id');
        $query->order('p.birthday');
        //$query = (self::$prefix != '') ? str_replace('#__', self::$prefix, $query) : $query;
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        }
        
		$db->setQuery($query);
		//echo($database->getQuery());

		$players = $db->loadObjectList();

		return $players;
	}
    
	/**
	 * SportsmanagementConnector::formatBirthdays()
	 * 
	 * @param mixed $rows
	 * @param mixed $matches
	 * @param mixed $dates
	 * @return
	 */
	static function formatBirthdays( $rows, &$matches, $dates )
	{
		$newrows = array();
		$year = substr($dates['start'], 0, 4);


		foreach ($rows AS $key => $row)
		{
			$newrows[$key]['type'] = 'jlb';
			$newrows[$key]['homepic'] = '';
			$newrows[$key]['awaypic'] = '';
			$newrows[$key]['date'] = $year.'-'.$row->month_day;
			$newrows[$key]['age'] = '('.$row->age.')';
			$newrows[$key]['headingtitle'] = SportsmanagementConnector::$xparams->get('birthday_text', 'Birthday');
			$newrows[$key]['name'] = '';

			if ($row->picture != '' AND file_exists(JPATH_BASE.DS.$row->picture))
			{
				$linkit = 1;
				$newrows[$key]['name'] = '<img src="'.JUri::root(true).'/'.$row->picture.'" alt="Picture" style="height:40px; vertical-align:middle;margin:0 5px;" />';

				//echo $newrows[$key]['name'].'<br />';
			}
			$newrows[$key]['name'] .= parent::jl_utf8_convert ($row->firstname, 'iso-8859-1', 'utf-8').' ';
			$newrows[$key]['name'] .= parent::jl_utf8_convert ($row->lastname, 'iso-8859-1', 'utf-8').' - '.parent::jl_utf8_convert ($row->short_name, 'iso-8859-1', 'utf-8');
			//$newrows[$key]['name'] .= ' ('..')';
			$newrows[$key]['matchcode'] = 0;
			$newrows[$key]['project_id'] = $row->project_id;

			// new insert for link to player profile
			//$newrows[$key]['link'] = 'index.php?option=com_joomleague&view=player&p='.$row->project_id.'&pid='.$row->id;
			$newrows[$key]['link'] = sportsmanagementHelperRoute::getPlayerRoute( $row->project_id, $row->teamid, $row->id);


			$matches[] = $newrows[$key];
		}
		return $newrows;
	}


	/**
	 * SportsmanagementConnector::formatMatches()
	 * 
	 * @param mixed $rows
	 * @param mixed $matches
	 * @return
	 */
	static function formatMatches( $rows, &$matches )
	{
		$newrows = array();
		$teamnames = SportsmanagementConnector::$xparams->get('team_names', 'short_name');
		$teams = SportsmanagementConnector::getTeamsFromMatches( $rows );
		$teams[0] = new stdclass;
		$teams[0]->name = $teams[0]->$teamnames = $teams[0]->logo_small = $teams[0]->logo_middle = $teams[0]->logo_big =  '';

		foreach ($rows AS $key => $row) {
			$newrows[$key]['type'] = 'jlm';
			$newrows[$key]['homepic'] = SportsmanagementConnector::buildImage($teams[$row->projectteam1_id]);
			$newrows[$key]['awaypic'] = SportsmanagementConnector::buildImage($teams[$row->projectteam2_id]);

			$newrows[$key]['date'] = sportsmanagementHelper::getMatchStartTimestamp($row);
			//$newrows[$key]['result'] = (!is_null($row->matchpart1_result)) ? $row->matchpart1_result . ':' . $row->matchpart2_result : '-:-';
			$newrows[$key]['result'] = (!is_null($row->team1_result)) ? $row->team1_result . ':' . $row->team2_result : '-:-';
			$newrows[$key]['headingtitle'] = parent::jl_utf8_convert ($row->name.'-'.$row->roundname, 'iso-8859-1', 'utf-8');
			$newrows[$key]['homename'] = SportsmanagementConnector::formatTeamName($teams[$row->projectteam1_id]);
			$newrows[$key]['awayname'] = SportsmanagementConnector::formatTeamName($teams[$row->projectteam2_id]);
			$newrows[$key]['matchcode'] = $row->matchcode;
			$newrows[$key]['project_id'] = $row->project_id;

			// insert matchdetaillinks
			$newrows[$key]['link'] = sportsmanagementHelperRoute::getNextMatchRoute( $row->project_id, $row->matchcode);
			$matches[] = $newrows[$key];
			parent::addTeam($row->projectteam1_id, parent::jl_utf8_convert ($teams[$row->projectteam1_id]->name, 'iso-8859-1', 'utf-8'), $newrows[$key]['homepic']);
			parent::addTeam($row->projectteam2_id, parent::jl_utf8_convert ($teams[$row->projectteam2_id]->name, 'iso-8859-1', 'utf-8'), $newrows[$key]['awaypic']);

		}
		return $newrows;
	}

	/**
	 * SportsmanagementConnector::formatTeamName()
	 * 
	 * @param mixed $team
	 * @return
	 */
	static function formatTeamName($team)
	{
		$teamnames = SportsmanagementConnector::$xparams->get('team_names', 'short_name');
		switch ($teamnames)
		{
			case '-':
				return '';
				break;
			case 'short_name':
				$teamname = '<acronym title="'.parent::jl_utf8_convert ($team->name, 'iso-8859-1', 'utf-8').'">'
				.parent::jl_utf8_convert ($team->short_name, 'iso-8859-1', 'utf-8')
				.'</acronym>';
				break;
			default:
				if (!isset($team->$teamnames) OR (is_null($team->$teamnames) OR trim($team->$teamnames)=='')) {
					$teamname = parent::jl_utf8_convert ($team->name, 'iso-8859-1', 'utf-8');
				}
				else {
					$teamname = parent::jl_utf8_convert ($team->$teamnames, 'iso-8859-1', 'utf-8');
				}
				break;
		}
		return $teamname;
	}

	/**
	 * SportsmanagementConnector::buildImage()
	 * 
	 * @param mixed $team
	 * @return
	 */
	static function buildImage($team)
	{
		$image = self::$xparams->get('team_logos', 'logo_small');
		if ($image == '-') { return ''; }
		$logo = '';

		if ($team->$image != '' && file_exists(JPATH_BASE.'/'.$team->$image))
		{
			$h = self::$xparams->get('logo_height', 20);
			$logo = '<img src="'.JUri::root(true).'/'.$team->$image.'" alt="'
			.parent::jl_utf8_convert ($team->short_name, 'iso-8859-1', 'utf-8').'" title="'
			.parent::jl_utf8_convert ($team->name, 'iso-8859-1', 'utf-8').'"';
			if ($h > 0) {
				$logo .= ' style="height:'.$h.'px;"';
			}
			$logo .= ' />';
		}
		return $logo;
	}

	/**
	 * SportsmanagementConnector::getMatches()
	 * 
	 * @param mixed $caldates
	 * @param string $ordering
	 * @return
	 */
	//function getMatches($caldates, $ordering='ASC')
    static function getMatches($caldates, $ordering='ASC')
	{
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

		$where = '';
        $teamCondition = '';
		$clubCondition = '';
		$favCondition = '';
		$limitingcondition = '';
		$limitingconditions = array();
		$favConds = array();

		$customteam = $jinput->getVar('jlcteam',0,'default','POST');

		$teamid	= SportsmanagementConnector::$xparams->get('team_ids') ;

		if($customteam != 0)
		{
			$limitingconditions[] = "( m.projectteam1_id = ".$customteam." OR m.projectteam2_id = ".$customteam.")";
            //$query->where("( m.projectteam1_id = ".$customteam." OR m.projectteam2_id = ".$customteam.")");
		}

		if ($teamid && $customteam == 0)
		{
			$teamids = (is_array($teamid)) ? implode(",", $teamid) : $teamid;
			if($teamids > 0)
			{
				$limitingconditions[] = "pt.team_id IN (".$teamids.")";
                //$query->where("pt.team_id IN (".$teamids.")");
			}
		}

		$clubid	= SportsmanagementConnector::$xparams->get('club_ids') ;

		if ($clubid && $customteam == 0)
		{
			$clubids = (is_array($clubid)) ? implode(",", $clubid) : $clubid;
			if($clubids > 0)
			{
				$limitingconditions[] = "team.club_id IN (".$clubids.")";
                //$query->where("team.club_id IN (".$clubids.")");
			}
		}

		if(SportsmanagementConnector::$xparams->get('sportsmanagement_use_favteams', 0) == 1 && $customteam == 0)
		{
			foreach (SportsmanagementConnector::$favteams as $projectfavs)
			{
				$favConds[] = "(pt.team_id IN (". $projectfavs->fav_team.") AND p.id =".$projectfavs->id.")";
			}
			if(!empty($favConds))
			{
				$limitingconditions[] = implode(' OR ', $favConds);
			}
		}

		if (count($limitingconditions) > 0)
		{
//			$limitingcondition .=' AND (';
//			$limitingcondition .= implode(' OR ', $limitingconditions);
            
            $query->where(" ( ".implode(' OR ', $limitingconditions)." ) ");
            
//			$limitingcondition .=')';
		}

		$limit = (isset($caldates['limitstart'])&&isset($caldates['limitend'])) ? ' LIMIT '.$caldates['limitstart'].', '.$caldates['limitend'] :'';


		$query->select('m.*,p.*,match_date AS caldate,r.roundcode, r.name AS roundname, r.round_date_first, r.round_date_last,m.id as matchcode, p.id as project_id');
        $query->from('#__sportsmanagement_match as m');
        $query->join('INNER','#__sportsmanagement_round as r ON r.id = m.round_id');
        $query->join('INNER','#__sportsmanagement_project as p ON p.id = r.project_id');
        $query->join('INNER','#__sportsmanagement_project_team as pt ON (pt.id = m.projectteam1_id OR pt.id = m.projectteam2_id)');
        $query->join('INNER','#__sportsmanagement_season_team_id AS st ON st.id = pt.team_id ');
        $query->join('INNER','#__sportsmanagement_team as team ON team.id = st.team_id');
        $query->join('INNER','#__sportsmanagement_club as club ON club.id = team.club_id');
        
//        $query = "SELECT  m.*,p.*,
//                      match_date AS caldate,
//                      r.roundcode, r.name AS roundname, r.round_date_first, r.round_date_last,
//                      m.id as matchcode, p.id as project_id
//              FROM #__joomleague_match m
//              inner JOIN #__joomleague_round r ON r.id = m.round_id
//              inner JOIN #__joomleague_project p ON p.id = r.project_id
//              inner JOIN #__joomleague_project_team pt ON (pt.id = m.projectteam1_id OR pt.id = m.projectteam2_id)
//              inner JOIN #__joomleague_team team ON team.id = pt.team_id
//              inner JOIN #__joomleague_club club ON club.id = team.club_id
//               ";

//		$where = " WHERE m.published = 1
//               AND p.published = 1 ";
        
        $query->where("m.published = 1");
        $query->where("p.published = 1");
               
		if (isset($caldates['start']))
        { 
        //$where .= " AND m.match_date >= '".$caldates['start']."'";
        $query->where("m.match_date >= ".$db->Quote(''.$caldates['start'].'')."");
        }
		if (isset($caldates['end'])) 
        {
        //$where .= " AND m.match_date <= '".$caldates['end']."'";
        $query->where("m.match_date <= ".$db->Quote(''.$caldates['end'].'')."");
        }
		if (isset($caldates['matchcode']))
        {
        //$where .= " AND r.matchcode = '".$caldates['matchcode']."'";
        $query->where("r.matchcode LIKE ".$db->Quote(''.$caldates['matchcode'].'')."");
        }
		$projectid = SportsmanagementConnector::$xparams->get('project_ids') ;

		if ($projectid)
		{
			$projectids = (is_array($projectid)) ? implode(",", $projectid) : $projectid;
			if($projectids > 0)
            {
            //$where .= " AND p.id IN (".$projectids.")";
            $query->where("p.id IN (".$projectids.")");
            }
		}

		if(isset($caldates['resultsonly']) && $caldates['resultsonly']== 1) 
        {
        //$where .= " AND m.team1_result IS NOT NULL";
        $query->where("m.team1_result IS NOT NULL");
        }

		$where .= $limitingcondition;

//		$where .= " GROUP BY m.id";
//		$where .=" ORDER BY m.match_date ".$ordering;
        
        $query->group('m.id');
        $query->order('m.match_date '.$ordering);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        }

		//$query = (SportsmanagementConnector::$prefix != '') ? str_replace('#__', SportsmanagementConnector::$prefix, $query) : $query;
		
        //$db->setQuery($query.$where.$limit);
        $db->setQuery($query.$limit);
        
		$result = $db->loadObjectList();
		if ($result)
		{
			foreach ($result as $match)
			{
				sportsmanagementHelper::convertMatchDateToTimezone($match);
			}
		}
		return $result;
	}

	/**
	 * SportsmanagementConnector::getTeamsFromMatches()
	 * 
	 * @param mixed $games
	 * @return
	 */
	static function getTeamsFromMatches( &$games )
	{
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
		if ( !count ($games) ) return Array();
		foreach ( $games as $m )
		{
			$teamsId[] = $m->projectteam1_id;
			$teamsId[] = $m->projectteam2_id;
		}

		$listTeamId = implode( ",", array_unique($teamsId) );
        
        $query->select('tl.id AS teamtoolid, tl.division_id, tl.standard_playground, tl.start_points,tl.info, tl.team_id, tl.checked_out, tl.checked_out_time, tl.picture, tl.project_id');
        $query->select('t.id, t.name, t.short_name, t.middle_name, t.info, t.club_id');
        $query->select('c.logo_small, c.logo_middle, c.logo_big, c.country');
        $query->select('p.name AS project_name');
        
        $query->from('#__sportsmanagement_team as t');
        $query->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = t.id ');
        
        $query->join('INNER','#__sportsmanagement_project_team as tl on tl.team_id = st.id');
        $query->join('INNER','#__sportsmanagement_project as p on p.id = tl.project_id');
        $query->join('LEFT','#__sportsmanagement_club as c on t.club_id = c.id');
        
        $query->where("tl.id IN (".$listTeamId.")");
        $query->where("tl.project_id = p.id");

//		$query = "SELECT tl.id AS teamtoolid, tl.division_id, tl.standard_playground, tl.start_points,
//                     tl.info, tl.team_id, tl.checked_out, tl.checked_out_time, tl.picture, tl.project_id,
//                     t.id, t.name, t.short_name, t.middle_name, t.info, t.club_id,
//                     c.logo_small, c.logo_middle, c.logo_big, c.country,
//                     p.name AS project_name
//                FROM #__joomleague_team t
//                INNER JOIN #__joomleague_project_team tl on tl.team_id = t.id
//                INNER JOIN #__joomleague_project p on p.id = tl.project_id
//                LEFT JOIN #__joomleague_club c on t.club_id = c.id
//                WHERE tl.id IN (".$listTeamId.") AND tl.project_id = p.id";

		//$query = (self::$prefix != '') ? str_replace('#__', self::$prefix, $query) : $query;
		$db->setQuery($query);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        }
        
		if ( !$result = $db->loadObjectList('teamtoolid') ) $result = Array();
		return $result;
	}

	/**
	 * SportsmanagementConnector::build_url()
	 * 
	 * @param mixed $row
	 * @return void
	 */
	function build_url( &$row )
	{

	}

	/**
	 * SportsmanagementConnector::getGlobalTeams()
	 * 
	 * @return void
	 */
	function getGlobalTeams ()
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
		$teamnames = SportsmanagementConnector::$xparams->get('team_names', 'short_name');
		$database = JFactory::getDbo();
		$query = "SELECT t.".$teamnames." AS name, t.id AS value
    FROM #__joomleague_teams t, #__joomleague p
    WHERE t.id IN(p.fav_team)";
		$db->setQuery($query);
        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($query,true).'</pre>'),'Notice');
        
		$result = $db->loadObjectList();
	}
}