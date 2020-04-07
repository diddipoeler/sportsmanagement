<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       percentage.php
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
 * SMStatisticPercentage
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class SMStatisticPercentage extends SMStatistic
{
	/**
	 * also the name of the associated xml file
	 */
	var $_name = 'percentage';

	var $_calculated = 1;

	var $_showinsinglematchreports = 1;

	var $_percentageSymbol = null;

	/**
	 * SMStatisticPercentage::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * SMStatisticPercentage::getSids()
	 *
	 * @return
	 */
	function getSids($id_field = 'numerator_ids')
	{
		  $app = Factory::getApplication();
		$params = SMStatistic::getParams();

			  // $numerator_ids = explode(',', $params->get('numerator_ids'));
		$numerator_ids = $params->get('numerator_ids');

		if (!count($numerator_ids))
		{
			Log::add(Text::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id), Log::WARNING, 'jsmerror');

			return(array(0));
		}

		// $denominator_ids = explode(',', $params->get('denominator_ids'));
		$denominator_ids = $params->get('denominator_ids');

		if (!count($denominator_ids))
		{
			Log::add(Text::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id), Log::WARNING, 'jsmerror');

			return(array(0));
		}

					  $db = sportsmanagementHelper::getDBConnection();
		$ids = array('num' => array(), 'den' => array());

		foreach ($numerator_ids as $s)
		{
			$ids['num'][] = (int) $s;
		}

		foreach ($denominator_ids as $s)
		{
			$ids['den'][] = (int) $s;
		}

			  return $ids;
	}


	/**
	 * SMStatisticPercentage::getQuotedSids()
	 *
	 * @param   string $id_field
	 * @return
	 */
	function getQuotedSids($id_field = 'numerator_ids')
	{
		$app = Factory::getApplication();
		$params = SMStatistic::getParams();

		$numerator_ids = $params->get('numerator_ids');

		if (!count($numerator_ids))
		{
			Log::add(Text::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id), Log::WARNING, 'jsmerror');

			return(array(0));
		}

		$denominator_ids = $params->get('denominator_ids');

		if (!count($denominator_ids))
		{
			Log::add(Text::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id), Log::WARNING, 'jsmerror');

			return(array(0));
		}

					  $db = sportsmanagementHelper::getDBConnection();
		$ids = array('num' => array(), 'den' => array());

		foreach ($numerator_ids as $s)
		{
			$ids['num'][] = $db->Quote((int) $s);
		}

		foreach ($denominator_ids as $s)
		{
			$ids['den'][] = $db->Quote((int) $s);
		}

			 return $ids;
	}

	/**
	 * SMStatisticPercentage::getMatchPlayerStat()
	 *
	 * @param   mixed $gamemodel
	 * @param   mixed $teamplayer_id
	 * @return
	 */
	function getMatchPlayerStat(&$gamemodel, $teamplayer_id)
	{
		$gamestats = $gamemodel->getPlayersStats();
		$ids = self::getSids();

			  $num = 0;

		foreach ($ids['num'] as $id)
		{
			if (isset($gamestats[$teamplayer_id][$id]))
			{
				$num += $gamestats[$teamplayer_id][$id];
			}
		}

		$den = 0;

		foreach ($ids['den'] as $id)
		{
			if (isset($gamestats[$teamplayer_id][$id]))
			{
				$den += $gamestats[$teamplayer_id][$id];
			}
		}

		return $this->formatValue($num, $den, $this->getPrecision(), $this->getShowPercentageSymbol());
	}

	/**
	 * SMStatisticPercentage::getPlayerStatsByGame()
	 *
	 * @param   mixed $teamplayer_ids
	 * @param   mixed $project_id
	 * @return
	 */
	function getPlayerStatsByGame($teamplayer_ids, $project_id)
	{
		$sids = self::getSids();
		$num = $this->getPlayerStatsByGameForIds($teamplayer_ids, $project_id, $sids['num']);
		$den = $this->getPlayerStatsByGameForIds($teamplayer_ids, $project_id, $sids['den']);
		$precision = $this->getPrecision();
		$showPercentageSymbol = $this->getShowPercentageSymbol();

		$res = array();

		foreach (array_unique(array_merge(array_keys($num), array_keys($den))) as $match_id)
		{
			$res[$match_id] = new stdclass;
			$res[$match_id]->match_id = $match_id;
			$n = isset($num[$match_id]->value) ? $num[$match_id]->value : 0;
			$d = isset($den[$match_id]->value) ? $den[$match_id]->value : 0;
			$res[$match_id]->value = $this->formatValue($n, $d, $precision, $showPercentageSymbol);
		}

		return $res;
	}

	/**
	 * SMStatisticPercentage::getPlayerStatsByProject()
	 *
	 * @param   mixed   $person_id
	 * @param   integer $projectteam_id
	 * @param   integer $project_id
	 * @param   integer $sports_type_id
	 * @return
	 */
	function getPlayerStatsByProject($person_id, $projectteam_id = 0, $project_id = 0, $sports_type_id = 0)
	{
		$sids = self::getSids();

		$num = $this->getPlayerStatsByProjectForIds($person_id, $projectteam_id, $project_id, $sports_type_id, $sids['num']);
		$den = $this->getPlayerStatsByProjectForIds($person_id, $projectteam_id, $project_id, $sports_type_id, $sids['den']);

		return $this->formatValue($num, $den, $this->getPrecision(), $this->getShowPercentageSymbol());
	}


	/**
	 * SMStatisticPercentage::getRosterStats()
	 *
	 * @param   mixed $team_id
	 * @param   mixed $project_id
	 * @param   mixed $position_id
	 * @return
	 */
	function getRosterStats($team_id, $project_id, $position_id)
	{
		$sids = self::getSids();
		$num = SMStatistic::getRosterStatsForIds($team_id, $project_id, $position_id, $sids['num']);
		$den = SMStatistic::getRosterStatsForIds($team_id, $project_id, $position_id, $sids['den']);
		$precision = SMStatistic::getPrecision();
		$showPercentageSymbol = self::getShowPercentageSymbol();

		$res = array();

		foreach (array_unique(array_merge(array_keys($num), array_keys($den))) as $person_id)
		{
			$res[$person_id] = new stdclass;
			$res[$person_id]->person_id = $person_id;
			$n = isset($num[$person_id]->value) ? $num[$person_id]->value : 0;
			$d = isset($den[$person_id]->value) ? $den[$person_id]->value : 0;
			$res[$person_id]->value = self::formatValue($n, $d, $precision, $showPercentageSymbol);
		}

		return $res;
	}

	/**
	 * SMStatisticPercentage::getPlayersRanking()
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
		$sids = self::getQuotedSids();

			  $option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
		$db = sportsmanagementHelper::getDBConnection();

			  $query_num = Factory::getDbo()->getQuery(true);
		$query_den = Factory::getDbo()->getQuery(true);
		$query_core = Factory::getDbo()->getQuery(true);

			  $query_num->select('SUM(ms.value) AS num, tp.id AS tpid, tp.person_id');
		$query_num->from('#__sportsmanagement_season_team_person_id AS tp');
		$query_num->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
		$query_num->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$query_num->join('INNER', '#__sportsmanagement_match_statistic AS ms ON ms.teamplayer_id = tp.id AND ms.statistic_id IN (' . implode(',', $sids['num']) . ')');
		$query_num->join('INNER', '#__sportsmanagement_match AS m ON m.id = ms.match_id AND m.published = 1 ');
		$query_num->where('pt.project_id = ' . $project_id);

		if ($division_id != 0)
		{
			$query_num->where('pt.division_id = ' . $division_id);
		}

		if ($team_id != 0)
		{
			$query_num->where('st.team_id = ' . $team_id);
		}

		$query_num->group('tp.id');

			  $query_den->select('SUM(ms.value) AS den, tp.id AS tpid, tp.person_id');
		$query_den->from('#__sportsmanagement_season_team_person_id AS tp');
		$query_den->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
		$query_den->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
		$query_den->join('INNER', '#__sportsmanagement_match_statistic AS ms ON ms.teamplayer_id = tp.id AND ms.statistic_id IN (' . implode(',', $sids['den']) . ')');
		$query_den->join('INNER', '#__sportsmanagement_match AS m ON m.id = ms.match_id AND m.published = 1 ');
		$query_den->where('pt.project_id = ' . $project_id);

		if ($division_id != 0)
		{
			$query_den->where('pt.division_id = ' . $division_id);
		}

		if ($team_id != 0)
		{
					$query_den->where('st.team_id = ' . $team_id);
		}

			  $query_den->where('ms.value > 0');
			$query_den->group('tp.id');

			  $query_select_count = 'COUNT(DISTINCT tp.id) as count';
			$query_select_details    = '(n.num / d.den) AS total, 1 as rank,'
								. ' tp.id AS teamplayer_id, tp.person_id, tp.picture AS teamplayerpic,'
								. ' p.firstname, p.nickname, p.lastname, p.picture, p.country,'
								. ' st.team_id, pt.picture AS projectteam_picture,'
								. ' t.picture AS team_picture, t.name AS team_name, t.short_name AS team_short_name';

									  $query_core->select($query_select_count);
			$query_core->from('#__sportsmanagement_season_team_person_id AS tp');
			$query_core->join('INNER', '(' . $query_num . ') AS n ON n.tpid = tp.id');
			$query_core->join('INNER', '(' . $query_den . ') AS d ON d.tpid = tp.id');
			$query_core->join('INNER', '#__sportsmanagement_person AS p ON p.id = tp.person_id ');
			$query_core->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
			$query_core->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
			$query_core->join('INNER', '#__sportsmanagement_team AS t ON st.team_id = t.id');
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
			$query_core->order('total ' . (!empty($order) ? $order : $this->getParam('ranking_order', 'DESC')) . ' ');

			$db->setQuery($query_core, $limitstart, $limit);

			  $res->ranking = $db->loadObjectList();

		if ($res->ranking)
		{
			$precision = $this->getPrecision();
			$showPercentageSymbol = $this->getShowPercentageSymbol();

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

					$res->ranking[$k]->total = $this->formatValue($res->ranking[$k]->total, 1, $precision, $showPercentageSymbol);
			}
		}

			return $res;
	}

		  /**
		   * SMStatisticPercentage::getTeamsRanking()
		   *
		   * @param   mixed   $project_id
		   * @param   integer $limit
		   * @param   integer $limitstart
		   * @param   mixed   $order
		   * @return
		   */
	function getTeamsRanking($project_id, $limit = 20, $limitstart = 0, $order=null)
	{
		$sids = self::getQuotedSids();
		$app = Factory::getApplication();
		$db = sportsmanagementHelper::getDBConnection();

			  $query_num = SMStatistic::getTeamsRankingStatisticNumQuery($project_id, $sids['num']);
		$query_den = SMStatistic::getTeamsRankingStatisticDenQuery($project_id, $sids['den']);
		$query = SMStatistic::getTeamsRankingStatisticCoreQuery($project_id, $query_num, $query_den);

			  $query->group((!empty($order) ? $order : $this->getParam('ranking_order', 'DESC')) . ' ');

			  $db->setQuery($query, $limitstart, $limit);

		try
		{
			$res = $db->loadObjectList();
		}
		catch (Exception $e)
		{
					$msg = $e->getMessage(); // Returns "Normally you would have other code...
					$code = $e->getCode(); // Returns '500';
					Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' ' . $msg, 'error'); // commonly to still display that error
		}

		if (!empty($res))
		{
			$precision = $this->getPrecision();
			$showPercentageSymbol = self::getShowPercentageSymbol();

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

					$res[$k]->total = $this->formatValue($res[$k]->total, 1, $precision, $showPercentageSymbol);
			}
		}

			return $res;
	}

	/**
	 * SMStatisticPercentage::getMatchStaffStat()
	 *
	 * @param   mixed $gamemodel
	 * @param   mixed $team_staff_id
	 * @return
	 */
	function getMatchStaffStat(&$gamemodel, $team_staff_id)
	{
		$gamestats = $gamemodel->getMatchStaffStats();
		$ids = self::getSids();

			  $num = 0;

		foreach ($ids['num'] as $id)
		{
			if (isset($gamestats[$team_staff_id][$id]))
			{
				$num += $gamestats[$team_staff_id][$id];
			}
		}

		$den = 0;

		foreach ($ids['den'] as $id)
		{
			if (isset($gamestats[$team_staff_id][$id]))
			{
				$den += $gamestats[$team_staff_id][$id];
			}
		}

		return $this->formatValue($num, $den, $this->getPrecision(), $this->getShowPercentageSymbol());
	}

	/**
	 * SMStatisticPercentage::getStaffStats()
	 *
	 * @param   mixed $person_id
	 * @param   mixed $team_id
	 * @param   mixed $project_id
	 * @return
	 */
	function getStaffStats($person_id, $team_id, $project_id)
	{
		$sids = $this->getQuotedSids();

			  $db = sportsmanagementHelper::getDBConnection();
		$select = 'SUM(ms.value) AS value, tp.person_id';
		$query = SMStatistic::getStaffStatsQuery($person_id, $team_id, $project_id, $sids['num'], $select, false);

			  $db->setQuery($query);
		$num = $db->loadResult();

			  $select = 'SUM(ms.value) AS value, tp.person_id';
		$query = SMStatistic::getStaffStatsQuery($person_id, $team_id, $project_id, $sids['den'], $select, false);

		$db->setQuery($query);
		$den = $db->loadResult();

		return $this->formatValue($num, $den, $this->getPrecision(), $this->getShowPercentageSymbol());
	}


	/**
	 * SMStatisticPercentage::getHistoryStaffStats()
	 *
	 * @param   mixed $person_id
	 * @return
	 */
	function getHistoryStaffStats($person_id)
	{
		$sids = $this->getQuotedSids();

			  $db = sportsmanagementHelper::getDBConnection();
		$select = 'SUM(ms.value) AS value, tp.person_id';
		$query = SMStatistic::getStaffStatsQuery($person_id, 0, 0, $sids['num'], $select, true);

			  $db->setQuery($query);
		$num = $db->loadResult();

			  $select = 'SUM(ms.value) AS value, tp.person_id';
		$query = SMStatistic::getStaffStatsQuery($person_id, 0, 0, $sids['den'], $select, true);

			  $db->setQuery($query);
		$den = $db->loadResult();

		return self::formatValue($num, $den, $this->getPrecision(), $this->getShowPercentageSymbol());
	}

	/**
	 * SMStatisticPercentage::getShowPercentageSymbol()
	 *
	 * @return
	 */
	function getShowPercentageSymbol()
	{
		$params = SMStatistic::getParams();

		return $params->get('show_percent_symbol', 1);
	}

	/**
	 * SMStatisticPercentage::formatValue()
	 *
	 * @param   mixed $num
	 * @param   mixed $den
	 * @param   mixed $precision
	 * @param   mixed $showPercentageSymbol
	 * @return
	 */
	function formatValue($num, $den, $precision, $showPercentageSymbol)
	{
		$value = (!empty($num) && !empty($den)) ? $num / $den : 0;

		if ($showPercentageSymbol)
		{
			$formattedValue = number_format(100 * $value, $precision) . "%";
		}
		else
		{
			$formattedValue = number_format($value, $precision);
		}

		return $formattedValue;
	}

	/**
	 * SMStatisticPercentage::formatZeroValue()
	 *
	 * @return
	 */
	function formatZeroValue()
	{
		return self::formatValue(0, 0, $this->getPrecision(), $this->getShowPercentageSymbol());
	}
}
