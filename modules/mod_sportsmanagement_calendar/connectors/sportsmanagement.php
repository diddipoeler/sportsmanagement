<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_calendar
 * @file       sportsmanagement.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * SportsmanagementConnector
 *
 * @package
 * @author    diddi
 * @copyright 2015
 * @version   $Id$
 * @access    public
 */
class SportsmanagementConnector extends JSMCalendar
{
	static $xparams;

	static $params;

	static $prefix;

	static $favteams;

	/**
	 * SportsmanagementConnector::getEntries()
	 *
	 * @param   mixed $caldates
	 * @param   mixed $params
	 * @param   mixed $matches
	 * @return
	 */
	public static function getEntries( &$caldates, &$params, &$matches )
	{
		$m = array();
		$b = array();
		self::$params = $params;
		self::$xparams = $params;
		self::$prefix = $params->prefix;

		if (self::$xparams->get('sportsmanagement_use_favteams', 0) == 1)
		{
			self::$favteams = self::getFavs();
		}

		if (self::$xparams->get('jlmatches', 0) == 1)
		{
			$rows = self::getMatches($caldates);
			$m = self::formatMatches($rows, $matches);
		}

		if (self::$xparams->get('jlbirthdays', 1) == 1)
		{
			$birthdays = self::getBirthdays($caldates, self::$params, JSMCalendar::$matches);
			$b = self::formatBirthdays($birthdays, $matches, $caldates);
		}

		return array_merge($m, $b);
	}

	/**
	 * SportsmanagementConnector::getFavs()
	 *
	 * @return
	 */
	static function getFavs()
	{
		  // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

			  $query->select('id, fav_team');
		$query->from('#__sportsmanagement_project');
		$query->where("fav_team != ''");

		$projectid = self::$xparams->get('p');

		if ($projectid)
		{
			$projectids = (is_array($projectid)) ? implode(",", array_map('intval', $projectid)) : (int) $projectid;
			$query->where("id IN(" . $projectids . ")");
		}

			 $db->setQuery($query);
		$fav = $db->loadObjectList();

		return $fav;
	}

	/**
	 * SportsmanagementConnector::getBirthdays()
	 *
	 * @param   mixed  $caldates
	 * @param   string $ordering
	 * @return
	 */
	static function getBirthdays( $caldates, $ordering='ASC' )
	{
		  // Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

			  $teamCondition = '';
		$clubCondition = '';
		$favCondition = '';
		$limitingcondition = '';
		$limitingconditions = array();

		$customteam = $jinput->getVar('jlcteam', 0, 'default', 'POST');
		$teamid    = self::$xparams->get('team_ids');

		if ($customteam != 0)
		{
			$limitingconditions[] = "( m.projectteam1_id = " . $customteam . " OR m.projectteam2_id = " . $customteam . ")";
		}

		if ($teamid && $customteam == 0)
		{
			$teamids = (is_array($teamid)) ? implode(",", array_map('intval', $teamid)) : (int) $teamid;

			if ($teamids > 0)
			{
				$limitingconditions[] = "pt.team_id IN (" . $teamids . ")";
			}
		}

		if (self::$xparams->get('joomleague_use_favteams', 0) == 1 && $customteam == 0)
		{
			foreach ($this->favteams as $projectfavs)
			{
				$favConds[] = "(pt.team_id IN (" . $projectfavs->fav_team . ") AND p.id =" . $projectfavs->id . ")";
			}

			$limitingconditions[] = implode(' OR ', $favConds);
		}

		// New insert for user select a club
		$clubid    = self::$xparams->get('club_ids');

		if ($clubid && $customteam == 0)
		{
			$clubids = (is_array($clubid)) ? implode(",", array_map('intval', $clubid)) : (int) $clubid;

			if ($clubids > 0)
			{
				$limitingconditions[] = "team.club_id IN (" . $clubids . ")";
			}
		}

		if (count($limitingconditions) > 0)
		{
			$query->where(" ( " . implode(' OR ', $limitingconditions) . " ) ");
		}

			  $query->select(
				  "p.id, p.firstname, p.lastname, p.picture, p.country,
                     DATE_FORMAT(p.birthday, '%m-%d') AS month_day,
                     YEAR( CURRENT_DATE( ) ) as year,
                     DATE_FORMAT('" . $caldates['start'] . "', '%Y') - YEAR( p.birthday ) AS age,
                     DATE_FORMAT(p.birthday,'%Y-%m-%d') AS date_of_birth,
                     pt.project_id as project_id,
                     'showPlayer' AS func_to_call,
                     'pid' AS id_to_append,
                     team.short_name, team.id as teamid"
			  );

			  $query->select('CONCAT_WS(\':\',team.id,team.alias) AS team_slug');
		$query->select('CONCAT_WS(\':\',pro.id,pro.alias) AS project_slug');
		$query->select('CONCAT_WS(\':\',p.id,p.alias) AS person_slug');

			  $query->from('#__sportsmanagement_person AS p');
		$query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON tp.person_id = p.id');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$query->join('INNER', '#__sportsmanagement_team AS team ON team.id = pt.team_id ');
		$query->join('INNER', '#__sportsmanagement_club AS club ON club.id = team.club_id ');
		$query->join('INNER', '#__sportsmanagement_project AS pro ON pro.id = pt.project_id');

			  $query->where("p.published = 1");
		$query->where("p.birthday != '0000-00-00'");
		$query->where("DATE_FORMAT(p.birthday, '%m') = DATE_FORMAT('" . $caldates['start'] . "', '%m')");

		$projectid = self::$xparams->get('p');

		if ($projectid)
		{
			$projectids = (is_array($projectid)) ? implode(",", array_map('intval', $projectid)) : (int) $projectid;

			if ($projectids > 0)
			{
					 $query->where("(pt.project_id IN (" . $projectids . ") )");
			}
		}

		$query->group('p.id');
		$query->order('p.birthday');

			 $db->setQuery($query);

		$players = $db->loadObjectList();

		return $players;
	}

	/**
	 * SportsmanagementConnector::formatBirthdays()
	 *
	 * @param   mixed $rows
	 * @param   mixed $matches
	 * @param   mixed $dates
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
			$newrows[$key]['date'] = $year . '-' . $row->month_day;
			$newrows[$key]['age'] = '(' . $row->age . ')';
			$newrows[$key]['headingtitle'] = self::$xparams->get('birthday_text', 'Birthday');
			$newrows[$key]['name'] = '';

			if ($row->picture != '' && file_exists(JPATH_BASE . DIRECTORY_SEPARATOR . $row->picture))
			{
				$linkit = 1;
				$newrows[$key]['name'] = '<img src="' . Uri::root(true) . '/' . $row->picture . '" alt="Picture" style="height:40px; vertical-align:middle;margin:0 5px;" />';
			}

			$newrows[$key]['name'] .= parent::jl_utf8_convert($row->firstname, 'iso-8859-1', 'utf-8') . ' ';
			$newrows[$key]['name'] .= parent::jl_utf8_convert($row->lastname, 'iso-8859-1', 'utf-8') . ' - ' . parent::jl_utf8_convert($row->short_name, 'iso-8859-1', 'utf-8');
			$newrows[$key]['matchcode'] = 0;
			$newrows[$key]['project_id'] = $row->project_id;

			// New insert for link to player profile
			$routeparameter = array();
			$routeparameter['cfg_which_database'] = self::$params->get('cfg_which_database');
			$routeparameter['s'] = self::$params->get('s');
			$routeparameter['p'] = $row->project_slug;
			$routeparameter['tid'] = $row->team_slug;
			$routeparameter['pid'] = $row->person_slug;
			$newrows[$key]['link'] = sportsmanagementHelperRoute::getSportsmanagementRoute('player', $routeparameter);

					  $matches[] = $newrows[$key];
		}

		return $newrows;
	}


	/**
	 * SportsmanagementConnector::formatMatches()
	 *
	 * @param   mixed $rows
	 * @param   mixed $matches
	 * @return
	 */
	static function formatMatches( $rows, &$matches )
	{
		$newrows = array();
		$teamnames = self::$xparams->get('team_names', 'short_name');
		$teams = self::getTeamsFromMatches($rows);
		$teams[0] = new stdclass;
		$teams[0]->name = $teams[0]->$teamnames = $teams[0]->logo_small = $teams[0]->logo_middle = $teams[0]->logo_big = '';

		foreach ($rows AS $key => $row)
		{
			$newrows[$key]['type'] = 'jlm';
			$newrows[$key]['homepic'] = self::buildImage($teams[$row->projectteam1_id]);
			$newrows[$key]['awaypic'] = self::buildImage($teams[$row->projectteam2_id]);
			$newrows[$key]['date'] = sportsmanagementHelper::getMatchStartTimestamp($row);
			if ($row->cancel == 1) {
				$newrows[$key]['result'] = $row->cancel_reason;
			}
			else {
				$newrows[$key]['result'] = (!is_null($row->team1_result)) ? $row->team1_result . ':' . $row->team2_result : '-:-';
			}
			$newrows[$key]['headingtitle'] = parent::jl_utf8_convert($row->name . '-' . $row->roundname, 'iso-8859-1', 'utf-8');
			$newrows[$key]['homename'] = self::formatTeamName($teams[$row->projectteam1_id]);
			$newrows[$key]['awayname'] = self::formatTeamName($teams[$row->projectteam2_id]);
			$newrows[$key]['matchcode'] = $row->matchcode;
			$newrows[$key]['project_id'] = $row->project_id;
			$newrows[$key]['timestamp'] = $row->timestamp;

			// Insert matchdetaillinks
			$routeparameter = array();
			$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
			$routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
			$routeparameter['p'] = $row->project_slug;
			$routeparameter['mid'] = $row->match_slug;
			$newrows[$key]['link'] = sportsmanagementHelperRoute::getSportsmanagementRoute('nextmatch', $routeparameter);

				  $matches[] = $newrows[$key];
			parent::addTeam($row->projectteam1_id, parent::jl_utf8_convert($teams[$row->projectteam1_id]->name, 'iso-8859-1', 'utf-8'), $newrows[$key]['homepic']);
			parent::addTeam($row->projectteam2_id, parent::jl_utf8_convert($teams[$row->projectteam2_id]->name, 'iso-8859-1', 'utf-8'), $newrows[$key]['awaypic']);
		}

		return $newrows;
	}

	/**
	 * SportsmanagementConnector::formatTeamName()
	 *
	 * @param   mixed $team
	 * @return
	 */
	static function formatTeamName($team)
	{
		$teamnames = self::$xparams->get('team_names', 'short_name');

		switch ($teamnames)
		{
			case '-':
					return '';
				  break;
			case 'short_name':
					$teamname = '<acronym title="' . parent::jl_utf8_convert($team->name, 'iso-8859-1', 'utf-8') . '">'
					. parent::jl_utf8_convert($team->short_name, 'iso-8859-1', 'utf-8')
					. '</acronym>';
					break;
			default:
				if (!isset($team->$teamnames) || (is_null($team->$teamnames) || trim($team->$teamnames) == ''))
				{
					$teamname = parent::jl_utf8_convert($team->name, 'iso-8859-1', 'utf-8');
				}
				else
				{
					$teamname = parent::jl_utf8_convert($team->$teamnames, 'iso-8859-1', 'utf-8');
				}
					break;
		}

		return $teamname;
	}

	/**
	 * SportsmanagementConnector::buildImage()
	 *
	 * @param   mixed $team
	 * @return
	 */
	static function buildImage($team)
	{
		$image = self::$xparams->get('team_logos', 'logo_small');

		if ($image == '-')
		{
			return '';
		}

		$logo = '';

		if (!sportsmanagementHelper::existPicture($team->$image))
		{
			$team->$image = sportsmanagementHelper::getDefaultPlaceholder('logo_big');
		}

			$h = self::$xparams->get('logo_height', 20);
			$logo = '<img src="' . $team->$image . '" alt="'
			. parent::jl_utf8_convert($team->short_name, 'iso-8859-1', 'utf-8') . '" title="'
			. parent::jl_utf8_convert($team->name, 'iso-8859-1', 'utf-8') . '"';

		if ($h > 0)
		{
					$logo .= ' style="height:' . $h . 'px;"';
		}

			$logo .= ' />';

			return $logo;
	}

	/**
	 * SportsmanagementConnector::getMatches()
	 *
	 * @param  mixed $caldates
	 * @param  string $ordering
	 * @return
	 */
	// Function getMatches($caldates, $ordering='ASC')
	static function getMatches($caldates, $ordering='ASC')
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

			  $where = '';
		$teamCondition = '';
		$clubCondition = '';
		$favCondition = '';
		$limitingcondition = '';
		$limitingconditions = array();
		$favConds = array();

		$customteam = $jinput->getVar('jlcteam', 0, 'default', 'POST');

		$teamid    = self::$xparams->get('team_ids');

		if ($customteam != 0)
		{
			   $limitingconditions[] = "( m.projectteam1_id = " . $customteam . " OR m.projectteam2_id = " . $customteam . ")";
		}

		if ($teamid && $customteam == 0)
		{
			   $teamids = (is_array($teamid)) ? implode(",", array_map('intval', $teamid)) : (int) $teamid;

			if ($teamids > 0)
			{
				$limitingconditions[] = "( st1.team_id IN (" . $teamids . ") OR st2.team_id IN (" . $teamids . ") )";
			}
		}

		$clubid    = self::$xparams->get('club_ids');

		if ($clubid && $customteam == 0)
		{
			   $clubids = (is_array($clubid)) ? implode(",", array_map('intval', $clubid)) : (int) $clubid;

			if ($clubids > 0)
			{
				$limitingconditions[] = "( t1.club_id IN (" . $clubids . ") OR t2.club_id IN (" . $clubids . ") )";
			}
		}

		if (self::$xparams->get('sportsmanagement_use_favteams', 0) == 1 && $customteam == 0)
		{
			foreach (self::$favteams as $projectfavs)
			{
				$favConds[] = "( ( st1.team_id IN (" . $projectfavs->fav_team . ") OR st2.team_id IN (" . $projectfavs->fav_team . ") )  AND p.id =" . $projectfavs->id . ") ";
			}

			if (!empty($favConds))
			{
				$limitingconditions[] = implode(' OR ', $favConds);
			}
		}

		if (count($limitingconditions) > 0)
		{
			$query->where(" ( " . implode(' OR ', $limitingconditions) . " ) ");
		}

		$limit = (isset($caldates['limitstart'])&&isset($caldates['limitend'])) ? ' LIMIT ' . $caldates['limitstart'] . ', ' . $caldates['limitend'] : '';

		$query->select('m.id,m.round_id,m.projectteam1_id,m.projectteam2_id,m.match_date,m.team1_result,m.team2_result,m.match_date as gamematchdate ');
		$query->select('p.timezone,p.name,p.alias');
		$query->select('match_date AS caldate,r.roundcode, r.name AS roundname, r.round_date_first, r.round_date_last,m.id as matchcode, p.id as project_id');
		$query->select('m.cancel,m.cancel_reason');
		$query->select('CONCAT_WS(\':\',p.id,p.alias) AS project_slug');
		$query->select('CONCAT_WS(\':\',m.id,CONCAT_WS("_",t1.alias,t2.alias)) AS match_slug ');
		$query->from('#__sportsmanagement_match as m');
		$query->join('INNER', '#__sportsmanagement_round as r ON r.id = m.round_id');
		$query->join('INNER', '#__sportsmanagement_project as p ON p.id = r.project_id');
		$query->join('LEFT', '#__sportsmanagement_project_team AS tt1 ON m.projectteam1_id = tt1.id');
		$query->join('LEFT', '#__sportsmanagement_project_team AS tt2 ON m.projectteam2_id = tt2.id');
		$query->join('LEFT', '#__sportsmanagement_season_team_id AS st1 ON st1.id = tt1.team_id ');
		$query->join('LEFT', '#__sportsmanagement_season_team_id AS st2 ON st2.id = tt2.team_id ');
		$query->join('LEFT', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
		$query->join('LEFT', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');

			  $query->where("m.published = 1");
		$query->where("p.published = 1");

		if (isset($caldates['start']))
		{
			$query->where("m.match_timestamp >= " . $caldates['starttimestamp']);
		}

		if (isset($caldates['end']))
		{
					  $query->where("m.match_timestamp <= " . $caldates['endtimestamp']);
		}

		if (isset($caldates['matchcode']))
		{
					  $query->where("r.matchcode LIKE " . $db->Quote('' . $caldates['matchcode'] . '') . "");
		}

					$projectid = self::$xparams->get('p');

		if (is_array($projectid))
		{
			foreach ($projectid as $key => $value)
			{
				$projectid[$key] = (int) $value;
			}
		}

		if ($projectid)
		{
			   $projectids = (is_array($projectid)) ? implode(",", array_map('intval', $projectid)) : (int) $projectid;

			if ($projectids > 0)
			{
					  $query->where("p.id IN (" . $projectids . ")");
			}
		}

		if (isset($caldates['resultsonly']) && $caldates['resultsonly'] == 1)
		{
			  $query->where("m.team1_result IS NOT NULL");
		}

			$where .= $limitingcondition;

			  $query->group('m.id');
			$query->order('m.match_date ' . $ordering);

			 $db->setQuery($query, 0, $limit);

			  $result = $db->loadObjectList();

		if ($result)
		{
			foreach ($result as $match)
			{
				$match->timestamp = sportsmanagementHelper::getTimestamp($match->match_date, 1, $match->timezone);
				sportsmanagementHelper::convertMatchDateToTimezone($match);
			}
		}

			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			return $result;
	}

	/**
	 * SportsmanagementConnector::getTeamsFromMatches()
	 *
	 * @param   mixed $games
	 * @return
	 */
	static function getTeamsFromMatches( &$games )
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		if (!count($games))
		{
			return Array();
		}

		foreach ($games as $m)
		{
					$teamsId[] = $m->projectteam1_id;
					$teamsId[] = $m->projectteam2_id;
		}

			$listTeamId = implode(",", array_unique($teamsId));

			  $query->select('tl.id AS teamtoolid, tl.division_id, tl.standard_playground, tl.start_points,tl.info, tl.team_id, tl.checked_out, tl.checked_out_time, tl.picture, tl.project_id');
			$query->select('t.id, t.name, t.short_name, t.middle_name, t.info, t.club_id');
			$query->select('c.logo_small, c.logo_middle, c.logo_big, c.country');
			$query->select('p.name AS project_name');

			  $query->from('#__sportsmanagement_team as t');
			$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = t.id ');

			  $query->join('INNER', '#__sportsmanagement_project_team as tl on tl.team_id = st.id');
			$query->join('INNER', '#__sportsmanagement_project as p on p.id = tl.project_id');
			$query->join('LEFT', '#__sportsmanagement_club as c on t.club_id = c.id');

			  $query->where("tl.id IN (" . $listTeamId . ")");
			$query->where("tl.project_id = p.id");

			$db->setQuery($query);

		if (!$result = $db->loadObjectList('teamtoolid'))
		{
			$result = Array();
		}

			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			return $result;
	}

	/**
	 * SportsmanagementConnector::build_url()
	 *
	 * @param   mixed $row
	 * @return void
	 */
	function build_url( &$row )
	{

	}

}
