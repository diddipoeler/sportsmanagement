<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       complexsumpergame.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage statistics
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

JLoader::import('components.com_sportsmanagement.statistics.base', JPATH_ADMINISTRATOR);

/**
 * SMStatisticComplexsumpergame
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class SMStatisticComplexsumpergame extends SMStatistic
{
	/**
	 * also the name of the associated xml file
	 */
	var $_name = 'complexsumpergame';

	var $_calculated = 1;

	var $_showinsinglematchreports = 0;

	var $_ids = 'stat_ids';

	/**
	 * SMStatisticComplexsumpergame::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * SMStatisticComplexsumpergame::getFactors()
	 *
	 * @return
	 */
	function getFactors()
	{
		$params  = SMStatistic::getParams();
		$factors = explode(',', $params->get('factors'));
		$stat_ids = SMStatistic::getSids($this->_ids);

		if (count($stat_ids) != count($factors))
		{
			Log::add(Text::sprintf('STAT %s/%s WRONG CONFIGURATION - BAD FACTORS COUNT', $this->_name, $this->id), Log::WARNING, 'jsmerror');

			return(array(0));
		}

					  $sids = array();

		foreach ($factors as $s)
		{
					$sids[] = (float) $s;
		}

			return $sids;
	}

	/**
	 * SMStatisticComplexsumpergame::getPlayerStatsByProject()
	 *
	 * @param   mixed   $person_id
	 * @param   integer $projectteam_id
	 * @param   integer $project_id
	 * @param   integer $sports_type_id
	 * @return
	 */
	function getPlayerStatsByProject($person_id, $projectteam_id = 0, $project_id = 0, $sports_type_id = 0)
	{
		$sids = SMStatistic::getSids($this->_ids);
		$factors = self::getFactors();

			  $num = $this->getPlayerStatsByProjectForIds($person_id, $projectteam_id, $project_id, $sports_type_id, $sids, $factors);
		$den = $this->getGamesPlayedByPlayer($person_id, $projectteam_id, $project_id, $sports_type_id);

		return $this->formatValue($num, $den, $this->getPrecision());
	}


	/**
	 * SMStatisticComplexsumpergame::getRosterStats()
	 *
	 * @param   mixed $team_id
	 * @param   mixed $project_id
	 * @param   mixed $position_id
	 * @return
	 */
	function getRosterStats($team_id, $project_id, $position_id)
	{
		$sids = SMStatistic::getSids($this->_ids);
		$factors  = self::getFactors();
		$num = SMStatistic::getRosterStatsForIds($team_id, $project_id, $position_id, $sids, $factors);
		$den = SMStatistic::getGamesPlayedByProjectTeam($team_id, $project_id, $position_id);

		$precision = SMStatistic::getPrecision();
		$res = array();

		foreach (array_unique(array_merge(array_keys($num), array_keys($den))) as $person_id)
		{
			$res[$person_id] = new stdclass;
			$res[$person_id]->person_id = $person_id;
			$n = isset($num[$person_id]->value) ? $num[$person_id]->value : 0;
			$d = isset($den[$person_id]->value) ? $den[$person_id]->value : 0;
			$res[$person_id]->value = self::formatValue($n, $d, $precision);
		}

		return $res;
	}

	/**
	 * SMStatisticComplexsumpergame::getPlayersRanking()
	 *
	 * @param   mixed   $project_id
	 * @param   mixed   $division_id
	 * @param   mixed   $team_id
	 * @param   integer $limit
	 * @param   integer $limitstart
	 * @param   mixed   $order
	 * @return
	 */
	function getPlayersRanking($project_id, $division_id, $team_id, $limit = 20, $limitstart = 0, $order = null)
	{
		$sids = SMStatistic::getSids($this->_ids);
		$sqids = SMStatistic::getQuotedSids($this->_ids);
		$factors  = self::getFactors();
		$app = Factory::getApplication();
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

			  // Get all stats
		$query->select('ms.value, ms.statistic_id, tp.id AS tpid');
		$query->from('#__sportsmanagement_match_statistic AS ms');
		$query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON tp.id = ms.teamplayer_id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$query->join('INNER', '#__sportsmanagement_match AS m ON m.id = ms.match_id');

					$query->where('pt.project_id = ' . $project_id);

		if ($division_id != 0)
		{
			$query->where('pt.division_id = ' . $division_id);
		}

		if ($team_id != 0)
		{
			$query->where('st.team_id = ' . $team_id);
		}

		$query->where('ms.statistic_id IN (' . implode(',', $sqids) . ')');
		$query->where('m.published = 1');
		$db->setQuery($query);
		$stats = $db->loadObjectList();
		$query = SMStatistic::getGamesPlayedQuery($project_id, $division_id, $team_id);
		$db->setQuery($query);
		$gp = $db->loadObjectList('tpid');

			  // Now calculate per player
		$players = array();

		// First, the numerator complex sum
		foreach ($stats as $stat)
		{
			$key = array_search($stat->statistic_id, $sids);

			if ($key !== false)
			{
				@$players[$stat->tpid] += $factors[$key] * $stat->value;
			}
		}

		// Then divide by games played
		foreach ($players as $k => $value)
		{
			if (isset($gp[$k]) && $gp[$k]->played)
			{
				$players[$k] = $players[$k] / $gp[$k]->played;
			}
			else
			{
				unset($players[$k]);
			}
		}

			  // Now we reorder
		$order = (!empty($order) ? $order : $this->getParam('ranking_order', 'DESC'));

		if ($order == 'ASC')
		{
			asort($players);
		}
		else
		{
			arsort($players);
		}

		$res = new stdclass;
		$res->pagination_total = count($players);

		$players = array_slice($players, $limitstart, $limit, true);
		$ids = array_keys($players);

			  $query->clear();
		$query->select('tp.id AS teamplayer_id, tp.person_id, tp.picture AS teamplayerpic,p.firstname, p.nickname, p.lastname, p.picture, p.country,st.team_id, pt.picture AS projectteam_picture');
		$query->select('t.picture AS team_picture,t.name AS team_name, t.short_name AS team_short_name');
		$query->from('#__sportsmanagement_season_team_person_id AS tp');
		$query->join('INNER', '#__sportsmanagement_person AS p ON p.id = tp.person_id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$query->join('INNER', '#__sportsmanagement_team AS t ON st.team_id = t.id');
		$query->where('pt.project_id = ' . $project_id);
		$query->where('p.published = 1');
		$query->where('tp.id IN (' . implode(',', $ids) . ')');

		if ($division_id != 0)
		{
			$query->where('pt.division_id = ' . $division_id);
		}

		if ($team_id != 0)
		{
					$query->where('st.team_id = ' . $team_id);
		}

					$db->setQuery($query);
					$details = $db->loadObjectList('teamplayer_id');

					$res->ranking = array();

		if (!empty($details))
		{
					$precision = SMStatistic::getPrecision();

					// Get ranks
					$previousval = 0;
					$currentrank = 1 + $limitstart;
					$i = 0;

			foreach ($players as $k => $value)
			{
						$res->ranking[$i] = $details[$k];
						$res->ranking[$i]->total = $value;

				if ($value == $previousval)
				{
					$res->ranking[$i]->rank = $currentrank;
				}
				else
				{
					$res->ranking[$i]->rank = $i + 1 + $limitstart;
				}

				  $previousval = $value;
						$currentrank = $res->ranking[$i]->rank;

						$res->ranking[$i]->total = self::formatValue($res->ranking[$i]->total, 1, $precision);

						$i++;
			}
		}

					return $res;
	}


	/**
	 * SMStatisticComplexsumpergame::getTeamsRanking()
	 *
	 * @param   integer $project_id
	 * @param   integer $limit
	 * @param   integer $limitstart
	 * @param   mixed   $order
	 * @return
	 */
	function getTeamsRanking($project_id = 0, $limit = 20, $limitstart = 0, $order = null)
	{
		$sids = SMStatistic::getSids($this->_ids);
		$sqids = SMStatistic::getQuotedSids($this->_ids);
		$factors  = self::getFactors();

			  $db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		// Team games
		$query->select('COUNT(m.id) AS value, st.team_id');
		$query->from('#__sportsmanagement_project_team AS pt');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.id = pt.team_id ');
		$query->join('INNER', '#__sportsmanagement_match AS m ON m.projectteam1_id = pt.id OR m.projectteam2_id = pt.id AND m.published = 1 AND m.team1_result IS NOT NULL');
		$query->where('pt.project_id = ' . $project_id);
		$query->group('pt.id');

			  $db->setQuery($query);
		$gp = $db->loadObjectList('team_id');

					  // Get all stats
		$query->clear();
		$select = 'ms.value, ms.statistic_id, st.team_id ';
		$statistic_id = implode(',', $sqids);
		$query = SMStatistic::getTeamsRanking($project_id, $limit, $limitstart, $order, $select, $statistic_id);
		$db->setQuery($query);
		$stats = $db->loadObjectList();

			  // Now calculate per team
		$teams = array();

		foreach ($stats as $stat)
		{
			$key = array_search($stat->statistic_id, $sids);

			if ($key !== false)
			{
				@$teams[$stat->team_id] += $factors[$key] * $stat->value;
			}
		}

		// Then divide by games played
		foreach ($teams as $k => $value)
		{
			if (isset($gp[$k]) && $gp[$k]->value)
			{
				$teams[$k] = $teams[$k] / $gp[$k]->value;
			}
			else
			{
				unset($teams[$k]);
			}
		}

			  // Now we reorder
		$order = (!empty($order) ? $order : $this->getParam('ranking_order', 'DESC'));

		if ($order == 'ASC')
		{
			asort($teams);
		}
		else
		{
			arsort($teams);
		}

			  $teams = array_slice($teams, $limitstart, $limit, true);

			  $res = array();

		foreach ($teams as $id => $value)
		{
			$obj = new stdclass;
			$obj->team_id = $id;
			$obj->total   = $value;
			$res[] = $obj;
		}

		if (!empty($res))
		{
			$precision = $this->getPrecision();

			// Get ranks
			$previousval = 0;
			$currentrank = 1 + $limitstart;

			foreach ($res as $k => $row)
			{
				if ($row->total == $previousval)
				{
					$res[$k]->rank = $currentrank;
				}
				else
				{
					$res[$k]->rank = $k + 1 + $limitstart;
				}

				$previousval = $row->total;
				$currentrank = $res[$k]->rank;

				$res[$k]->total = $this->formatValue($res[$k]->total, 1, $precision);
			}
		}

		return $res;
	}

	/**
	 * SMStatisticComplexsumpergame::getStaffStats()
	 *
	 * @param   mixed $person_id
	 * @param   mixed $team_id
	 * @param   mixed $project_id
	 * @return
	 */
	function getStaffStats($person_id, $team_id, $project_id)
	{
		$sids = SMStatistic::getSids($this->_ids);
		$sqids = SMStatistic::getQuotedSids($this->_ids);
		$factors  = self::getFactors();

			  $db = sportsmanagementHelper::getDBConnection();
		$select = 'ms.value, ms.statistic_id ';
		$query = SMStatistic::getStaffStatsQuery($person_id, $team_id, $project_id, $sqids, $select, false);

		$db->setQuery($query);
		$stats = $db->loadObjectList();

			  $num = 0;

		foreach ($stats as $stat)
		{
			$key = array_search($stat->statistic_id, $sids);

			if ($key !== false)
			{
				$num += $factors[$key] * $stat->value;
			}
		}

			  // Games
		$select = 'COUNT(ms.id) AS value, tp.person_id ';
		$query = SMStatistic::getStaffStatsQuery($person_id, $team_id, $project_id, $sqids, $select, false, 'match_staff');

			  $db->setQuery($query);
		$den = $db->loadResult();

			  return $this->formatValue($num, $den, $this->getPrecision());
		;
	}

	/**
	 * SMStatisticComplexsumpergame::getHistoryStaffStats()
	 *
	 * @param   mixed $person_id
	 * @return
	 */
	function getHistoryStaffStats($person_id)
	{
		$sids = SMStatistic::getSids($this->_ids);
		$sqids = $this->getQuotedSids();
		$factors  = $this->getFactors();

			  $db = sportsmanagementHelper::getDBConnection();
		$select = 'ms.value, ms.statistic_id ';
		$query = SMStatistic::getStaffStatsQuery($person_id, 0, 0, $sqids, $select, true);

		$db->setQuery($query);
		$stats = $db->loadObjectList();

			  $num = 0;

		foreach ($stats as $stat)
		{
			$key = array_search($stat->statistic_id, $sids);

			if ($key !== false)
			{
				$num += $factors[$key] * $stat->value;
			}
		}

			  // Games
		$select = 'COUNT(ms.id) AS value, tp.person_id ';
		$query = SMStatistic::getStaffStatsQuery($person_id, 0, 0, $sqids, $select, true, 'match_staff');
		$db->setQuery($query);
		$den = $db->loadResult();

			  return self::formatValue($num, $den, $this->getPrecision());
		;
	}

	/**
	 * SMStatisticComplexsumpergame::formatValue()
	 *
	 * @param   mixed $num
	 * @param   mixed $den
	 * @param   mixed $precision
	 * @return
	 */
	function formatValue($num, $den, $precision)
	{
		$value = (!empty($num) && !empty($den)) ? $num / $den : 0;

		return number_format($value, $precision);
	}
}
