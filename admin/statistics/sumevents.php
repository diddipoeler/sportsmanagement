<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage statistics
 * @file       sumevents.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

JLoader::import('components.com_sportsmanagement.statistics.base', JPATH_ADMINISTRATOR);

/**
 * SMStatisticSumevents
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class SMStatisticSumevents extends SMStatistic
{
	/**
	 * also the name of the associated xml file
	 */
	var $_name = 'sumevents';

	var $_calculated = 1;

	var $_showinsinglematchreports = 1;

	var $_ids = 'stat_ids';

	/**
	 * SMStatisticSumevents::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();
	}


	/**
	 * SMStatisticSumevents::getMatchPlayerStat()
	 *
	 * @param   mixed  $gamemodel
	 * @param   mixed  $teamplayer_id
	 *
	 * @return
	 */
	function getMatchPlayerStat(&$gamemodel, $teamplayer_id)
	{
		$gamestats = $gamemodel->getPlayersEvents();
		$stat_ids  = SMStatistic::getSids($this->_ids);

		$res = 0;

		foreach ($stat_ids as $id)
		{
			if (isset($gamestats[$teamplayer_id][$id]))
			{
				$res += $gamestats[$teamplayer_id][$id];
			}
		}

		return self::formatValue($res, SMStatistic::getPrecision());
	}

	/**
	 * SMStatisticSumevents::formatValue()
	 *
	 * @param   mixed  $value
	 * @param   mixed  $precision
	 *
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

	/**
	 * SMStatisticSumevents::getPlayerStatsByGame()
	 *
	 * @param   mixed  $teamplayer_ids
	 * @param   mixed  $project_id
	 *
	 * @return
	 */
	function getPlayerStatsByGame($teamplayer_ids, $project_id)
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		$sids   = SMStatistic::getQuotedSids($this->_ids);
		$db     = sportsmanagementHelper::getDBConnection();
		$query  = $db->getQuery(true);

		$quoted_tpids = array();

		foreach ($teamplayer_ids as $tpid)
		{
			$quoted_tpids[] = $db->Quote($tpid);
		}

		$query->select('SUM(ms.event_sum) AS value, ms.match_id');
		$query->from('#__sportsmanagement_match_event AS ms ');
		$query->where('ms.teamplayer_id IN (' . implode(',', $quoted_tpids) . ')');
		$query->where('ms.event_type_id IN (' . implode(',', $sids) . ')');
		$query->group('ms.match_id');

		$db->setQuery($query);
		$res = $db->loadObjectList('match_id');

		// Determine total for the whole project
		$totals               = new stdclass;
		$totals->statistic_id = $this->id;
		$totals->value        = 0;

		if (!empty($res))
		{
			$precision = SMStatistic::getPrecision();

			foreach ($res as $k => $match)
			{
				$totals->value  += $match->value;
				$res[$k]->value = self::formatValue($res[$k]->value, $precision);
			}

			$totals->value = self::formatValue($totals->value, $precision);
		}

		$res['totals'] = $totals;

		return $res;
	}

	/**
	 * SMStatisticSumevents::getPlayerStatsByProject()
	 *
	 * @param   mixed    $person_id
	 * @param   integer  $projectteam_id
	 * @param   integer  $project_id
	 * @param   integer  $sports_type_id
	 *
	 * @return
	 */
	function getPlayerStatsByProject($person_id, $projectteam_id = 0, $project_id = 0, $sports_type_id = 0)
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');

		$sids = SMStatistic::getSids($this->_ids);
		$res  = SMStatistic::getPlayerStatsByProjectForEvents($person_id, $projectteam_id, $project_id, $sports_type_id, $sids);

		return self::formatValue($res, SMStatistic::getPrecision());
	}

	/**
	 * Get players stats
	 *
	 * @param  $team_id
	 * @param  $project_id
	 *
	 * @return array
	 */
	function getRosterStats($team_id, $project_id, $position_id)
	{
		$sids = SMStatistic::getSids($this->_ids);
		$res  = SMStatistic::getRosterStatsForEvents($team_id, $project_id, $position_id, $sids);

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
	 * SMStatisticSumevents::getPlayersRanking()
	 *
	 * @param   mixed    $project_id
	 * @param   mixed    $division_id
	 * @param   mixed    $team_id
	 * @param   integer  $limit
	 * @param   integer  $limitstart
	 * @param   mixed    $order
	 *
	 * @return
	 */
	function getPlayersRanking($project_id, $division_id, $team_id, $limit = 20, $limitstart = 0, $order = null)
	{
		$sids       = SMStatistic::getQuotedSids($this->_ids);
		$app        = Factory::getApplication();
		$db         = sportsmanagementHelper::getDBConnection();
		$query_core = Factory::getDbo()->getQuery(true);

		$query_select_count   = 'COUNT(DISTINCT tp.id) as count';
		$query_select_details = 'SUM(ms.event_sum) AS total,'
			. ' tp.id AS teamplayer_id, tp.person_id, tp.picture AS teamplayerpic,'
			. ' p.firstname, p.nickname, p.lastname, p.picture, p.country,'
			. ' st.team_id, pt.picture AS projectteam_picture,'
			. ' t.picture AS team_picture, t.name AS team_name, t.short_name AS team_short_name';

		$query_core = SMStatistic::getPlayersRankingStatisticQuery($project_id, $division_id, $team_id, $sids, $query_select_count, 'event');

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
			$precision = $this->getPrecision();

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
	 * SMStatisticSumevents::getTeamsRanking()
	 *
	 * @param   mixed    $project_id
	 * @param   integer  $limit
	 * @param   integer  $limitstart
	 * @param   mixed    $order
	 *
	 * @return
	 */
	function getTeamsRanking($project_id, $limit = 20, $limitstart = 0, $order = null)
	{
		$sids      = SMStatistic::getQuotedSids($this->_ids);
		$option    = Factory::getApplication()->input->getCmd('option');
		$app       = Factory::getApplication();
		$db        = sportsmanagementHelper::getDBConnection();
		$query_num = $db->getQuery(true);
		$query_num->select('SUM(es.event_sum) AS num, pt.team_id');
		$query_num->from('#__sportsmanagement_season_team_person_id AS tp');
		$query_num->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
		$query_num->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id ');
		$query_num->join('INNER', '#__sportsmanagement_match_event AS es ON es.teamplayer_id = tp.id AND es.event_type_id IN (' . implode(',', $sids) . ')');
		$query_num->join('INNER', '#__sportsmanagement_match AS m ON m.id = es.match_id AND m.published = 1 ');
		$query_num->where('pt.project_id = ' . $projectid);
		$query_num->where('tp.published = 1');
		$query_num->group('pt.id');
		$query_num->order('total ' . (!empty($order) ? $order : $this->getParam('ranking_order', 'DESC')) . ', tp.id');

		$db->setQuery($query_num, $limitstart, $limit);
		$res = $db->loadObjectList();

		if (!empty($res))
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
}
