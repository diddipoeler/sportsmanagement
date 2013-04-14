<?php
/**
 * @copyright	Copyright (C) 2006-2013 JoomLeague.net. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(JLG_PATH_ADMIN.DS.'statistics'.DS.'base.php');

/**
 * base class for statistics handling.
 *
 * @package Joomla
 * @subpackage Joomleague
 * @since 0.9
 */
class JLGStatisticPercentage extends JLGStatistic {
//also the name of the associated xml file	
	var $_name = 'percentage';
	
	var $_calculated = 1;
	
	var $_showinsinglematchreports = 1;

	var $_percentageSymbol = null;
	
	function __construct()
	{
		parent::__construct();
	}

	function getSids()
	{
		$params = &$this->getParams();
		$numerator_ids = explode(',', $params->get('numerator_ids'));
		if (!count($numerator_ids)) {
			JError::raiseWarning(0, JText::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id));
			return(array(0));
		}
		$denominator_ids = explode(',', $params->get('denominator_ids'));
		if (!count($denominator_ids)) {
			JError::raiseWarning(0, JText::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id));
			return(array(0));
		}
				
		$db = &JFactory::getDBO();
		$ids = array('num' => array(), 'den' => array());
		foreach ($numerator_ids as $s) {
			$ids['num'][] = (int)$s;
		}		
		foreach ($denominator_ids as $s) {
			$ids['den'][] = (int)$s;
		}
		return $ids;
	}

	function getQuotedSids()
	{
		$params = &$this->getParams();
		$numerator_ids = explode(',', $params->get('numerator_ids'));
		if (!count($numerator_ids)) {
			JError::raiseWarning(0, JText::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id));
			return(array(0));
		}
		$denominator_ids = explode(',', $params->get('denominator_ids'));
		if (!count($denominator_ids)) {
			JError::raiseWarning(0, JText::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id));
			return(array(0));
		}
				
		$db = &JFactory::getDBO();
		$ids = array('num' => array(), 'den' => array());
		foreach ($numerator_ids as $s) {
			$ids['num'][] = $db->Quote((int)$s);
		}		
		foreach ($denominator_ids as $s) {
			$ids['den'][] = $db->Quote((int)$s);
		}
		return $ids;
	}
	
	function getMatchPlayerStat(&$gamemodel, $teamplayer_id)
	{
		$gamestats = $gamemodel->getPlayersStats();
		$ids = $this->getSids();
		
		$num = 0;
		foreach ($ids['num'] as $id) 
		{
			if (isset($gamestats[$teamplayer_id][$id])) {
				$num += $gamestats[$teamplayer_id][$id];
			}
		}
		$den = 0;
		foreach ($ids['den'] as $id) 
		{
			if (isset($gamestats[$teamplayer_id][$id])) {
				$den += $gamestats[$teamplayer_id][$id];
			}
		}
		return $this->formatValue($num, $den, $this->getPrecision(), $this->getShowPercentageSymbol());
	}

	function getPlayerStatsByGame($teamplayer_ids, $project_id)
	{
		$sids = $this->getSids();
		$num = $this->getPlayerStatsByGameForIds($teamplayer_ids, $project_id, $sids['num']);
		$den = $this->getPlayerStatsByGameForIds($teamplayer_ids, $project_id, $sids['den']);
		$precision = $this->getPrecision();
		$showPercentageSymbol = $this->getShowPercentageSymbol();

		$res = array();
		foreach (array_unique(array_merge(array_keys($num), array_keys($den))) as $match_id) 
		{
			$res[$match_id] = new stdclass();
			$res[$match_id]->match_id = $match_id;
			$n = isset($num[$match_id]->value) ? $num[$match_id]->value : 0;
			$d = isset($den[$match_id]->value) ? $den[$match_id]->value : 0;
			$res[$match_id]->value = $this->formatValue($n, $d, $precision, $showPercentageSymbol);
		}
		return $res;
	}
	
	function getPlayerStatsByProject($person_id, $projectteam_id = 0, $project_id = 0, $sports_type_id = 0)
	{
		$sids = $this->getSids();

		$num = $this->getPlayerStatsByProjectForIds($person_id, $projectteam_id, $project_id, $sports_type_id, $sids['num']);
		$den = $this->getPlayerStatsByProjectForIds($person_id, $projectteam_id, $project_id, $sports_type_id, $sids['den']);

		return $this->formatValue($num, $den, $this->getPrecision(), $this->getShowPercentageSymbol());
	}

	/**
	 * Get players stats
	 * @param $team_id
	 * @param $project_id
	 * @return array
	 */
	function getRosterStats($team_id, $project_id, $position_id)
	{
		$sids = $this->getSids();
		$num = $this->getRosterStatsForIds($team_id, $project_id, $position_id, $sids['num']);
		$den = $this->getRosterStatsForIds($team_id, $project_id, $position_id, $sids['den']);
		$precision = $this->getPrecision();
		$showPercentageSymbol = $this->getShowPercentageSymbol();

		$res = array();
		foreach (array_unique(array_merge(array_keys($num), array_keys($den))) as $person_id) 
		{
			$res[$person_id] = new stdclass();
			$res[$person_id]->person_id = $person_id;
			$n = isset($num[$person_id]->value) ? $num[$person_id]->value : 0;
			$d = isset($den[$person_id]->value) ? $den[$person_id]->value : 0;
			$res[$person_id]->value = $this->formatValue($n, $d, $precision, $showPercentageSymbol);
		}
		return $res;
	}

	function getPlayersRanking($project_id, $division_id, $team_id, $limit = 20, $limitstart = 0, $order=null)
	{
		$sids = $this->getQuotedSids();
		
		$db = &JFactory::getDBO();
		$query_num	= ' SELECT SUM(ms.value) AS num, tp.id AS tpid'
					. ' FROM #__joomleague_team_player AS tp '
					. ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
					. ' INNER JOIN #__joomleague_match_statistic AS ms ON ms.teamplayer_id = tp.id '
					. '   AND ms.statistic_id IN ('. implode(',', $sids['num']) .')'
					. ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
					. '   AND m.published = 1 '
					. ' WHERE pt.project_id = '. $db->Quote($project_id);
		if ($division_id != 0)
		{
			$query_num .= ' AND pt.division_id = '. $db->Quote($division_id);
		}
		if ($team_id != 0)
		{
			$query_num .= '   AND pt.team_id = ' . $db->Quote($team_id);
		}
		$query_num .= ' GROUP BY tp.id ';
		
		$query_den = ' SELECT SUM(ms.value) AS den, tp.id AS tpid'
			. ' FROM #__joomleague_team_player AS tp '
			. ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
			. ' INNER JOIN #__joomleague_match_statistic AS ms ON ms.teamplayer_id = tp.id '
			. '   AND ms.statistic_id IN ('. implode(',', $sids['den']) .')'
			. ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
			. '   AND m.published = 1 '
			. ' WHERE pt.project_id = '. $db->Quote($project_id)
		;
		if ($division_id != 0)
		{
			$query_den .= ' AND pt.division_id = '. $db->Quote($division_id);
		}
		if ($team_id != 0)
		{
			$query_den .= '   AND pt.team_id = ' . $db->Quote($team_id);
		}
		$query_den .= '   AND value > 0 '
			. ' GROUP BY tp.id '
		;
		
		$query_select_count = ' SELECT COUNT(DISTINCT tp.id) as count';

		$query_select_details	= ' SELECT (n.num / d.den) AS total, 1 as rank,'
								. ' tp.id AS teamplayer_id, tp.person_id, tp.picture AS teamplayerpic,'
								. ' p.firstname, p.nickname, p.lastname, p.picture, p.country,'
								. ' pt.team_id, pt.picture AS projectteam_picture,'
								. ' t.picture AS team_picture, t.name AS team_name, t.short_name AS team_short_name';
 
		$query_core	= ' FROM #__joomleague_team_player AS tp'
					. ' INNER JOIN ('.$query_num.') AS n ON n.tpid = tp.id'
					. ' INNER JOIN ('.$query_den.') AS d ON d.tpid = tp.id'
					. ' INNER JOIN #__joomleague_person AS p ON p.id = tp.person_id'
					. ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id'
					. ' INNER JOIN #__joomleague_team AS t ON pt.team_id = t.id'
					. ' WHERE pt.project_id = '. $db->Quote($project_id)
				    . '   AND p.published = 1 '
		;
		if ($division_id != 0)
		{
			$query_core .= ' AND pt.division_id = '. $db->Quote($division_id);
		}
		if ($team_id != 0)
		{
			$query_core .= '   AND pt.team_id = ' . $db->Quote($team_id);
		}
		$query_end_details = ' ORDER BY total '.(!empty($order) ? $order : $this->getParam('ranking_order', 'DESC')).' ';

		$res = new stdclass;
		$db->setQuery($query_select_count.$query_core);
		$res->pagination_total = $db->loadResult();

		$db->setQuery($query_select_details.$query_core.$query_end_details, $limitstart, $limit);
		$res->ranking = $db->loadObjectList();
	
		if ($res->ranking)
		{
			$precision = $this->getPrecision();
			$showPercentageSymbol = $this->getShowPercentageSymbol();

			// get ranks
			$previousval = 0;
			$currentrank = 1 + $limitstart;
			foreach ($res->ranking as $k => $row) 
			{
				if ($row->total == $previousval) {
					$res->ranking[$k]->rank = $currentrank;
				}
				else {
					$res->ranking[$k]->rank = $k + 1 + $limitstart;
				}
				$previousval = $row->total;
				$currentrank = $res->ranking[$k]->rank;

				$res->ranking[$k]->total = $this->formatValue($res->ranking[$k]->total, 1, $precision, $showPercentageSymbol);
			}
		}

		return $res;
	}
		
	function getTeamsRanking($project_id, $limit = 20, $limitstart = 0, $order=null)
	{
		$sids = $this->getQuotedSids();
		
		$db = &JFactory::getDBO();
		$query_num = ' SELECT SUM(ms.value) AS num, pt.id '
		       . ' FROM #__joomleague_team_player AS tp '
		       . ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
		       . ' INNER JOIN #__joomleague_match_statistic AS ms ON ms.teamplayer_id = tp.id '
		       . '   AND ms.statistic_id IN ('. implode(',', $sids['num']) .')'
		       . ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
		       . '   AND m.published = 1 '
		       . ' WHERE pt.project_id = '. $db->Quote($project_id)
		       . ' GROUP BY pt.id '
		       ;
		
		$query_den = ' SELECT SUM(ms.value) AS den, pt.id '
		       . ' FROM #__joomleague_team_player AS tp '
		       . ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
		       . ' INNER JOIN #__joomleague_match_statistic AS ms ON ms.teamplayer_id = tp.id '
		       . '   AND ms.statistic_id IN ('. implode(',', $sids['den']) .')'
		       . ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
		       . '   AND m.published = 1 '
		       . ' WHERE pt.project_id = '. $db->Quote($project_id)
		       . '   AND value > 0 '
		       . ' GROUP BY pt.id '
		       ;
		       
		$query = ' SELECT (n.num / d.den) AS total, pt.team_id ' 
		       . ' FROM #__joomleague_project_team AS pt '
		       . ' INNER JOIN ('.$query_num.') AS n ON n.id = pt.id '
		       . ' INNER JOIN ('.$query_den.') AS d ON d.id = pt.id '
		       . ' INNER JOIN #__joomleague_team AS t ON pt.team_id = t.id '
		       . ' WHERE pt.project_id = '. $db->Quote($project_id)
		       . ' ORDER BY total '.(!empty($order) ? $order : $this->getParam('ranking_order', 'DESC')).' '
		       ;
		
		$db->setQuery($query, $limitstart, $limit);
		$res = $db->loadObjectList();

		if (!empty($res))
		{
			$precision = $this->getPrecision();
			$showPercentageSymbol = $this->getShowPercentageSymbol();

			// get ranks
			$previousval = 0;
			$currentrank = 1 + $limitstart;
			foreach ($res as $k => $row) 
			{
				if ($row->total == $previousval) {
					$res[$k]->rank = $currentrank;
				}
				else {
					$res[$k]->rank = $k + 1 + $limitstart;
				}
				$previousval = $row->total;
				$currentrank = $res[$k]->rank;

				$res[$k]->total = $this->formatValue($res[$k]->total, 1, $precision, $showPercentageSymbol);
			}
		}
		return $res;
	}

	function getMatchStaffStat(&$gamemodel, $team_staff_id)
	{
		$gamestats = $gamemodel->getMatchStaffStats();
		$ids = $this->getSids();
		
		$num = 0;
		foreach ($ids['num'] as $id) 
		{
			if (isset($gamestats[$team_staff_id][$id])) {
				$num += $gamestats[$team_staff_id][$id];
			}
		}
		$den = 0;
		foreach ($ids['den'] as $id) 
		{
			if (isset($gamestats[$team_staff_id][$id])) {
				$den += $gamestats[$team_staff_id][$id];
			}
		}
		return $this->formatValue($num, $den, $this->getPrecision(), $this->getShowPercentageSymbol());
	}
	
	function getStaffStats($person_id, $team_id, $project_id)
	{
		$sids = $this->getQuotedSids();
		
		$db = &JFactory::getDBO();
		$query = ' SELECT SUM(ms.value) AS value, tp.person_id '
		       . ' FROM #__joomleague_team_staff AS tp '
		       . ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
		       . ' INNER JOIN #__joomleague_match_staff_statistic AS ms ON ms.team_staff_id = tp.id '
		       . '   AND ms.statistic_id IN ('. implode(',', $sids['num']) .')'
		       . ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
		       . '   AND m.published = 1 '
		       . ' WHERE pt.team_id = '. $db->Quote($team_id)
		       . '   AND pt.project_id = '. $db->Quote($project_id)
		       . '   AND tp.person_id = '. $db->Quote($person_id)
		       . ' GROUP BY tp.id '
		       ;
		$db->setQuery($query);
		$num = $db->loadResult();
		
		$query = ' SELECT SUM(ms.value) AS value, tp.person_id '
		       . ' FROM #__joomleague_team_staff AS tp '
		       . ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
		       . ' INNER JOIN #__joomleague_match_staff_statistic AS ms ON ms.team_staff_id = tp.id '
		       . '   AND ms.statistic_id IN ('. implode(',', $sids['den']) .')'
		       . ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
		       . '   AND m.published = 1 '
		       . ' WHERE pt.team_id = '. $db->Quote($team_id)
		       . '   AND pt.project_id = '. $db->Quote($project_id)
		       . '   AND value > 0 '
		       . '   AND tp.person_id = '. $db->Quote($person_id)
		       . ' GROUP BY tp.id '
		       ;
		$db->setQuery($query);
		$den = $db->loadResult();
	
		return $this->formatValue($num, $den, $this->getPrecision(), $this->getShowPercentageSymbol());
	}
	

	function getHistoryStaffStats($person_id)
	{
		$sids = $this->getQuotedSids();
		
		$db = &JFactory::getDBO();
		$query = ' SELECT SUM(ms.value) AS value, tp.person_id '
		       . ' FROM #__joomleague_team_staff AS tp '
		       . ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
		       . ' INNER JOIN #__joomleague_match_staff_statistic AS ms ON ms.team_staff_id = tp.id '
		       . '   AND ms.statistic_id IN ('. implode(',', $sids['num']) .')'
		       . ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
		       . '   AND m.published = 1 '
		       . ' WHERE tp.person_id = '. $db->Quote($person_id)
		       . ' GROUP BY tp.id '
		       ;
		$db->setQuery($query);
		$num = $db->loadResult();
		
		$query = ' SELECT SUM(ms.value) AS value, tp.person_id '
		       . ' FROM #__joomleague_team_staff AS tp '
		       . ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
		       . ' INNER JOIN #__joomleague_match_staff_statistic AS ms ON ms.team_staff_id = tp.id '
		       . '   AND ms.statistic_id IN ('. implode(',', $sids['den']) .')'
		       . ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
		       . '   AND m.published = 1 '
		       . ' WHERE value > 0 '
		       . '   AND tp.person_id = '. $db->Quote($person_id)
		       . ' GROUP BY tp.id '
		       ;
		$db->setQuery($query);
		$den = $db->loadResult();
	
		return $this->formatValue($num, $den, $this->getPrecision(), $this->getShowPercentageSymbol());
	}

	function getShowPercentageSymbol()
	{
		$params = &$this->getParams();
		return $params->get('show_percent_symbol', 1);
	}

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

	function formatZeroValue()
	{
		return $this->formatValue(0, 0, $this->getPrecision(), $this->getShowPercentageSymbol());
	}
}
