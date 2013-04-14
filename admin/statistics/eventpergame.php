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
class JLGStatisticEventPergame extends JLGStatistic {
//also the name of the associated xml file	
	var $_name = 'eventpergame';
	
	var $_calculated = 1;
	
	var $_showinsinglematchreports = 0;
	
	function __construct()
	{
		parent::__construct();
	}
	
	function getSids()
	{
		$params = &$this->getParams();
		$event_ids = explode(',', $params->get('event_ids'));
		if (!count($event_ids)) {
			JError::raiseWarning(0, JText::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id));
			return(array(0));
		}
				
		$db = &JFactory::getDBO();
		$ids = array();
		foreach ($event_ids as $s) {
			$ids[] = (int)$s;
		}		
		return $ids;
	}

	function getQuotedSids()
	{
		$params = &$this->getParams();
		$event_ids = explode(',', $params->get('event_ids'));
		if (!count($event_ids)) {
			JError::raiseWarning(0, JText::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id));
			return(array(0));
		}
				
		$db = &JFactory::getDBO();
		$ids = array();
		foreach ($event_ids as $s) {
			$ids[] = $db->Quote((int)$s);
		}		
		return $ids;
	}
		
	/**
	 * (non-PHPdoc)
	 * @see administrator/components/com_joomleague/statistics/JLGStatistic#getPlayerStatsByProject($person_id, $project_id)
	 */
	function getPlayerStatsByProject($person_id, $projectteam_id = 0, $project_id = 0, $sports_type_id = 0)
	{
		$sids = $this->getSids();
		$num = $this->getPlayerStatsByProjectForEvents($person_id, $projectteam_id, $project_id, $sports_type_id, $sids);
		$den = $this->getGamesPlayedByPlayer($person_id, $projectteam_id, $project_id, $sports_type_id);

		return $this->formatValue($num, $den, $this->getPrecision());
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
		$num = $this->getRosterStatsForEvents($team_id, $project_id, $position_id, $sids);
		$den = $this->getGamesPlayedByProjectTeam($team_id, $project_id, $position_id);
		$precision = $this->getPrecision();
		
		$res = array();
		foreach (array_unique(array_merge(array_keys($num), array_keys($den))) as $person_id) 
		{
			$n = isset($num[$person_id]) ? $num[$person_id]->value : 0;
			$d = isset($den[$person_id]) ? $den[$person_id]->value : 0;
			$res[$person_id] = new stdclass();
			$res[$person_id]->person_id = $person_id;
			$res[$person_id]->value = $this->formatValue($n, $d, $precision);
		}
		return $res;
	}

	function getPlayersRanking($project_id, $division_id, $team_id, $limit = 20, $limitstart = 0, $order=null)
	{
		$sids = $this->getQuotedSids();
		
		$db = &JFactory::getDBO();
		$query_num = ' SELECT SUM(me.event_sum) AS num, tp.id AS tpid, tp.person_id '
			. ' FROM #__joomleague_team_player AS tp '
			. ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
			. ' INNER JOIN #__joomleague_match_event AS me ON me.teamplayer_id = tp.id '
			. '   AND me.event_type_id IN ('. implode(',', $sids) .')'
			. ' INNER JOIN #__joomleague_match AS m ON m.id = me.match_id '
			. '   AND m.published = 1 '
			. ' WHERE pt.project_id = '. $db->Quote($project_id)
		;
		if ($division_id != 0)
		{
			$query_num .= ' AND pt.division_id = '. $db->Quote($division_id);
		}
		if ($team_id != 0)
		{
			$query_num .= '   AND pt.team_id = ' . $db->Quote($team_id);
		}
		$query_num .= ' GROUP BY tp.id ';
		
		$query_den = $this->getGamesPlayedQuery($project_id, $division_id, $team_id);

		$query_select_count = ' SELECT COUNT(DISTINCT tp.id) as count';

		$query_select_details = ' SELECT (n.num / d.played) AS total, n.person_id, 1 as rank,'
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
					. '   AND p.published = 1 ';

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

				$res->ranking[$k]->total = $this->formatValue($res->ranking[$k]->total, 1, $precision);
			}
		}

		return $res;
	}
		
	function getTeamsRanking($project_id, $limit = 20, $limitstart = 0)
	{
		$sids = $this->getQuotedSids();
		
		$db = &JFactory::getDBO();
		$query_num = ' SELECT SUM(es.event_sum) AS num, pt.id '
		       . ' FROM #__joomleague_team_player AS tp '
		       . ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
		       . ' INNER JOIN #__joomleague_match_event AS es ON es.teamplayer_id = tp.id '
		       . '   AND es.event_type_id IN ('. implode(',', $sids) .')'
		       . ' INNER JOIN #__joomleague_match AS m ON m.id = es.match_id '
		       . '   AND m.published = 1 '
		       . ' WHERE pt.project_id = '. $db->Quote($project_id)
		       . '   AND tp.published = 1 '
		       . ' GROUP BY pt.id '
		       ;
		
		$query_den = ' SELECT COUNT(m.id) AS value, pt.id '
		       . ' FROM #__joomleague_project_team AS pt '
		       . ' INNER JOIN #__joomleague_match AS m ON m.projectteam1_id = pt.id OR m.projectteam2_id = pt.id'
		       . '   AND m.team1_result IS NOT NULL '
		       . '   AND m.published = 1 '
		       . ' WHERE pt.project_id = '. $db->Quote($project_id)
		       . ' GROUP BY pt.id '
		       ;
		       
		$query = ' SELECT (n.num / d.value) AS total, pt.team_id ' 
		       . ' FROM #__joomleague_project_team AS pt '
		       . ' INNER JOIN ('.$query_num.') AS n ON n.id = pt.id '
		       . ' INNER JOIN ('.$query_den.') AS d ON d.id = pt.id '
		       . ' INNER JOIN #__joomleague_team AS t ON pt.team_id = t.id '
		       . ' WHERE pt.project_id = '. $db->Quote($project_id)
		       . ' ORDER BY total DESC '
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
	
				$res[$k]->total = $this->formatValue($res[$k]->total, 1, $precision);
			}
		}
		return $res;
	}
	
	function getStaffStats($person_id, $team_id, $project_id)
	{
		$sids = $this->getQuotedSids();
		
		$db = &JFactory::getDBO();
		$query = ' SELECT SUM(ms.value) AS value, tp.person_id '
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
		$num = $db->loadResult();
		
		$query = ' SELECT COUNT(ms.id) AS value, tp.person_id '
		       . ' FROM #__joomleague_team_staff AS tp '
		       . ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
		       . ' INNER JOIN #__joomleague_match_staff AS ms ON ms.team_staff_id = tp.id '
		       . ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
		       . '   AND m.published = 1 '
		       . ' WHERE pt.team_id = '. $db->Quote($team_id)
		       . '   AND pt.project_id = '. $db->Quote($project_id)
		       . '   AND tp.person_id = '. $db->Quote($person_id)
		       . ' GROUP BY tp.id '
		       ;
		$db->setQuery($query);
		$den = $db->loadResult();
	
		return $this->formatValue($num, $den, $this->getPrecision());
	}
	

	function getHistoryStaffStats($person_id)
	{
		$sids = $this->getQuotedSids();
		
		$db = &JFactory::getDBO();
		$query = ' SELECT SUM(ms.value) AS value, tp.person_id '
		       . ' FROM #__joomleague_team_staff AS tp '
		       . ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
		       . ' INNER JOIN #__joomleague_match_staff_statistic AS ms ON ms.team_staff_id = tp.id '
		       . '   AND ms.statistic_id IN ('. implode(',', $sids) .')'
		       . ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
		       . '   AND m.published = 1 '
		       . ' WHERE tp.person_id = '. $db->Quote($person_id)
		       . ' GROUP BY tp.id '
		       ;
		$db->setQuery($query);
		$num = $db->loadResult();
		
		$query = ' SELECT COUNT(ms.id) AS value, tp.person_id '
		       . ' FROM #__joomleague_team_staff AS tp '
		       . ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
		       . ' INNER JOIN #__joomleague_match_staff AS ms ON ms.team_staff_id = tp.id '
		       . ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
		       . '   AND m.published = 1 '
		       . ' WHERE tp.person_id = '. $db->Quote($person_id)
		       . ' GROUP BY tp.id '
		       ;
		$db->setQuery($query);
		$den = $db->loadResult();
	
		return $this->formatValue($num, $den, $this->getPrecision());
	}

	function formatValue($num, $den, $precision)
	{
		$value = (!empty($num) && !empty($den)) ? $num / $den : 0;
		return number_format($value, $precision);
	}
}