<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_matches
 * @file       sportsmanagement.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

/**
 * MatchesSportsmanagementConnector
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class MatchesSportsmanagementConnector extends modMatchesSportsmanagementHelper
{

	/**
	 * MatchesSportsmanagementConnector::getCountGames()
	 *
	 * @param   mixed  $projectid
	 * @param   mixed  $ishd_update_hour
	 *
	 * @return boolean
	 */
	static function getCountGames($projectid, $ishd_update_hour)
	{
		$db              = Factory::getDBO();
		$app             = Factory::getApplication();
		$query           = $db->getQuery(true);
		$date            = time();
		$enddatum        = $date - ($ishd_update_hour * 60 * 60);  // Ein Tag später (stunden * minuten * sekunden)
		$match_timestamp = sportsmanagementHelper::getTimestamp($enddatum);
		$query->clear();
		$query->select('count(*) AS count');
		$query->from('#__sportsmanagement_match AS m ');
		$query->join('INNER', '#__sportsmanagement_round AS r on r.id = m.round_id ');
		$query->join('INNER', '#__sportsmanagement_project AS p on p.id = r.project_id ');
		$query->where('p.id = ' . $projectid);
		$query->where('m.team1_result IS NULL ');
		$query->where('m.match_timestamp < ' . $match_timestamp);
		$db->setQuery($query);

		try
		{
			$matchestoupdate = $db->loadResult();
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			return $matchestoupdate;
		}
		catch (Exception $e)
		{
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
			$msg  = $e->getMessage(); // Returns "Normally you would have other code...
			$code = $e->getCode(); // Returns '500';
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error'); // commonly to still display that error

			return false;
		}

	}

	/**
	 * MatchesSportsmanagementConnector::buildTeamLinks()
	 *
	 * @param   mixed  $obj
	 * @param   mixed  $nr
	 *
	 * @return boolean|string
	 */
	public function buildTeamLinks(&$obj, $nr)
	{
		if (($this->params->get('link_teams', 0) + $this->usedteamscheck($obj->team_id, $obj->project_id)) < 2)
		{
			return false;
		}

		$linktext      = '';
		$urls          = array();
		$linkstructure = array(
			'club'     => array(
				'view' => 'clubinfo',
				'cid'  => $obj->club_id,
				'p'    => $obj->project_id
			),
			'teaminfo' => array(
				'view' => 'teaminfo',
				'tid'  => $obj->team_id,
				'p'    => $obj->project_id
			),
			'roster'   => array(
				'view' => 'roster',
				'tid'  => $obj->team_id,
				'p'    => $obj->project_id
			),
			'curve'    => array(
				'view' => 'curve',
				'tid'  => $obj->team_id,
				'p'    => $obj->project_id
			),
			'plan'     => array(
				'view' => 'teamplan',
				'tid'  => $obj->team_id,
				'p'    => $obj->project_id
			)
		);

		foreach ($linkstructure AS $linktype => $urlarray)
		{
			if ($this->params->get('link_team_' . $linktype, 1) == 1)
			{
				$linktext .= '<a href="' . Route::_('index.php?option=com_sportsmanagement' . $this->arrayToUri($urlarray) . $this->itemid) . '">' . $this->addteamicon('link_team_' . $linktype) . '</a>';
			}
		}

		if ($this->params->get('link_team_www', 1) == 1)
		{
			if (!empty($obj->website))
			{
				$linktext .= '<a href="' . trim($obj->website) . '">' . $this->addteamicon('link_team_www') . '</a>';
			}
		}

		if (!empty($linktext))
		{
			$linktext = '<span class="jlmlTeamLinks" style="display:' . $this->params->get('team_link_status', 'none') . ';" id="jlmlTeamname' . $obj->id . 'mod' . $this->module_id . 'nr' . $nr . '_over">' .
				$linktext . '</span>';
		}

		return $linktext;
	}

	/**
	 * MatchesSportsmanagementConnector::getMatches()
	 *
	 * @return boolean
	 */
	public function getMatches()
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;

		// Get a refrence of the page instance in joomla
		$document = Factory::getDocument();

		// Get a db connection.
		$db    = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		$limit = ($this->params->get('limit', 0) > 0) ? $this->params->get('limit', 0) : 1;

		$p = $this->params->get('p');

		/**
		 * da wir in der tabelle mit den spielen: sportsmanagement_match den timestamp
		 * speichern, ist es wesentlich einfacher die abfragen zu generieren
		 *
		 * laut modulparameter wird der timestamp für die gespielten paarungen berechnet
		 */
		if ($this->params->get('show_played', 0))
		{
			$enddatum_played_matches = time();    // Aktuelles Datum
			$stunden                 = 24;   // z.B. ein Tag
			$minuten                 = 60;
			$sekunden                = 60;

			switch ($this->params->get('result_add_unit', 'HOUR'))
			{
				case 'SECOND':
					$zeit = $this->params->get('result_add_time', 0);
					break;
				case 'MINUTE':
					$zeit = $this->params->get('result_add_time', 0) * $sekunden;
					break;
				case 'HOUR':
					$zeit = $this->params->get('result_add_time', 0) * $minuten * $sekunden;
					break;
				case 'DAY':
					$zeit = $this->params->get('result_add_time', 0) * $stunden * $minuten * $sekunden;
					break;
			}

			$startdatum_played_matches = $enddatum_played_matches - ($zeit);
		}

		/**
		 * laut modulparameter wird der timestamp für die zukünftige paarungen berechnet
		 */
		if ($this->params->get('show_nextmatches', 0))
		{
			$startdatum_next_matches = time();
			$stunden                 = 24;
			$minuten                 = 60;
			$sekunden                = 60;

			switch ($this->params->get('period_string', 'HOUR'))
			{
				case 'SECOND':
					$zeit = $this->params->get('period_int', 0);
					break;
				case 'MINUTE':
					$zeit = $this->params->get('period_int', 0) * $sekunden;
					break;
				case 'HOUR':
					$zeit = $this->params->get('period_int', 0) * $minuten * $sekunden;
					break;
				case 'DAY':
					$zeit = $this->params->get('period_int', 0) * $stunden * $minuten * $sekunden;
					break;
			}

			$enddatum_next_matches = $startdatum_next_matches + ($zeit);
		}

		$projectstring = (is_array($p)) ? implode(",", array_map('intval', $p)) : (int) $p;

		$nu            = $this->params->get('project_not_used');
		$notusedstring = (is_array($nu)) ? implode(",", array_map('intval', $nu)) : (int) $nu;

		$teams = $this->params->get('teams');
		$fav   = '';

		if ($this->params->get('use_fav', 0))
		{
			$query->clear();
			$query->select('id, fav_team');
			$query->from('#__sportsmanagement_project AS p ');
			$query->where("p.fav_team != '' ");
			$query->where('p.id IN ( ' . $projectstring . ' )');

			if ($notusedstring)
			{
				$query->where('p.id NOT IN ( ' . $notusedstring . ' )');
			}

			$db->setQuery($query);
			$fav = $db->loadObjectList();
		}

		$usedteams = (is_array($teams)) ? implode(",", array_map('intval', $teams)) : (int) $teams;

		$favteams = array();

		if ($fav)
		{
			foreach ($fav AS $key => $team)
			{
				$favteams[] = $team->fav_team;
			}
		}

		$query->clear();
		$query->select('m.id,m.id as match_id,m.projectteam1_id,m.projectteam2_id,m.round_id,m.team1_result,m.team2_result');
		$query->select('m.team1_result_split,m.team2_result_split,m.match_result_detail,m.match_result_type,m.crowd,m.show_report,m.playground_id ');
		$query->select('m.team1_result_ot,m.team2_result_ot,m.team1_result_so,m.team2_result_so');
		$query->select('m.match_result_type');
		$query->select('m.cancel,m.cancel_reason');
		$query->select('r.name');
		$query->select('m.match_date AS match_date,m.match_timestamp');
		$query->select('UTC_TIMESTAMP() AS currenttime');
		$query->select('p.game_regular_time+(p.game_parts * p.halftime)-p.halftime as totaltime');
		$query->select('p.name AS pname,p.current_round,p.id AS project_id,p.timezone,p.game_parts,p.ordering');
		$query->select('t1.id as team1_id');
		$query->select('t2.id as team2_id');
		$query->select('IF (mref.project_referee_id > 0, concat(person.lastname, \', \', person.firstname), \'\') AS refname');
		$query->select('IF (m.team1_result IS NULL, \'z\', \'\') AS live');
		$query->select('CONCAT_WS( \':\', p.id, p.alias ) AS project_slug');
		$query->select('CONCAT_WS( \':\', r.id, r.alias ) AS round_slug');
		$query->select('CONCAT_WS(\':\',m.id,CONCAT_WS("_",t1.alias,t2.alias)) AS match_slug ');
		$query->select('playground.name as pg_name, playground.short_name as pg_shortname');

		// From
		$query->from('#__sportsmanagement_match AS m ');

		// Join
		$query->join('INNER', '#__sportsmanagement_round AS r ON m.round_id = r.id ');
		$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = r.project_id ');
		$query->join('LEFT', '#__sportsmanagement_project_team AS pt1 ON m.projectteam1_id = pt1.id');
		$query->join('LEFT', '#__sportsmanagement_project_team AS pt2 ON m.projectteam2_id = pt2.id');

		$query->join('LEFT', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id ');
		$query->join('LEFT', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id ');

		$query->join('LEFT', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
		$query->join('LEFT', '#__sportsmanagement_club AS c1 ON c1.id = t1.club_id');

		$query->join('LEFT', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');
		$query->join('LEFT', '#__sportsmanagement_club AS c2 ON c2.id = t2.club_id');
		$query->join('LEFT', '#__sportsmanagement_playground AS playground ON playground.id = m.playground_id');

		$query->join('LEFT', '#__sportsmanagement_match_referee AS mref ON mref.match_id = m.id');
		$query->join('LEFT', '#__sportsmanagement_project_referee AS pref ON pref.id = mref.project_referee_id');
		$query->join('LEFT', '#__sportsmanagement_season_person_id AS sp ON pref.person_id = sp.id ');
		$query->join('LEFT', '#__sportsmanagement_person AS person ON person.id = sp.person_id ');

		$query->where('p.published = 1');
		$query->where('m.published = 1');

		/**
		 * gespielte und keine zukünftigen paarungen
		 */
		if ($this->params->get('show_played', 0) && !$this->params->get('show_nextmatches', 0))
		{
			$query->select('m.match_date AS alreadyplayed');
			$query->where('m.team1_result IS NOT NULL ');
			$query->where('( m.match_timestamp >= ' . $startdatum_played_matches . ' AND m.match_timestamp <= ' . $enddatum_played_matches . ' )');
		}

		/**
		 * zukünftige und keine gespielten paarungen
		 */
		if (!$this->params->get('show_played', 0) && $this->params->get('show_nextmatches', 0))
		{
			$query->select('m.match_date AS upcoming');
			$query->where('m.team1_result IS NULL ');
			$query->where('( m.match_timestamp >= ' . $startdatum_next_matches . ' AND m.match_timestamp <= ' . $enddatum_next_matches . ' )');
		}

		/**
		 * zukünftige und gespielte paarungen
		 */
		if ($this->params->get('show_played', 0) && $this->params->get('show_nextmatches', 0))
		{
			$query->where('( m.match_timestamp >= ' . $startdatum_played_matches . ' AND m.match_timestamp <= ' . $enddatum_next_matches . ' )');
		}

		if ($this->id > 0)
		{
			$query->where('m.id = ' . $this->id);
		}

		$query->where('p.id IN ( ' . $projectstring . ' )');

		if ($notusedstring)
		{
			$query->where('p.id NOT IN ( ' . $notusedstring . ' )');
		}

		if ($usedteams)
		{
			$query->where(' ( st1.team_id IN (' . $usedteams . ' ) OR st2.team_id IN (' . $usedteams . ' ) )');
		}

		if ($favteams)
		{
			$query->where(' ( st1.team_id IN (' . implode(",", $favteams) . ' ) OR st2.team_id IN (' . implode(",", $favteams) . ' ) )');
		}

		if ($this->params->get('order_by_project') == 0)
		{
			if ($this->params->get('lastsortorder') == 'desc')
			{
				$query->order('match_date DESC');
			}
			else
			{
				$query->order('match_date');
			}
		}
		else
		{
			$query->order('match_date, p.ordering ASC');
		}

		try
		{
			$db->setQuery($query, 0, $limit);
			$matches = $db->loadObjectList();

			foreach ($matches AS $key => $match)
			{
				$match->live             = 'z';
				$match->actplaying       = 'z';
				$match->alreadyplayed    = 'z';
				$match->upcoming         = 'z';
				$match->match_enddate    = strtotime($match->match_date . ' + ' . $match->totaltime . ' minute');
				$match->currenttimestamp = sportsmanagementHelper::getTimestamp();

				if (in_array($match->currenttimestamp, range($match->match_timestamp, $match->match_enddate)))
				{
					$match->live       = true;
					$match->actplaying = true;
				}
				elseif ($match->currenttimestamp <= $match->match_enddate)
				{
					$match->upcoming = true;
				}
				elseif ($match->match_timestamp <= $match->currenttimestamp)
				{
					$match->alreadyplayed = true;
				}
			}

			if (ComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info_frontend'))
			{
			}

			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			return $this->formatMatches($matches);
		}
		catch (Exception $e)
		{
			$msg  = $e->getMessage(); // Returns "Normally you would have other code...
			$code = $e->getCode(); // Returns '500';
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error'); // commonly to still display that error
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			return false;
		}

	}

	/**
	 * MatchesSportsmanagementConnector::formatHeading()
	 *
	 * @param   mixed  $row
	 * @param   mixed  $match
	 *
	 * @return void
	 */
	public function formatHeading(&$row, $match)
	{
		$pview         = $this->params->get('p_link_func', 'results');
		$rview         = $this->params->get('r_link_func', 'results');
		$views         = array(
			'results'     => 'results',
			'resultsrank' => 'resultsranking',
			'ranking'     => 'ranking'
		);
		$linkstructure = array(
			'project'  => array(
				'view'               => $views[$pview],
				'cfg_which_database' => 0,
				's'                  => 0,
				'p'                  => $match->project_slug
			),
			'matchday' => array(
				'view'               => $views[$rview],
				'cfg_which_database' => 0,
				's'                  => 0,
				'p'                  => $match->project_slug,
				'r'                  => $match->round_slug
			)
		);
		$heading       = '';
		$heading2      = '';

		if ($this->params->get('show_project_title') != 0)
		{
			if ($this->params->get('link_project_title') != 0)
			{
				$heading = '<a href="' . Route::_('index.php?option=com_sportsmanagement' . $this->arrayToUri($linkstructure['project']) . $this->itemid) . '">';
			}

			$heading .= $this->jl_utf8_convert($match->pname, 'iso-8859-1', 'utf-8');

			if ($this->params->get('link_project_title') != 0)
			{
				$heading .= '</a>';
			}

			if ($this->params->get('show_matchday_title') != 0)
			{
				$heading2 .= ' - ';
			}
		}

		$row['pname'] = $heading;

		if ($this->params->get('show_matchday_title') != 0)
		{
			if ($this->params->get('link_matchday_title') != 0)
			{
				$heading2 .= '<a href="' . Route::_('index.php?option=com_sportsmanagement' . $this->arrayToUri($linkstructure['matchday']) . $this->itemid) . '">';
			}

			$heading2 .= $this->jl_utf8_convert($match->name, 'iso-8859-1', 'utf-8');

			if ($this->params->get('link_matchday_title') != 0)
			{
				$heading2 .= '</a>';
			}

			$row['rname'] = $heading2;
		}

		$row['heading'] = $heading . $heading2;
	}

	/**
	 * MatchesSportsmanagementConnector::getTeamsFromMatches()
	 *
	 * @param   mixed  $matches
	 *
	 * @return array|boolean
	 */
	public function getTeamsFromMatches(&$matches)
	{
		$db    = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		if (!count($matches))
		{
			return Array();
		}

		foreach ($matches as $m)
		{
			$cond[] = "(pt.id = '" . $m->projectteam1_id . "'
					    OR  pt.id = '" . $m->projectteam2_id . "')";
		}

		$query->select('pt.id as ptid, pt.division_id, pt.standard_playground, pt.admin, pt.start_points');
		$query->select('pt.info, pt.team_id, pt.checked_out, pt.checked_out_time, pt.picture team_picture, pt.project_id');
		$query->select('t.id, t.name, t.short_name, t.middle_name, t.notes, t.club_id');
		$query->select('pg1.id AS tt_pg_id, pg1.name AS tt_pg_name, pg1.short_name AS tt_pg_short_name');
		$query->select('pg2.id AS club_pg_id, pg2.name AS club_pg_name, pg2.short_name AS club_pg_short_name');
		$query->select('u.username, u.email, t.id team_id');
		$query->select('c.logo_small AS club_small, c.logo_middle AS club_middle, c.logo_big AS club_big, c.country');
		$query->select('d.name AS division_name, d.shortname AS division_shortname');
		$query->select('p.name AS project_name, c.website');

		$query->from('#__sportsmanagement_team as t');

		// Join
		$query->join('LEFT', '#__sportsmanagement_season_team_id as st1 ON st1.team_id = t.id ');
		$query->join('LEFT', '#__sportsmanagement_project_team as pt on pt.team_id = st1.id ');
		$query->join('LEFT', '#__users u on pt.admin = u.id ');
		$query->join('LEFT', '#__sportsmanagement_club as c on t.club_id = c.id ');
		$query->join('LEFT', '#__sportsmanagement_division as d on d.id = pt.division_id ');
		$query->join('LEFT', '#__sportsmanagement_project as p on p.id = pt.project_id ');
		$query->join('LEFT', '#__sportsmanagement_playground as pg1 ON pg1.id = pt.standard_playground ');
		$query->join('LEFT', '#__sportsmanagement_playground as pg2 ON pg2.id = c.standard_playground ');

		$query->where('(' . implode(' OR ', $cond) . ')');

		// $tempteams = $this->getFromDB($query, 'ptid');
		$db->setQuery($query);

		try
		{
			$tempteams = $db->loadObjectList();
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			$teams = array();

			foreach ((array) $tempteams AS $t)
			{
				$teams[$t->ptid] = $t;
			}

			return $teams;
		}
		catch (Exception $e)
		{
			$msg  = $e->getMessage(); // Returns "Normally you would have other code...
			$code = $e->getCode(); // Returns '500';
			$app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error'); // commonly to still display that error
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			return false;
		}

	}

	/**
	 * MatchesSportsmanagementConnector::createMatchLinks()
	 *
	 * @param   mixed  $row
	 * @param   mixed  $match
	 *
	 * @return void
	 */
	public function createMatchLinks(&$row, &$match)
	{
		$useicons = $this->iconpath;

		$row['reportlink']    = false;
		$row['statisticlink'] = false;
		$row['nextmatchlink'] = false;
		$linkstructure        = array(
			'report'    => array(
				'view'               => 'matchreport',
				'cfg_which_database' => 0,
				's'                  => 0,
				'p'                  => $match->project_slug,
				'mid'                => $match->match_slug

			),
			'nextmatch' => array(
				'view'               => 'nextmatch',
				'cfg_which_database' => 0,
				's'                  => 0,
				'p'                  => $match->project_slug,
				'mid'                => $match->match_slug

			),
			'statistic' => array(
				'view'               => 'stats',
				'cfg_which_database' => 0,
				's'                  => 0,
				'p'                  => $match->project_slug
			),
		);

		if ($this->params->get('show_act_report_link', 0) == 1 && $match->show_report == 1)
		{
			$uri               = 'index.php?option=com_sportsmanagement' . $this->arrayToUri($linkstructure['report']) . $this->itemid;
			$row['reportlink'] = '<a href="' . JRoute::_($uri) . '" title="' . $this->params->get('show_act_report_text') . '">';
			$row['reportlink'] .= ($useicons) ? HTMLHelper::_(
				'image', $this->iconpath . 'report.png', $this->params->get('show_act_report_text'), array(
					'title'  => $this->params->get('show_act_report_text'),
					'height' => '16',
					'width'  => '16'
				)
			) : $this->params->get('show_act_report_text') . '<br />';
			$row['reportlink'] .= '</a>';
		}

		if ($this->params->get('show_statistic_link', 0) == 1 && ($match->team1_result || $match->team2_result))
		{
			$uri                  = 'index.php?option=com_sportsmanagement' . $this->arrayToUri($linkstructure['statistic']) . $this->itemid;
			$row['statisticlink'] = '<a href="' . Route::_($uri) . '" title="' . $this->params->get('statistic_link_text') . '">';
			$row['statisticlink'] .= ($useicons) ? HTMLHelper::_(
				'image', $this->iconpath . 'history.png', $this->params->get('statistic_link_text'), array(
					'title'  => $this->params->get('statistic_link_text'),
					'height' => '16',
					'width'  => '16'
				)
			) : $this->params->get('statistic_link_text') . '<br />';
			$row['statisticlink'] .= '</a>';
		}

		if ($this->params->get('show_nextmatch_link', 0) == 1 && !($match->team1_result || $match->team2_result))
		{
			$uri                  = 'index.php?option=com_sportsmanagement' . $this->arrayToUri($linkstructure['nextmatch']) . $this->itemid;
			$row['nextmatchlink'] = '<a href="' . Route::_($uri) . '" title="' . $this->params->get('statistic_link_text') . '">';
			$row['nextmatchlink'] .= ($useicons) ? HTMLHelper::_(
				'image', $this->iconpath . 'history.png', $this->params->get('nextmatch_link_text'), array(
					'title'  => $this->params->get('nextmatch_link_text'),
					'height' => '16',
					'width'  => '16'
				)
			) : $this->params->get('nextmatch_link_text') . '<br />';
			$row['nextmatchlink'] .= '</a>';
		}

	}

	/**
	 * MatchesSportsmanagementConnector::createLocation()
	 *
	 * @param   mixed  $row
	 * @param   mixed  $match
	 * @param   mixed  $team
	 *
	 * @return void
	 */
	public function createLocation(&$row, &$match, &$team)
	{

		$thisvenue = false;

		if ($team && $this->params->get('show_venue') != 0)
		{
			$location_id   = 0;
			$usedvenuename = $this->params->get('venue_name');
			$venue         = array(
				'id' => 0
			);

			if ($match->playground_id > 0)
			{
				$venue['id']         = $match->playground_id;
				$venue['name']       = $match->pg_name;
				$venue['short_name'] = $match->pg_shortname;
			}
			elseif ($team->tt_pg_id > 0)
			{
				$venue['id']         = $team->tt_pg_id;
				$venue['name']       = $team->tt_pg_name;
				$venue['short_name'] = $team->tt_pg_short_name;
			}
			elseif ($team->club_pg_id > 0)
			{
				$venue['id']         = $team->club_pg_id;
				$venue['name']       = $team->club_pg_name;
				$venue['short_name'] = $team->club_pg_short_name;
			}

			if ($venue['id'] > 0)
			{
				$venuename = ($usedvenuename == 'name') ? $this->jl_utf8_convert($venue[$usedvenuename], 'iso-8859-1', 'utf-8') : '<acronym title="' .
					$this->jl_utf8_convert($venue['name'], 'iso-8859-1', 'utf-8') . '">' .
					$this->jl_utf8_convert($venue[$usedvenuename], 'iso-8859-1', 'utf-8') . '</acronym>';
				$venuetext = '%s';
				$venuetip  = $this->params->get('venue_text');

				if ($this->params->get('link_venue') == 1)
				{
					$linkstructure = array(
						'venue' => array(
							'view'               => 'playground',
							'cfg_which_database' => $this->params->get('cfg_which_database'),
							's'                  => $this->params->get('s'),
							'p'                  => $match->project_id,
							'pgid'               => $venue['id']

						)
					);
					$venuelink     = Route::_('index.php?option=com_sportsmanagement' . $this->arrayToUri($linkstructure['venue']) . $this->itemid);
					$venuetext     = '<a href="' . $venuelink . '" title="%s">%s</a>';
					$thisvenue     = sprintf($venuetext, $venuetip, $venuename);
				}
				else
				{
					$thisvenue = sprintf($venuetext, $venuename);
				}

				if ($this->iconpath)
				{
					$thisvenue = HTMLHelper::_(
							'image', $this->iconpath . 'house.png', $venuetip, array(
								'title'  => $venuetip,
								'height' => '16',
								'width'  => '16'
							)
						) . ' ' . $thisvenue;
				}
				else
				{
					$thisvenue = $venuetip . ' ' . $thisvenue;
				}
			}
		}

		if (ComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info_frontend'))
		{
		}

		$row['location'] = $thisvenue;
	}

	/**
	 * MatchesSportsmanagementConnector::getDefaultLogos()
	 *
	 * @return
	 */
	public function getDefaultLogos()
	{
		return array(
			"club_big"     => sportsmanagementHelper::getDefaultPlaceholder('clublogobig'),
			"club_middle"  => sportsmanagementHelper::getDefaultPlaceholder('clublogomedium'),
			"club_small"   => sportsmanagementHelper::getDefaultPlaceholder('clublogosmall'),
			"team_picture" => sportsmanagementHelper::getDefaultPlaceholder('team'),
			"country"      => sportsmanagementHelper::getDefaultPlaceholder('icon'),
		);
	}


	/**
	 * MatchesSportsmanagementConnector::next_last2()
	 *
	 * @param   mixed  $match
	 * @param   bool   $allprojects
	 *
	 * @return void
	 */
	public function next_last(&$match, $allprojects = false)
	{
		$db    = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		$currenttimestamp = sportsmanagementHelper::getTimestamp();
		$result_add_time  = $this->params->get('result_add_time');
		$period_int       = $this->params->get('period_int');
		$currentdate      = date('Y-m-d H:i:s', $currenttimestamp);
		$datebis          = strtotime($currentdate . ' + ' . $period_int . ' ' . $this->params->get('period_string'));
		$datevon          = strtotime($currentdate . ' - ' . $result_add_time . ' ' . $this->params->get('result_add_unit'));

		if ($allprojects)
		{
			$match->lasthome = $match->nexthome = $match->lastaway = $match->nextaway = false;
			$p               = $this->params->get('p');
			$projectstring   = (is_array($p)) ? implode(",", array_map('intval', $p)) : (int) $p;
		}
		else
		{
			$projectstring = $match->project_id;
		}

		$match->lasthome = 0;
		$match->nexthome = 0;
		$match->lastaway = 0;
		$match->nextaway = 0;

		// Select some fields
		$query->select('m.id');

		// From
		$query->from('#__sportsmanagement_match AS m ');

		// Join
		$query->join('LEFT', '#__sportsmanagement_project_team pt1 ON pt1.id = m.projectteam1_id ');
		$query->join('LEFT', '#__sportsmanagement_season_team_id as st1 ON st1.id = pt1.team_id ');
		$query->join('INNER', '#__sportsmanagement_team t1 ON t1.id = st1.team_id ');

		$query->join('LEFT', '#__sportsmanagement_project_team pt2 ON pt2.id = m.projectteam2_id ');
		$query->join('LEFT', '#__sportsmanagement_season_team_id as st2 ON st2.id = pt2.team_id ');
		$query->join('INNER', '#__sportsmanagement_team t2 ON t2.id = st2.team_id ');

		$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = pt1.project_id');

		$query->where("(m.match_timestamp < " . $match->match_timestamp . ' AND m.match_timestamp > ' . $datevon . ' )');
		$query->where('(t1.id  = ' . $match->team1_id . ' OR t2.id = ' . $match->team1_id . ' )');
		$query->where('p.id IN (' . $projectstring . ')');
		$query->order('m.match_date DESC');

		$db->setQuery($query, 0, 1);

		if ($temp = $db->loadObjectList())
		{
			$match->lasthome = $temp[0]->id;
		}

		$query->clear('where');
		$query->clear('order');
		$query->where("(m.match_timestamp > " . $match->match_timestamp . ' AND m.match_timestamp < ' . $datebis . ' )');
		$query->where('(t1.id  = ' . $match->team1_id . ' OR t2.id = ' . $match->team1_id . ' )');
		$query->where('p.id IN (' . $projectstring . ')');
		$query->order('m.match_date ASC');

		$db->setQuery($query, 0, 1);

		if ($temp = $db->loadObjectList())
		{
			$match->nexthome = $temp[0]->id;
		}

		$query->clear('where');
		$query->clear('order');
		$query->where("(m.match_timestamp < " . $match->match_timestamp . ' AND m.match_timestamp > ' . $datevon . ' )');
		$query->where('(t1.id  = ' . $match->team2_id . ' OR t2.id = ' . $match->team2_id . ' )');
		$query->where('p.id IN (' . $projectstring . ')');
		$query->order('m.match_date DESC');

		$db->setQuery($query, 0, 1);

		if ($temp = $db->loadObjectList())
		{
			$match->lastaway = $temp[0]->id;
		}

		$query->clear('where');
		$query->clear('order');
		$query->where("(m.match_timestamp > " . $match->match_timestamp . ' AND m.match_timestamp < ' . $datebis . ' )');
		$query->where('(t1.id  = ' . $match->team2_id . ' OR t2.id = ' . $match->team2_id . ' )');
		$query->where('p.id IN (' . $projectstring . ')');
		$query->order('m.match_date ASC');

		$db->setQuery($query, 0, 1);

		if ($temp = $db->loadObjectList())
		{
			$match->nextaway = $temp[0]->id;
		}

		$db->disconnect();
	}
}
