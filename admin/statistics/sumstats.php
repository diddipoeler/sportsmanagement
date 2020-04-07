<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage statistics
 * @file       sumstats.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

JLoader::import('components.com_sportsmanagement.statistics.base', JPATH_ADMINISTRATOR);

/**
 * SMStatisticSumstats
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class SMStatisticSumstats extends SMStatistic
{
	// Also the name of the associated xml file
	var $_name = 'sumstats';

	var $_calculated = 1;

	var $_showinsinglematchreports = 1;

	var $_ids = 'stat_ids';

	/**
	 * SMStatisticSumstats::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();
	}





	/**
	 * SMStatisticSumstats::getMatchPlayerStat()
	 *
	 * @param   mixed $gamemodel
	 * @param   mixed $teamplayer_id
	 * @return
	 */
	function getMatchPlayerStat(&$gamemodel, $teamplayer_id)
	{
		$gamestats = $gamemodel->getPlayersStats();
		$stat_ids = SMStatistic::getSids($this->_ids);

			  $res = 0;

		foreach ($stat_ids as $id)
		{
			if (isset($gamestats[$teamplayer_id][$id]))
			{
				$res += $gamestats[$teamplayer_id][$id];
			}
		}

		return self::formatValue($res, $this->getPrecision());
	}

	/**
	 * SMStatisticSumstats::getPlayerStatsByGame()
	 *
	 * @param   mixed $teamplayer_ids
	 * @param   mixed $project_id
	 * @return
	 */
	function getPlayerStatsByGame($teamplayer_ids, $project_id)
	{
		$sids = SMStatistic::getSids($this->_ids);
		$res = SMStatistic::getPlayerStatsByGameForIds($teamplayer_ids, $project_id, $sids);

		if (is_array($res))
		{
			$precision = SMStatistic::getPrecision();

			foreach ($res as $k => $match)
			{
				$res[$k]->value = self::formatValue($res[$k]->value, $precision);
			}
		}

		return $res;
	}

	/**
	 * SMStatisticSumstats::getPlayerStatsByProject()
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
		$value = SMStatistic::getPlayerStatsByProjectForIds($person_id, $projectteam_id, $project_id, $sports_type_id, $sids);

		return self::formatValue($value, SMStatistic::getPrecision());
	}

	/**
	 * Get players stats
	 *
	 * @param  $team_id
	 * @param  $project_id
	 * @return array
	 */
	function getRosterStats($team_id, $project_id, $position_id)
	{
		$sids = SMStatistic::getSids($this->_ids);
		$res = SMStatistic::GetRosterStatsForIds($team_id, $project_id, $position_id, $sids);

		if (!empty($res))
		{
			$precision = SMStatistic::getPrecision();

			foreach ($res as $k => $player)
			{
				$res[$k]->value = self::formatValue($res[$k]->value, $precision);
			}
		}

		return $res;
	}

	/**
	 * SMStatisticSumstats::getPlayersRanking()
	 *
	 * @param   mixed   $project_id
	 * @param   mixed   $division_id
	 * @param   mixed   $team_id
	 * @param   integer $limit
	 * @param   integer $limitstart
	 * @param   mixed   $order
	 * @return
	 */
	function getPlayersRanking($project_id, $division_id, $team_id, $limit = 20, $limitstart = 0, $order=null)
	{
		$sids = SMStatistic::getQuotedSids($this->_ids);
		$app = Factory::getApplication();
		$db = sportsmanagementHelper::getDBConnection();
		$query_num = Factory::getDbo()->getQuery(true);

		$query_select_count = 'COUNT(DISTINCT tp.id) as count';

		$query_select_details    = 'SUM(ms.value) AS total,'
								. ' tp.id AS teamplayer_id, tp.person_id, tp.picture AS teamplayerpic,'
								. ' p.firstname, p.nickname, p.lastname, p.picture, p.country,'
								. ' st.team_id, pt.picture AS projectteam_picture, t.picture AS team_picture,'
								. ' t.name AS team_name, t.short_name AS team_short_name';

			 $query_core    = SMStatistic::getPlayersRankingStatisticQuery($project_id, $division_id, $team_id, $sids, $query_select_count, 'statistic');

		$res = new stdclass;
		$db->setQuery($query_core);

			  $res->pagination_total = $db->loadResult();

			  $query_core->clear('select');
		$query_core->select($query_select_details);
		$query_core->order('total ' . (!empty($order) ? $order : $this->getParam('ranking_order', 'DESC')) . ', tp.id');

		$db->setQuery($query_core, $limitstart, $limit);

			  $res->ranking = $db->loadObjectList();

		if ($res->ranking)
		{
			$precision = SMStatistic::getPrecision();

			// Get ranks
			$previousval = 0;
			$currentrank = 1 + $limitstart;

			foreach ($res->ranking as $k => $row)
			{
				if ($row->total == $previousval)
				{
					$res->ranking[$k]->rank = $currentrank;
				}
				else
				{
					$res->ranking[$k]->rank = $k + 1 + $limitstart;
				}

				$previousval = $row->total;
				$currentrank = $res->ranking[$k]->rank;

				$res->ranking[$k]->total = self::formatValue($res->ranking[$k]->total, $precision);
			}
		}

		return $res;
	}

	/**
	 * SMStatisticSumstats::getTeamsRanking()
	 *
	 * @param   mixed   $project_id
	 * @param   integer $limit
	 * @param   integer $limitstart
	 * @param   mixed   $order
	 * @param   string  $select
	 * @param   integer $statistic_id
	 * @return
	 */
	function getTeamsRanking($project_id, $limit = 20, $limitstart = 0, $order=null, $select = '', $statistic_id = 0)
	{
		$sids = SMStatistic::getQuotedSids($this->_ids);
		$db = sportsmanagementHelper::getDBConnection();

			  $select = 'SUM(ms.value) AS total, st.team_id ';
		$statistic_id = $this->id;
		$query = SMStatistic::getTeamsRanking($project_id, $limit, $limitstart, $order, $select, $statistic_id);
		$query->order('total ' . (!empty($order) ? $order : $this->getParam('ranking_order', 'DESC')) . ', tp.id ');
		$query->group('st.team_id');

		try
		{
			 $db->setQuery($query, $limitstart, $limit);
			 $res = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$msg = $e->getMessage(); // Returns "Normally you would have other code...
			$code = $e->getCode(); // Returns '500';
			Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error'); // commonly to still display that error
		}

		if ($res)
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

				$res[$k]->total = $this->formatValue($res[$k]->total, $precision);
			}
		}

			return $res;
	}

	function getMatchStaffStat(&$gamemodel, $team_staff_id)
	{
		$gamestats = $gamemodel->getMatchStaffStats();
		$stat_ids = SMStatistic::getSids($this->_ids);

			  $res = 0;

		foreach ($stat_ids as $id)
		{
			if (isset($gamestats[$team_staff_id][$id]))
			{
				$res += $gamestats[$team_staff_id][$id];
			}
		}

		return $this->formatValue($res, $this->getPrecision());
	}

	/**
	 * SMStatisticSumstats::getStaffStats()
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
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
		$db = sportsmanagementHelper::getDBConnection();

			  $select = 'ms.value, ms.statistic_id ';
		$query = SMStatistic::getStaffStatsQuery($person_id, $team_id, $project_id, $sqids, $select, false);

		$db->setQuery($query);

			  $stats = $db->loadObjectList();

			  $res = 0;

		foreach ($stats as $stat)
		{
			$key = array_search($stat->statistic_id, $sids);

			if ($key !== false)
			{
				$res += $factors[$key] * $stat->value;
			}
		}

			  return self::formatValue($res, SMStatistic::getPrecision());
	}

	/**
	 * SMStatisticSumstats::getHistoryStaffStats()
	 *
	 * @param   mixed $person_id
	 * @return
	 */
	function getHistoryStaffStats($person_id)
	{
		$sids = SMStatistic::getSids($this->_ids);
		$sqids = SMStatistic::getQuotedSids($this->_ids);
		$factors  = self::getFactors();
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
		$db = sportsmanagementHelper::getDBConnection();

			  $query = SMStatistic::getStaffStatsQuery($person_id, 0, 0, $sqids, $select, true);

		$db->setQuery($query);

			  $stats = $db->loadObjectList();

			  $res = 0;

		foreach ($stats as $stat)
		{
			$key = array_search($stat->statistic_id, $sids);

			if ($key !== false)
			{
				$res += $factors[$key] * $stat->value;
			}
		}

			  return self::formatValue($res, SMStatistic::getPrecision());
	}

	/**
	 * SMStatisticSumstats::formatValue()
	 *
	 * @param   mixed $value
	 * @param   mixed $precision
	 * @return
	 */
	function formatValue($value, $precision)
	{
		if (empty($value))
		{
			$value = 0;
		}

		return number_format($value, $precision);
	}
}
