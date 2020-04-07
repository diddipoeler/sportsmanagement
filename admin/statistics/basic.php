<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage statistics
 * @file       basic.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

JLoader::import('components.com_sportsmanagement.statistics.base', JPATH_ADMINISTRATOR);

/**
 * SMStatisticBasic
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class SMStatisticBasic extends SMStatistic
{
	/**
	 * also the name of the associated xml file
	 */
	var $_name = 'basic';

	/**
	 * SMStatisticBasic::getMatchPlayerStat()
	 *
	 * @param   mixed $gamemodel
	 * @param   mixed $teamplayer_id
	 * @return
	 */
	function getMatchPlayerStat(&$gamemodel, $teamplayer_id)
	{
		$gamestats = $gamemodel->getPlayersStats();
		$res = 0;

		if (isset($gamestats[$teamplayer_id][$this->id]))
		{
			$res = $gamestats[$teamplayer_id][$this->id];
		}

		return self::formatValue($res, SMStatistic::getPrecision());
	}

	/**
	 * SMStatisticBasic::getMatchPlayersStats()
	 *
	 * @param   mixed $match_id
	 * @return
	 */
	function getMatchPlayersStats($match_id)
	{
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
		$query->select('SUM(ms.value) AS value, tp.id');
		$query->from('#__sportsmanagement_match_statistic AS ms');
		$query->join('INNER', '#__sportsmanagement_match AS m ON m.id = ms.match_id AND m.published = 1');
		$query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON tp.id = ms.teamplayer_id ');
		$query->where('ms.statistic_id = ' . $this->id);
		$query->where('ms.match_id = ' . $match_id);
		$db->setQuery($query);
		$res = $db->loadObjectList('id');

		if (!empty($res))
		{
			$precision = SMStatistic::getPrecision();

			foreach ($res as $player)
			{
				$player->value = self::formatValue($player->value, $precision);
			}
		}

		return $res;
	}

	/**
	 * SMStatisticBasic::getPlayerStatsByGame()
	 *
	 * @param   mixed $teamplayer_ids
	 * @param   mixed $project_id
	 * @return
	 */
	function getPlayerStatsByGame($teamplayer_ids, $project_id)
	{
		$res = SMStatistic::getPlayerStatsByGameForIds($teamplayer_ids, $project_id, array($this->id));

		if (!empty($res))
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
	 * SMStatisticBasic::getPlayerStatsByProject()
	 *
	 * @param   mixed   $person_id
	 * @param   integer $projectteam_id
	 * @param   integer $project_id
	 * @param   integer $sports_type_id
	 * @return
	 */
	function getPlayerStatsByProject($person_id, $projectteam_id = 0, $project_id = 0, $sports_type_id = 0)
	{
		$res = SMStatistic::getPlayerStatsByProjectForIds($person_id, $projectteam_id, $project_id, $sports_type_id, array($this->id));

		return self::formatValue($res, $this->getPrecision());
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
		$res = SMStatistic::GetRosterStatsForIds($team_id, $project_id, $position_id, array($this->id));

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
	 * SMStatisticBasic::getPlayersRanking()
	 *
	 * @param   integer $project_id
	 * @param   integer $division_id
	 * @param   integer $team_id
	 * @param   integer $limit
	 * @param   integer $limitstart
	 * @param   mixed   $order
	 * @return
	 */
	function getPlayersRanking($project_id = 0, $division_id = 0, $team_id = 0, $limit = 20, $limitstart = 0, $order = null)
	{
		$app = Factory::getApplication();
		$db = sportsmanagementHelper::getDBConnection();
		$query_core = Factory::getDbo()->getQuery(true);

			  $query_select_count = ' COUNT(DISTINCT tp.id) as count';

		$query_select_details = ' SUM(ms.value) AS total,'
		. ' tp.id AS teamplayer_id, tp.person_id, tp.picture AS teamplayerpic,'
		. ' p.firstname, p.nickname, p.lastname, p.picture, p.country,'
		. ' st.team_id, pt.picture AS projectteam_picture,'
		. ' t.picture AS team_picture, t.name AS team_name, t.short_name AS team_short_name';

			  $query_core->select($query_select_count);
		$query_core->from('#__sportsmanagement_season_team_person_id AS tp');
		$query_core->join('INNER', '#__sportsmanagement_person AS p ON p.id = tp.person_id ');
		$query_core->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
		$query_core->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$query_core->join('INNER', '#__sportsmanagement_team AS t ON st.team_id = t.id');
		$query_core->join('INNER', '#__sportsmanagement_match_statistic AS ms ON ms.teamplayer_id = tp.id AND ms.statistic_id = ' . $db->Quote($this->id));
		$query_core->join('INNER', '#__sportsmanagement_match AS m ON m.id = ms.match_id AND m.published = 1');
		$query_core->where('pt.project_id = ' . $project_id);
		$query_core->where('p.published = 1');

		if ($division_id != 0)
		{
			$query_core->where('pt.division_id = ' . $division_id);
		}

		if ($team_id != 0)
		{
					$query_core->where('st.team_id = ' . $team_id);
		}

			$res = new stdclass;
			$db->setQuery($query_core);

			 $res->pagination_total = $db->loadResult();

			  $query_core->clear('select');
			$query_core->select($query_select_details);
			$query_core->group('tp.id');
			$query_core->order('total ' . (!empty($order) ? $order : $this->getParam('ranking_order', 'DESC')) . ', tp.id ');

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
	 * SMStatisticBasic::getTeamsRanking()
	 *
	 * @param   integer $project_id
	 * @param   integer $limit
	 * @param   integer $limitstart
	 * @param   mixed   $order
	 * @param   string  $select
	 * @param   integer $statistic_id
	 * @return
	 */
	function getTeamsRanking($project_id = 0, $limit = 20, $limitstart = 0, $order = null, $select = '', $statistic_id = 0)
	{
		$app = Factory::getApplication();
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
			$precision = SMStatistic::getPrecision();

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
				$res[$k]->total = self::formatValue($res[$k]->total, $precision);
			}
		}

		return $res;
	}

	/**
	 * SMStatisticBasic::getMatchStaffStat()
	 *
	 * @param   mixed $gamemodel
	 * @param   mixed $team_staff_id
	 * @return
	 */
	function getMatchStaffStat(&$gamemodel, $team_staff_id)
	{
		$gamestats = $gamemodel->getMatchStaffStats();
		$res = 0;

		if (isset($gamestats[$team_staff_id][$this->id]))
		{
			$res = $gamestats[$team_staff_id][$this->id];
		}

		return self::formatValue($res, $this->getPrecision());
	}

	/**
	 * SMStatisticBasic::getStaffStats()
	 *
	 * @param   mixed $person_id
	 * @param   mixed $team_id
	 * @param   mixed $project_id
	 * @return
	 */
	function getStaffStats($person_id, $team_id, $project_id)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
		$db = sportsmanagementHelper::getDBConnection();
		$select = 'SUM(ms.value) AS value ';
		$query = SMStatistic::getStaffStatsQuery($person_id, $team_id, $project_id, $this->id, $select, false);

					$db->setQuery($query, 0, 1);

			 $res = $db->loadResult();

		return self::formatValue($res, $this->getPrecision());
	}

	/**
	 * SMStatisticBasic::getHistoryStaffStats()
	 *
	 * @param   mixed $person_id
	 * @return
	 */
	function getHistoryStaffStats($person_id)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
		$db = sportsmanagementHelper::getDBConnection();

			  $select = 'SUM(ms.value) AS value ';
		$query = SMStatistic::getStaffStatsQuery($person_id, 0, 0, $this->id, $select, true);

			  $db->setQuery($query);

			 $res = $db->loadResult();

		return self::formatValue($res, SMStatistic::getPrecision());
	}

	/**
	 * SMStatisticBasic::formatValue()
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
