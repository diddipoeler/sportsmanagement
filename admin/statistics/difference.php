<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage statistics
 * @file       difference.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

JLoader::import('components.com_sportsmanagement.statistics.base', JPATH_ADMINISTRATOR);

/**
 * SMStatisticDifference
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class SMStatisticDifference extends SMStatistic
{
	// Also the name of the associated xml file
	var $_name = 'difference';

	var $_calculated = 1;

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * SMStatisticDifference::getMatchPlayerStat()
	 *
	 * @param   mixed  $gamemodel
	 * @param   mixed  $teamplayer_id
	 *
	 * @return
	 */
	function getMatchPlayerStat(&$gamemodel, $teamplayer_id)
	{
		$gamestats = $gamemodel->getPlayersStats();
		$ids       = $this->getSids();

		$add = 0;

		foreach ($ids['add'] as $id)
		{
			if (isset($gamestats[$teamplayer_id][$id]))
			{
				$add += $gamestats[$teamplayer_id][$id];
			}
		}

		$sub = 0;

		foreach ($ids['sub'] as $id)
		{
			if (isset($gamestats[$teamplayer_id][$id]))
			{
				$sub += $gamestats[$teamplayer_id][$id];
			}
		}

		return $this->formatValue($add, $sub, $this->getPrecision());
	}

	/**
	 * SMStatisticDifference::getSids()
	 *
	 * @param   string  $id_field
	 *
	 * @return
	 */
	function getSids($id_field = '')
	{
		$params = SMStatistic::getParams();
		$app    = Factory::getApplication();

		// $add_ids = explode(',', $params->get('add_ids'));
		$add_ids = $params->get('add_ids');

		if (!count($add_ids))
		{
			Log::add(Text::sprintf('STAT %s/%s WRONG CONFIGURATION ADD_IDS', $this->_name, $this->id), Log::WARNING, 'jsmerror');

			return (array(0));
		}

		$sub_ids = $params->get('sub_ids');

		if (!count($sub_ids))
		{
			Log::add(Text::sprintf('STAT %s/%s WRONG CONFIGURATION SUB_IDS', $this->_name, $this->id), Log::ERROR, 'jsmerror');

			return (array(0));
		}

		$ids = array('add' => $add_ids, 'sub' => $sub_ids);

		return $ids;
	}

	/**
	 * SMStatisticDifference::formatValue()
	 *
	 * @param   mixed  $add
	 * @param   mixed  $sub
	 * @param   mixed  $precision
	 *
	 * @return
	 */
	function formatValue($add, $sub, $precision)
	{
		$value = (!empty($add) ? $add : 0) - (!empty($sub) ? $sub : 0);

		return number_format($value, $precision);
	}

	/**
	 * SMStatisticDifference::getPlayerStatsByGame()
	 *
	 * @param   mixed  $teamplayer_ids
	 * @param   mixed  $project_id
	 *
	 * @return
	 */
	function getPlayerStatsByGame($teamplayer_ids, $project_id)
	{
		$sids      = $this->getSids();
		$add       = $this->getPlayerStatsByGameForIds($teamplayer_ids, $project_id, $sids['add']);
		$sub       = $this->getPlayerStatsByGameForIds($teamplayer_ids, $project_id, $sids['sub']);
		$precision = $this->getPrecision();

		$res = array();

		foreach (array_unique(array_merge(array_keys($add), array_keys($sub))) as $match_id)
		{
			$a                        = isset($add[$match_id]) ? $add[$match_id]->value : 0;
			$s                        = isset($sub[$match_id]) ? $sub[$match_id]->value : 0;
			$res[$match_id]           = new stdclass;
			$res[$match_id]->match_id = $match_id;
			$res[$match_id]->value    = $this->formatValue($a, $s, $precision);
		}

		return $res;
	}

	/**
	 * SMStatisticDifference::getPlayerStatsByProject()
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
		$sids = $this->getSids();
		$add  = $this->getPlayerStatsByProjectForIds($person_id, $projectteam_id, $project_id, $sports_type_id, $sids['add']);
		$sub  = $this->getPlayerStatsByProjectForIds($person_id, $projectteam_id, $project_id, $sports_type_id, $sids['sub']);

		return $this->formatValue($add, $sub, $this->getPrecision());
	}


	/**
	 * SMStatisticDifference::getRosterStats()
	 *
	 * @param   mixed  $team_id
	 * @param   mixed  $project_id
	 * @param   mixed  $position_id
	 *
	 * @return
	 */
	function getRosterStats($team_id, $project_id, $position_id)
	{
		$sids      = $this->getSids();
		$add       = $this->getRosterStatsForIds($team_id, $project_id, $position_id, $sids['add']);
		$sub       = $this->getRosterStatsForIds($team_id, $project_id, $position_id, $sids['sub']);
		$precision = $this->getPrecision();

		$res = array();

		foreach (array_unique(array_merge(array_keys($add), array_keys($sub))) as $person_id)
		{
			$a                          = isset($add[$person_id]) ? $add[$person_id]->value : 0;
			$s                          = isset($sub[$person_id]) ? $sub[$person_id]->value : 0;
			$res[$person_id]            = new stdclass;
			$res[$person_id]->person_id = $person_id;
			$res[$person_id]->value     = $this->formatValue($a, $s, $precision);
		}

		return $res;
	}

	/**
	 * SMStatisticDifference::getPlayersRanking()
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
		$sids = self::getQuotedSids('');
		$db   = sportsmanagementHelper::getDBConnection();
		$app  = Factory::getApplication();

		$query_add = SMStatistic::getPlayersRankingStatisticQuery($project_id, $division_id, $team_id, $sids['add'], 'SUM(ms.value) AS num, tp.id AS tpid, tp.person_id');
		$query_sub = SMStatistic::getPlayersRankingStatisticQuery($project_id, $division_id, $team_id, $sids['sub'], 'SUM(ms.value) AS den, tp.id AS tpid, tp.person_id');

		$select               = 'COUNT(DISTINCT tp.id) as count';
		$query_select_details = 'n.num - d.den AS total, n.person_id, 1 as rank,'
			. ' tp.id AS teamplayer_id, tp.person_id, tp.picture AS teamplayerpic,'
			. ' p.firstname, p.nickname, p.lastname, p.picture, p.country,'
			. ' st.team_id, pt.picture AS projectteam_picture, t.picture AS team_picture,'
			. ' t.name AS team_name, t.short_name AS team_short_name';

		$query_core = SMStatistic::getPlayersRankingStatisticCoreQuery($project_id, $division_id, $team_id, $query_add, $query_sub, $select);

		$res = new stdclass;
		$db->setQuery($query_core);

		$res->pagination_total = $db->loadResult();

		$query_core->clear('select');
		$query_core->select($query_select_details);
		$query_core->order('total ' . (!empty($order) ? $order : SMStatistic::getParam('ranking_order', 'DESC')) . ' ');

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

				$previousval             = $row->total;
				$currentrank             = $res->ranking[$k]->rank;
				$res->ranking[$k]->total = $this->formatValue($res->ranking[$k]->total, 0, $precision);
			}
		}

		return $res;
	}

	/**
	 * SMStatisticDifference::getQuotedSids()
	 *
	 * @param   string  $id_field
	 *
	 * @return
	 */
	function getQuotedSids($id_field = '')
	{
		$db  = sportsmanagementHelper::getDBConnection();
		$ids = self::getSids('');

		foreach ($ids['add'] as $k => $s)
		{
			$ids['add'][$k] = $db->Quote((int) $s);
		}

		foreach ($ids['sub'] as $k => $s)
		{
			$ids['sub'][$k] = $db->Quote((int) $s);
		}

		return $ids;
	}

	/**
	 * SMStatisticDifference::getTeamsRanking()
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
		$sids = $this->getQuotedSids();

		$db    = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		$query_add = SMStatistic::getPlayersRankingStatisticQuery($project_id, 0, 0, $sids['add'], 'SUM(ms.value) AS num, tp.person_id');
		$query_sub = SMStatistic::getPlayersRankingStatisticQuery($project_id, 0, 0, $sids['sub'], 'SUM(ms.value) AS den, tp.person_id');

		$select = 'SUM(n.num - d.den) AS total, pt.team_id';
		$query  = SMStatistic::getPlayersRankingStatisticCoreQuery($project_id, 0, 0, $query_add, $query_sub, $select);

		$query->group('pt.team_id');
		$query->order('total ' . (!empty($order) ? $order : SMStatistic::getParam('ranking_order', 'DESC')) . ' ');

		$db->setQuery($query, $limitstart, $limit);
		$res = $db->loadObjectList();

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

				$previousval    = $row->total;
				$currentrank    = $res[$k]->rank;
				$res[$k]->total = $this->formatValue($res[$k]->total, 0, $precision);
			}
		}

		return $res;
	}

	/**
	 * SMStatisticDifference::getMatchStaffStat()
	 *
	 * @param   mixed  $gamemodel
	 * @param   mixed  $team_staff_id
	 *
	 * @return
	 */
	function getMatchStaffStat(&$gamemodel, $team_staff_id)
	{
		$gamestats = $gamemodel->getMatchStaffStats();
		$ids       = $this->getSids();

		$add = 0;

		foreach ($ids['add'] as $id)
		{
			if (isset($gamestats[$team_staff_id][$id]))
			{
				$add += $gamestats[$team_staff_id][$id];
			}
		}

		$sub = 0;

		foreach ($ids['sub'] as $id)
		{
			if (isset($gamestats[$team_staff_id][$id]))
			{
				$sub += $gamestats[$team_staff_id][$id];
			}
		}

		return self::formatValue($add, $sub, SMStatistic::getPrecision());
	}

	/**
	 * SMStatisticDifference::getStaffStats()
	 *
	 * @param   mixed  $person_id
	 * @param   mixed  $team_id
	 * @param   mixed  $project_id
	 *
	 * @return
	 */
	function getStaffStats($person_id, $team_id, $project_id)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();
		$sids   = self::getQuotedSids();

		$db = sportsmanagementHelper::getDBConnection();

		$select = 'SUM(ms.value) AS value, tp.person_id ';
		$query  = SMStatistic::getStaffStatsQuery($person_id, $team_id, $project_id, implode(',', $sids['add']), $select, false);

		$db->setQuery($query);

		$add = $db->loadResult();
		$add = isset($add->value) ? $add->value : 0;

		$select = 'SUM(ms.value) AS value, tp.person_id ';
		$query  = SMStatistic::getStaffStatsQuery($person_id, $team_id, $project_id, implode(',', $sids['sub']), $select, false);

		$db->setQuery($query);

		$sub = $db->loadResult();
		$sub = isset($sub->value) ? $sub->value : 0;

		return self::formatValue($add, $sub, SMStatistic::getPrecision());
	}

	/**
	 * SMStatisticDifference::getHistoryStaffStats()
	 *
	 * @param   mixed  $person_id
	 *
	 * @return
	 */
	function getHistoryStaffStats($person_id)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();
		$sids   = self::getQuotedSids();

		$db     = sportsmanagementHelper::getDBConnection();
		$select = 'SUM(ms.value) AS value, tp.person_id ';
		$query  = SMStatistic::getStaffStatsQuery($person_id, 0, 0, implode(',', $sids['add']), $select, true);

		$db->setQuery($query);

		$add = $db->loadResult();
		$add = isset($add->value) ? $add->value : 0;

		$select = 'SUM(ms.value) AS value, tp.person_id ';
		$query  = SMStatistic::getStaffStatsQuery($person_id, 0, 0, implode(',', $sids['sub']), $select, true);

		$db->setQuery($query);

		$sub = $db->loadResult();
		$sub = isset($sub->value) ? $sub->value : 0;

		return self::formatValue($add, $sub, $this->getPrecision());
	}
}
