<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'statistics'.DS.'base.php');



/**
 * SMStatisticDifference
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class SMStatisticDifference extends SMStatistic 
{
//also the name of the associated xml file	
	var $_name = 'difference';
	
	var $_calculated = 1;
		
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * SMStatisticDifference::getSids()
	 * 
	 * @param string $id_field
	 * @return
	 */
	function getSids($id_field = '')
	{
		$params = SMStatistic::getParams();
		$app = JFactory::getApplication();
		//$add_ids = explode(',', $params->get('add_ids'));
        $add_ids = $params->get('add_ids');
		//JArrayHelper::toInteger($add_ids);
		if (!count($add_ids)) 
        {
			JError::raiseWarning(0, JText::sprintf('STAT %s/%s WRONG CONFIGURATION ADD_IDS', $this->_name, $this->id));
			return(array(0));
		}
		
		//$sub_ids = explode(',', $params->get('sub_ids'));
        $sub_ids = $params->get('sub_ids');
		//JArrayHelper::toInteger($sub_ids);
		if (!count($sub_ids)) 
        {
			JError::raiseError(0, JText::sprintf('STAT %s/%s WRONG CONFIGURATION SUB_IDS', $this->_name, $this->id));
			return(array(0));
		}
				
		$ids = array('add' => $add_ids, 'sub' => $sub_ids);
		return $ids;
	}

	/**
	 * SMStatisticDifference::getQuotedSids()
	 * 
	 * @param string $id_field
	 * @return
	 */
	function getQuotedSids($id_field = '')
	{
		$db = sportsmanagementHelper::getDBConnection();
		$ids = self::getSids('');
		
		foreach ($ids['add'] as $k => $s) 
        {
			$ids['add'][$k] = $db->Quote((int)$s);
		}		
		foreach ($ids['sub'] as $k => $s) 
        {
			$ids['sub'][$k] = $db->Quote((int)$s);
		}		
		return $ids;
	}
	
	function getMatchPlayerStat(&$gamemodel, $teamplayer_id)
	{
		$gamestats = $gamemodel->getPlayersStats();
		$ids = $this->getSids();
		
		$add = 0;
		foreach ($ids['add'] as $id) 
		{
			if (isset($gamestats[$teamplayer_id][$id])) {
				$add += $gamestats[$teamplayer_id][$id];
			}
		}
		$sub = 0;
		foreach ($ids['sub'] as $id) 
		{
			if (isset($gamestats[$teamplayer_id][$id])) {
				$sub += $gamestats[$teamplayer_id][$id];
			}
		}
		
		return $this->formatValue($add, $sub, $this->getPrecision());
	}

	function getPlayerStatsByGame($teamplayer_ids, $project_id)
	{
		$sids = $this->getSids();
		$add = $this->getPlayerStatsByGameForIds($teamplayer_ids, $project_id, $sids['add']);
		$sub = $this->getPlayerStatsByGameForIds($teamplayer_ids, $project_id, $sids['sub']);
		$precision = $this->getPrecision();
		
		$res = array();
		foreach (array_unique(array_merge(array_keys($add), array_keys($sub))) as $match_id) 
		{
			$a = isset($add[$match_id]) ? $add[$match_id]->value : 0;
			$s = isset($sub[$match_id]) ? $sub[$match_id]->value : 0;
			$res[$match_id] = new stdclass();
			$res[$match_id]->match_id = $match_id;
			$res[$match_id]->value =  $this->formatValue($a, $s, $precision);
		}
		return $res;
	}
	
	function getPlayerStatsByProject($person_id, $projectteam_id = 0, $project_id = 0, $sports_type_id = 0)
	{
		$sids = $this->getSids();
		$add = $this->getPlayerStatsByProjectForIds($person_id, $projectteam_id, $project_id, $sports_type_id, $sids['add']);
		$sub = $this->getPlayerStatsByProjectForIds($person_id, $projectteam_id, $project_id, $sports_type_id, $sids['sub']);
		return $this->formatValue($add, $sub, $this->getPrecision());
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
		$add = $this->getRosterStatsForIds($team_id, $project_id, $position_id, $sids['add']);
		$sub = $this->getRosterStatsForIds($team_id, $project_id, $position_id, $sids['sub']);
		$precision = $this->getPrecision();
		
		$res = array();
		foreach (array_unique(array_merge(array_keys($add), array_keys($sub))) as $person_id) 
		{
			$a = isset($add[$person_id]) ? $add[$person_id]->value : 0;
			$s = isset($sub[$person_id]) ? $sub[$person_id]->value : 0;
			$res[$person_id] = new stdclass();
			$res[$person_id]->person_id = $person_id;
			$res[$person_id]->value = $this->formatValue($a, $s, $precision);
		}
		return $res;
	}

	/**
	 * SMStatisticDifference::getPlayersRanking()
	 * 
	 * @param mixed $project_id
	 * @param mixed $division_id
	 * @param mixed $team_id
	 * @param integer $limit
	 * @param integer $limitstart
	 * @param mixed $order
	 * @return
	 */
	function getPlayersRanking($project_id, $division_id, $team_id, $limit = 20, $limitstart = 0, $order = null)
	{
		$sids = self::getQuotedSids('');
		$db = sportsmanagementHelper::getDBConnection();
        $app = JFactory::getApplication();

//		$query_add = ' SELECT SUM(ms.value) AS num, tp.id AS tpid, tp.person_id '
//			. ' FROM #__joomleague_team_player AS tp '
//			. ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
//			. ' INNER JOIN #__joomleague_match_statistic AS ms ON ms.teamplayer_id = tp.id '
//			. '   AND ms.statistic_id IN ('. implode(',', $sids['add']) .')'
//			. ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
//			. '   AND m.published = 1 '
//			. ' WHERE pt.project_id = '. $db->Quote($project_id)
//		;
//		if ($division_id != 0)
//		{
//			$query_add .= ' AND pt.division_id = '. $db->Quote($division_id);
//		}
//		if ($team_id != 0)
//		{
//			$query_add .= '   AND pt.team_id = ' . $db->Quote($team_id);
//		}
//		$query_add .= ' GROUP BY tp.id ';
//		
//        
//        
//		$query_sub = ' SELECT SUM(ms.value) AS den, tp.id AS tpid, tp.person_id '
//			. ' FROM #__joomleague_team_player AS tp '
//			. ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
//			. ' INNER JOIN #__joomleague_match_statistic AS ms ON ms.teamplayer_id = tp.id '
//			. '   AND ms.statistic_id IN ('. implode(',', $sids['sub']) .')'
//			. ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
//			. '   AND m.published = 1 '
//			. ' WHERE pt.project_id = '. $db->Quote($project_id)
//		;
//		if ($division_id != 0)
//		{
//			$query_sub .= ' AND pt.division_id = '. $db->Quote($division_id);
//		}
//		if ($team_id != 0)
//		{
//			$query_sub .= '   AND pt.team_id = ' . $db->Quote($team_id);
//		}
//		$query_sub .= '   AND value > 0 '
//			. ' GROUP BY tp.id '
//		;
        
        
        $query_add = SMStatistic::getPlayersRankingStatisticQuery($project_id, $division_id, $team_id,$sids['add'],'SUM(ms.value) AS num, tp.id AS tpid, tp.person_id');
        $query_sub = SMStatistic::getPlayersRankingStatisticQuery($project_id, $division_id, $team_id,$sids['sub'],'SUM(ms.value) AS den, tp.id AS tpid, tp.person_id');
        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query_add<br><pre>'.print_r($query_add->dump(),true).'</pre>'),'');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query_sub<br><pre>'.print_r($query_sub->dump(),true).'</pre>'),'');
        
        //$query_sub = SMStatistic::getTeamsRankingStatisticDenQuery($project_id, $division_id, $team_id,$sids);

		//$query_select_count = ' SELECT COUNT(DISTINCT tp.id) as count';

		$query_select_details	= 'n.num - d.den AS total, n.person_id, 1 as rank,'
								. ' tp.id AS teamplayer_id, tp.person_id, tp.picture AS teamplayerpic,'
								. ' p.firstname, p.nickname, p.lastname, p.picture, p.country,'
								. ' st.team_id, pt.picture AS projectteam_picture, t.picture AS team_picture,'
								. ' t.name AS team_name, t.short_name AS team_short_name';

//		$query_core	= ' FROM #__joomleague_team_player AS tp'
//					. ' INNER JOIN ('.$query_add.') AS n ON n.tpid = tp.id'
//					. ' INNER JOIN ('.$query_sub.') AS d ON d.tpid = tp.id'
//					. ' INNER JOIN #__joomleague_person AS p ON p.id = tp.person_id'
//					. ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id'
//					. ' INNER JOIN #__joomleague_team AS t ON pt.team_id = t.id'
//					. ' WHERE pt.project_id = '. $db->Quote($project_id)
//					. '   AND p.published = 1 '
//		;
//		if ($division_id != 0)
//		{
//			$query_core .= ' AND pt.division_id = '. $db->Quote($division_id);
//		}
//		if ($team_id != 0)
//		{
//			$query_core .= '   AND pt.team_id = ' . $db->Quote($team_id);
//		}
        
        $query_core = SMStatistic::getPlayersRankingStatisticCoreQuery($project_id, $division_id, $team_id,$query_add,$query_sub);
        

		//$query_end_details	= ' ORDER BY total '.(!empty($order) ? $order : $this->getParam('ranking_order', 'DESC')).' ';
		
		$res = new stdclass;
		$db->setQuery($query_core);
        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query_core<br><pre>'.print_r($query_core->dump(),true).'</pre>'),'');
        
		$res->pagination_total = $db->loadResult();
        
        $query_core->clear('select');
        $query_core->select($query_select_details);
        $query_core->order('total '.(!empty($order) ? $order : SMStatistic::getParam('ranking_order', 'DESC')).' ');

		$db->setQuery($query_core, $limitstart, $limit);
        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query_core<br><pre>'.print_r($query_core->dump(),true).'</pre>'),'');
        
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
				$res->ranking[$k]->total = $this->formatValue($res->ranking[$k]->total, 0, $precision);
			}
		}

		return $res;
	}
		
	function getTeamsRanking($project_id, $limit = 20, $limitstart = 0, $order=null)
	{
		$sids = $this->getQuotedSids();
		
		$db = &sportsmanagementHelper::getDBConnection();
		$query_add = ' SELECT SUM(ms.value) AS num, tp.person_id '
		       . ' FROM #__joomleague_team_player AS tp '
		       . ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
		       . ' INNER JOIN #__joomleague_match_statistic AS ms ON ms.teamplayer_id = tp.id '
		       . '   AND ms.statistic_id IN ('. implode(',', $sids['add']) .')'
		       . ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
		       . '   AND m.published = 1 '
		       . ' WHERE pt.project_id = '. $db->Quote($project_id)
		       . '   AND tp.published = 1 '
		       . ' GROUP BY tp.id '
		       ;
		
		$query_sub = ' SELECT SUM(ms.value) AS den, tp.person_id '
		       . ' FROM #__joomleague_team_player AS tp '
		       . ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
		       . ' INNER JOIN #__joomleague_match_statistic AS ms ON ms.teamplayer_id = tp.id '
		       . '   AND ms.statistic_id IN ('. implode(',', $sids['sub']) .')'
		       . ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
		       . '   AND m.published = 1 '
		       . ' WHERE pt.project_id = '. $db->Quote($project_id)
		       . '   AND value > 0 '
		       . '   AND tp.published = 1 '
		       . ' GROUP BY tp.id '
		       ;
		       
		$query = ' SELECT SUM(n.num - d.den) AS total, pt.team_id  '
		       . ' FROM #__joomleague_team_player AS tp '
		       . ' INNER JOIN ('.$query_add.') AS n ON n.person_id = tp.person_id '
		       . ' INNER JOIN ('.$query_sub.') AS d ON n.person_id = d.person_id '
		       . ' INNER JOIN #__joomleague_person AS p ON p.id = tp.person_id '
		       . ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
		       . ' INNER JOIN #__joomleague_team AS t ON pt.team_id = t.id '
		       . ' WHERE pt.project_id = '. $db->Quote($project_id)
		       . '   AND tp.published = 1 '
		       . ' GROUP BY pt.team_id '
		       . ' ORDER BY total '.(!empty($order) ? $order : $this->getParam('ranking_order', 'DESC')).' '
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
				$res[$k]->total = $this->formatValue($res[$k]->total, 0, $precision);
			}
		}
		return $res;
	}

	function getMatchStaffStat(&$gamemodel, $team_staff_id)
	{
		$gamestats = $gamemodel->getMatchStaffStats();
		$ids = $this->getSids();
		
		$add = 0;
		foreach ($ids['add'] as $id) 
		{
			if (isset($gamestats[$team_staff_id][$id])) {
				$add += $gamestats[$team_staff_id][$id];
			}
		}
		$sub = 0;
		foreach ($ids['sub'] as $id) 
		{
			if (isset($gamestats[$team_staff_id][$id])) {
				$sub += $gamestats[$team_staff_id][$id];
			}
		}
		
		return self::formatValue($add, $sub, SMStatistic::getPrecision());
	}
	
	/**
	 * SMStatisticDifference::getStaffStats()
	 * 
	 * @param mixed $person_id
	 * @param mixed $team_id
	 * @param mixed $project_id
	 * @return
	 */
	function getStaffStats($person_id, $team_id, $project_id)
	{
		$option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
        $sids = self::getQuotedSids();
		
		$db = sportsmanagementHelper::getDBConnection();
        
        $select = 'SUM(ms.value) AS value, tp.person_id ';
        $query = SMStatistic::getStaffStatsQuery($person_id, $team_id, $project_id, implode(',', $sids['add']),$select,FALSE);
        
//		$query = ' SELECT SUM(ms.value) AS value, tp.person_id '
//		       . ' FROM #__joomleague_team_staff AS tp '
//		       . ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
//		       . ' INNER JOIN #__joomleague_match_staff_statistic AS ms ON ms.team_staff_id = tp.id '
//		       . '   AND ms.statistic_id IN ('. implode(',', $sids['add']) .')'
//		       . ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
//		       . '   AND m.published = 1 '
//		       . ' WHERE pt.team_id = '. $db->Quote($team_id)
//		       . '   AND pt.project_id = '. $db->Quote($project_id)
//		       . '   AND tp.person_id = '. $db->Quote($person_id)
//		       . ' GROUP BY tp.id '
//		       ;
		$db->setQuery($query);
        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        
		$add = $db->loadResult();
		$add = isset($add->value) ? $add->value : 0;
		
        $select = 'SUM(ms.value) AS value, tp.person_id ';
        $query = SMStatistic::getStaffStatsQuery($person_id, $team_id, $project_id, implode(',', $sids['sub']),$select,FALSE);
//		$query = ' SELECT SUM(ms.value) AS value, tp.person_id '
//		       . ' FROM #__joomleague_team_staff AS tp '
//		       . ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
//		       . ' INNER JOIN #__joomleague_match_staff_statistic AS ms ON ms.team_staff_id = tp.id '
//		       . '   AND ms.statistic_id IN ('. implode(',', $sids['sub']) .')'
//		       . ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
//		       . '   AND m.published = 1 '
//		       . ' WHERE pt.team_id = '. $db->Quote($team_id)
//		       . '   AND pt.project_id = '. $db->Quote($project_id)
//		       . '   AND value > 0 '
//		       . '   AND tp.person_id = '. $db->Quote($person_id)
//		       . ' GROUP BY tp.id '
//		       ;
		$db->setQuery($query);
        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        
		$sub = $db->loadResult();
		$sub = isset($sub->value) ? $sub->value : 0;

		return self::formatValue($add, $sub, SMStatistic::getPrecision());
	}
	

	/**
	 * SMStatisticDifference::getHistoryStaffStats()
	 * 
	 * @param mixed $person_id
	 * @return
	 */
	function getHistoryStaffStats($person_id)
	{
		$option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
        $sids = self::getQuotedSids();
		
		$db = sportsmanagementHelper::getDBConnection();
        $select = 'SUM(ms.value) AS value, tp.person_id ';
        $query = SMStatistic::getStaffStatsQuery($person_id, 0, 0, implode(',', $sids['add']),$select,TRUE);
        
//		$query = ' SELECT SUM(ms.value) AS value, tp.person_id '
//		       . ' FROM #__joomleague_team_staff AS tp '
//		       . ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
//		       . ' INNER JOIN #__joomleague_match_staff_statistic AS ms ON ms.team_staff_id = tp.id '
//		       . '   AND ms.statistic_id IN ('. implode(',', $sids['add']) .')'
//		       . ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
//		       . '   AND m.published = 1 '
//		       . ' WHERE tp.person_id = '. $db->Quote($person_id)
//		       . ' GROUP BY tp.id '
//		       ;
		$db->setQuery($query);
        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        
		$add = $db->loadResult();
		$add = isset($add->value) ? $add->value : 0;
		
        $select = 'SUM(ms.value) AS value, tp.person_id ';
        $query = SMStatistic::getStaffStatsQuery($person_id, 0, 0, implode(',', $sids['sub']),$select,TRUE);
//		$query = ' SELECT SUM(ms.value) AS value, tp.person_id '
//		       . ' FROM #__joomleague_team_staff AS tp '
//		       . ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
//		       . ' INNER JOIN #__joomleague_match_staff_statistic AS ms ON ms.team_staff_id = tp.id '
//		       . ' AND ms.statistic_id IN ('. implode(',', $sids['sub']) .')'
//		       . ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
//		       . '   AND m.published = 1 '
//		       . ' WHERE value > 0 '
//		       . '   AND tp.person_id = '. $db->Quote($person_id)
//		       . ' GROUP BY tp.id '
//		       ;
		$db->setQuery($query);
        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        
		$sub = $db->loadResult();
		$sub = isset($sub->value) ? $sub->value : 0;
	
		return self::formatValue($add, $sub, $this->getPrecision());
	}

	/**
	 * SMStatisticDifference::formatValue()
	 * 
	 * @param mixed $add
	 * @param mixed $sub
	 * @param mixed $precision
	 * @return
	 */
	function formatValue($add, $sub, $precision)
	{
		$value = (!empty($add) ? $add : 0) - (!empty($sub) ? $sub : 0);
		return number_format($value, $precision);
	}
}