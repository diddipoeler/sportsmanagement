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
class JLGStatisticSumstats extends JLGStatistic {
//also the name of the associated xml file	
	var $_name = 'sumstats';
	
	var $_calculated = 1;
	
	var $_showinsinglematchreports = 1;
	
	function __construct()
	{
		parent::__construct();
	}
	
	function getSids()
	{
		$params = &$this->getParams();
		$stat_ids = explode(',', $params->get('stat_ids'));
		if (!count($stat_ids)) {
			JError::raiseWarning(0, JText::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id));
			return(array(0));
		}
				
		$db = &JFactory::getDBO();
		$sids = array();
		foreach ($stat_ids as $s) {
			$sids[] = (int)$s;
		}		
		return $sids;
	}
	
	function getQuotedSids()
	{
		$params = &$this->getParams();
		$stat_ids = explode(',', $params->get('stat_ids'));
		if (!count($stat_ids)) {
			JError::raiseWarning(0, JText::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id));
			return(array(0));
		}
				
		$db = &JFactory::getDBO();
		$sids = array();
		foreach ($stat_ids as $s) {
			$sids[] = $db->Quote($s);
		}		
		return $sids;
	}
	
	function getMatchPlayerStat(&$gamemodel, $teamplayer_id)
	{
		$gamestats = $gamemodel->getPlayersStats();
		$stat_ids = $this->getSids();
		
		$res = 0;
		foreach ($stat_ids as $id) 
		{
			if (isset($gamestats[$teamplayer_id][$id])) {
				$res += $gamestats[$teamplayer_id][$id];
			}
		}
		return $this->formatValue($res, $this->getPrecision());
	}

	function getPlayerStatsByGame($teamplayer_ids, $project_id)
	{
		$sids = $this->getSids();
		$res = $this->getPlayerStatsByGameForIds($teamplayer_ids, $project_id, $sids);
		if (is_array($res))
		{
			$precision = $this->getPrecision();
			foreach($res as $k => $match)
			{
				$res[$k]->value = $this->formatValue($res[$k]->value, $precision);
			}
		}
		return $res;
	}

	function getPlayerStatsByProject($person_id, $projectteam_id = 0, $project_id = 0, $sports_type_id = 0)
	{
		$sids = $this->getSids();
		$value = $this->getPlayerStatsByProjectForIds($person_id, $projectteam_id, $project_id, $sports_type_id, $sids);
		return $this->formatValue($value, $this->getPrecision());
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
		$res = $this->GetRosterStatsForIds($team_id, $project_id, $position_id, $sids);
		if (!empty($res))
		{
			$precision = $this->getPrecision();
			foreach ($res as $k => $player)
			{
				$res[$k]->value = $this->formatValue($res[$k]->value, $precision);
			}
		}
		return $res;
	}

	function getPlayersRanking($project_id, $division_id, $team_id, $limit = 20, $limitstart = 0, $order=null)
	{		
		$sids = $this->getQuotedSids();
		
		$db = &JFactory::getDBO();

		$query_select_count = ' SELECT COUNT(DISTINCT tp.id) as count';

		$query_select_details	= ' SELECT SUM(ms.value) AS total,'
								. ' tp.id AS teamplayer_id, tp.person_id, tp.picture AS teamplayerpic,'
								. ' p.firstname, p.nickname, p.lastname, p.picture, p.country,'
								. ' pt.team_id, pt.picture AS projectteam_picture, t.picture AS team_picture,'
								. ' t.name AS team_name, t.short_name AS team_short_name';

		$query_core	= ' FROM #__joomleague_team_player AS tp'
					. ' INNER JOIN #__joomleague_person AS p ON p.id = tp.person_id'
					. ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id'
					. ' INNER JOIN #__joomleague_team AS t ON pt.team_id = t.id'
					. ' INNER JOIN #__joomleague_match_statistic AS ms ON ms.teamplayer_id = tp.id'
					. '   AND ms.statistic_id IN ('. implode(',', $sids) .')'
					. ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id'
					. '   AND m.published = 1'
					. ' WHERE pt.project_id = '. $db->Quote($project_id)
					. '   AND p.published = 1 '
		;
		if ($division_id != 0)
		{
			$query .= ' AND pt.division_id = '. $db->Quote($division_id);
		}
		if ($team_id != 0)
		{
			$query_core .= '   AND pt.team_id = ' . $db->Quote($team_id);
		}
		$query_end_details	= ' GROUP BY tp.id '
							. ' ORDER BY total '.(!empty($order) ? $order : $this->getParam('ranking_order', 'DESC')).', tp.id'
		;

		$res = new stdclass;
		$db->setQuery($query_select_count.$query_core);
		$res->pagination_total = $db->loadResult();

		$db->setQuery($query_select_details.$query_core.$query_end_details, $limitstart, $limit);
		$res->ranking = $db->loadObjectList();
	
		if ($res->ranking)
		{
			$precision = $this->getPrecision();
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

				$res->ranking[$k]->total = $this->formatValue($res->ranking[$k]->total, $precision);
			}
		}

		return $res;
	}

	function getTeamsRanking($project_id, $limit = 20, $limitstart = 0, $order=null)
	{		
		$sids = $this->getQuotedSids();
		
		$db = &JFactory::getDBO();
		
		$query = ' SELECT SUM(ms.value) AS total,  pt.team_id ' 
		       . ' FROM #__joomleague_team_player AS tp '
		       . ' INNER JOIN #__joomleague_person AS p ON p.id = tp.person_id '
		       . ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
		       . ' INNER JOIN #__joomleague_team AS t ON pt.team_id = t.id '
		       . ' INNER JOIN #__joomleague_match_statistic AS ms ON ms.teamplayer_id = tp.id '
		       . '   AND ms.statistic_id IN ('. implode(',', $sids) .')'
		       . ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
		       . '   AND m.published = 1 '
		       . ' WHERE pt.project_id = '. $db->Quote($project_id)
		       . '   AND p.published = 1 '
		       . '   AND tp.published = 1 '
		       . ' GROUP BY pt.id '
		       . ' ORDER BY total '.(!empty($order) ? $order : $this->getParam('ranking_order', 'DESC')).', tp.id'
		       ;
		$db->setQuery($query, $limitstart, $limit);
		$res = $db->loadObjectList();

		if (!empty($res))
		{
			$precision = $this->getPrecision();
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

				$res[$k]->total = $this->formatValue($res[$k]->total, $precision);
			}
		}
		return $res;
	}

	function getMatchStaffStat(&$gamemodel, $team_staff_id)
	{
		$gamestats = $gamemodel->getMatchStaffStats();
		$stat_ids = $this->getSids();
		
		$res = 0;
		foreach ($stat_ids as $id) 
		{
			if (isset($gamestats[$team_staff_id][$id])) {
				$res += $gamestats[$team_staff_id][$id];
			}
		}

		return $this->formatValue($res, $this->getPrecision());
	}
	
	function getStaffStats($person_id, $team_id, $project_id)
	{
		$sids = $this->getQuotedSids();
		
		$db = &JFactory::getDBO();
		
		$query = ' SELECT SUM(ms.value) AS value '
		       . ' FROM #__joomleague_team_staff AS tp '
		       . ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
		       . ' INNER JOIN #__joomleague_match_staff_statistic AS ms ON ms.team_staff_id = tp.id '
		       . '   AND ms.statistic_id IN ('. implode(',', $sids) .')'
		       . ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
		       . '   AND m.published = 1 '
		       . ' WHERE pt.team_id = '. $db->Quote($team_id)
		       . '   AND pt.project_id = '. $db->Quote($project_id)
		       . '   AND tp.person_id = '. $db->Quote($person_id)
		       . ' GROUP BY tp.id '
		       ;
		$db->setQuery($query);
		$res = $db->loadResult();

		return $this->formatValue($res, $this->getPrecision());
	}
	
	function getHistoryStaffStats($person_id)
	{
		$sids = $this->getQuotedSids();
		
		$db = &JFactory::getDBO();
		
		$query = ' SELECT SUM(ms.value) AS value '
		       . ' FROM #__joomleague_team_staff AS tp '
		       . ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
		       . ' INNER JOIN #__joomleague_project AS p ON p.id = pt.project_id '
		       . ' INNER JOIN #__joomleague_match_staff_statistic AS ms ON ms.team_staff_id = tp.id '
		       . '   AND ms.statistic_id IN ('. implode(',', $sids) .')'
		       . ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
		       . '   AND m.published = 1 '
		       . ' WHERE  tp.person_id = '. $db->Quote($person_id)
		       . '   AND p.published = 1 '
		       . ' GROUP BY tp.id '
		       ;
		$db->setQuery($query);
		$res = $db->loadResult();

		return $this->formatValue($res, $this->getPrecision());
	}

	function formatValue($value, $precision)
	{
		if (empty($value))
		{
			$value = 0;
		}
		return number_format($value, $precision);
	}
}