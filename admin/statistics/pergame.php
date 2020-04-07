<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage statistics
 * @file       pergame.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

JLoader::import('components.com_sportsmanagement.statistics.base', JPATH_ADMINISTRATOR);

/**
 * SMStatisticPergame
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class SMStatisticPergame extends SMStatistic
{
	/**
	 * also the name of the associated xml file
	 */
	var $_name = 'pergame';

	var $_calculated = 1;

	var $_showinsinglematchreports = 0;

	var $_ids = 'numerator_ids';

	/**
	 * SMStatisticPergame::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();
	}


	/**
	 * SMStatisticPergame::getPlayerStatsByProject()
	 *
	 * @param   integer $person_id
	 * @param   integer $projectteam_id
	 * @param   integer $project_id
	 * @param   integer $sports_type_id
	 * @return
	 */
	function getPlayerStatsByProject($person_id = 0, $projectteam_id = 0, $project_id = 0, $sports_type_id = 0)
	{
		$sids = SMStatistic::getSids($this->_ids);

			  $num = $this->getPlayerStatsByProjectForIds($person_id, $projectteam_id, $project_id, $sports_type_id, $sids);
		$den = $this->getGamesPlayedByPlayer($person_id, $projectteam_id, $project_id, $sports_type_id);

		return self::formatValue($num, $den, $this->getPrecision());
	}


	/**
	 * SMStatisticPergame::getRosterStats()
	 *
	 * @param   integer $team_id
	 * @param   integer $project_id
	 * @param   integer $position_id
	 * @return
	 */
	function getRosterStats($team_id = 0, $project_id = 0, $position_id = 0)
	{
		$sids = SMStatistic::getSids($this->_ids);
		$num = $this->getRosterStatsForIds($team_id, $project_id, $position_id, $sids);
		$den = $this->getGamesPlayedByProjectTeam($team_id, $project_id, $position_id);
		$precision = $this->getPrecision();

			  $res = array();

		foreach (array_unique(array_merge(array_keys($num), array_keys($den))) as $person_id)
		{
			$n = isset($num[$person_id]) ? $num[$person_id]->value : 0;
			$d = isset($den[$person_id]) ? $den[$person_id]->value : 0;
			$res[$person_id] = new stdclass;
			$res[$person_id]->person_id = $person_id;
			$res[$person_id]->value = $this->formatValue($n, $d, $precision);
		}

		return $res;
	}


	/**
	 * SMStatisticPergame::getPlayersRanking()
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
		$sids = SMStatistic::getQuotedSids($this->_ids);

			  $option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
		$db = sportsmanagementHelper::getDBConnection();

		$query_num = SMStatistic::getPlayersRankingStatisticNumQuery($project_id, $division_id, $team_id, $sids);
		$query_den = SMStatistic::getGamesPlayedQuery($project_id, $division_id, $team_id);
		$select = '';
		$query_select_details = '(n.num / d.played) AS total, n.person_id, 1 as rank,'
							  . ' tp.id AS teamplayer_id, tp.person_id, tp.picture AS teamplayerpic,'
							  . ' p.firstname, p.nickname, p.lastname, p.picture, p.country,'
							  . ' st.team_id, pt.picture AS projectteam_picture,'
							  . ' t.picture AS team_picture, t.name AS team_name, t.short_name AS team_short_name';

									$res = new stdclass;
		$query_core = SMStatistic::getPlayersRankingStatisticCoreQuery($project_id, $division_id, $team_id, $query_num, $query_den, $select);

		try
		{
			 $db->setQuery($query_core);
			 $res->pagination_total = $db->loadResult();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), 'error');
		}

			  $query_core->clear('select');
		$query_core->select($query_select_details);
		$query_core->group('tp.id');
		$query_core->order('total ' . (!empty($order) ? $order : SMStatistic::getParam('ranking_order', 'DESC')) . ' ');

		try
		{
			 $db->setQuery($query_core, $limitstart, $limit);
			 $res->ranking = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), 'error');
		}

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

				$res->ranking[$k]->total = self::formatValue($res->ranking[$k]->total, 1, $precision);
			}
		}

		return $res;
	}

	/**
	 * SMStatisticPergame::getTeamsRanking()
	 *
	 * @param   integer $project_id
	 * @param   integer $limit
	 * @param   integer $limitstart
	 * @param   mixed   $order
	 * @return
	 */
	function getTeamsRanking($project_id = 0, $limit = 20, $limitstart = 0, $order = null, $select = '', $statistic_id = 0)
	{
		$app = Factory::getApplication();
		$sids = SMStatistic::getQuotedSids($this->_ids);

			  $db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
		$query_den = $db->getQuery(true);

			  $query_num = SMStatistic::getTeamsRankingStatisticNumQuery($project_id, $sids);

		$query_den->select('COUNT(m.id) AS value, pt.id');
		$query_den->from('#__sportsmanagement_project_team AS pt');
		$query_den->join('INNER', '#__sportsmanagement_match AS m ON m.projectteam1_id = pt.id OR m.projectteam2_id = pt.id ');
		$query_den->where('pt.project_id = ' . $project_id);
		$query_den->where('m.published = 1');
		$query_den->where('m.team1_result IS NOT NULL');
		$query_den->group('pt.id');

			  $query->select('(n.num / d.value) AS total, st.team_id');
		$query->from('#__sportsmanagement_project_team AS pt');
		$query->join('INNER', '#__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
		$query->join('INNER', '#__sportsmanagement_team AS t ON st.team_id = t.id ');
		$query->join('INNER', '(' . $query_num . ') AS n ON n.id = pt.id ');
		$query->join('INNER', '(' . $query_den . ') AS d ON d.id = pt.id ');
		$query->where('pt.project_id = ' . $projectid);
		$query->order('total ' . (!empty($order) ? $order : $this->getParam('ranking_order', 'DESC')) . ' ');

		try
		{
			 $db->setQuery($query, $limitstart, $limit);
			 $res = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), 'error');
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
	 * SMStatisticPergame::getStaffStats()
	 *
	 * @param   integer $person_id
	 * @param   integer $team_id
	 * @param   integer $project_id
	 * @return
	 */
	function getStaffStats($person_id = 0, $team_id = 0, $project_id = 0)
	{
		$sids = SMStatistic::getQuotedSids($this->_ids);

			  $db = sportsmanagementHelper::getDBConnection();
		$select = 'SUM(ms.value) AS value, tp.person_id';
		$query = SMStatistic::getStaffStatsQuery($person_id, $team_id, $project_id, $sids, $select, false);
		$db->setQuery($query);
		$num = $db->loadResult();

			  $select = 'SUM(ms.value) AS value, tp.person_id';
		$query = SMStatistic::getStaffStatsQuery($person_id, $team_id, $project_id, '', $select, false);

			  $db->setQuery($query);
		$den = $db->loadResult();

		return $this->formatValue($num, $den, $this->getPrecision());
	}


	/**
	 * SMStatisticPergame::getHistoryStaffStats()
	 *
	 * @param   integer $person_id
	 * @return
	 */
	function getHistoryStaffStats($person_id = 0)
	{
		$sids = SMStatistic::getQuotedSids($this->_ids);

			  $db = sportsmanagementHelper::getDBConnection();
		$select = 'SUM(ms.value) AS value, tp.person_id';
		$query = SMStatistic::getStaffStatsQuery($person_id, 0, 0, $sids, $select, true);

			  $db->setQuery($query);
		$num = $db->loadResult();

			  $select = 'COUNT(ms.id) AS value, tp.person_id';
		$query = SMStatistic::getStaffStatsQuery($person_id, 0, 0, '', $select, true, 'match_staff');

			  $db->setQuery($query);
		$den = $db->loadResult();

		return $this->formatValue($num, $den, $this->getPrecision());
	}

	/**
	 * SMStatisticPergame::formatValue()
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
