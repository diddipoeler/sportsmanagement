<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      sumstats.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage statistics
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'statistics'.DS.'base.php');


/**
 * SMStatisticSumstats
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class SMStatisticSumstats extends SMStatistic 
{
//also the name of the associated xml file	
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
	
//	function getSids()
//	{
//		$params = &$this->getParams();
//		$stat_ids = explode(',', $params->get('stat_ids'));
//		if (!count($stat_ids)) {
//			JError::raiseWarning(0, JText::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id));
//			return(array(0));
//		}
//				
//		$db = &sportsmanagementHelper::getDBConnection();
//		$sids = array();
//		foreach ($stat_ids as $s) {
//			$sids[] = (int)$s;
//		}		
//		return $sids;
//	}
	
//	function getQuotedSids()
//	{
//		$params = &$this->getParams();
//		$stat_ids = explode(',', $params->get('stat_ids'));
//		if (!count($stat_ids)) {
//			JError::raiseWarning(0, JText::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id));
//			return(array(0));
//		}
//				
//		$db = &sportsmanagementHelper::getDBConnection();
//		$sids = array();
//		foreach ($stat_ids as $s) {
//			$sids[] = $db->Quote($s);
//		}		
//		return $sids;
//	}
	
	/**
	 * SMStatisticSumstats::getMatchPlayerStat()
	 * 
	 * @param mixed $gamemodel
	 * @param mixed $teamplayer_id
	 * @return
	 */
	function getMatchPlayerStat(&$gamemodel, $teamplayer_id)
	{
		$gamestats = $gamemodel->getPlayersStats();
		$stat_ids = SMStatistic::getSids($this->_ids);
		
		$res = 0;
		foreach ($stat_ids as $id) 
		{
			if (isset($gamestats[$teamplayer_id][$id])) {
				$res += $gamestats[$teamplayer_id][$id];
			}
		}
		return self::formatValue($res, $this->getPrecision());
	}

	/**
	 * SMStatisticSumstats::getPlayerStatsByGame()
	 * 
	 * @param mixed $teamplayer_ids
	 * @param mixed $project_id
	 * @return
	 */
	function getPlayerStatsByGame($teamplayer_ids, $project_id)
	{
		$sids = SMStatistic::getSids($this->_ids);
		$res = SMStatistic::getPlayerStatsByGameForIds($teamplayer_ids, $project_id, $sids);
		if (is_array($res))
		{
			$precision = SMStatistic::getPrecision();
			foreach($res as $k => $match)
			{
				$res[$k]->value = self::formatValue($res[$k]->value, $precision);
			}
		}
		return $res;
	}

	/**
	 * SMStatisticSumstats::getPlayerStatsByProject()
	 * 
	 * @param mixed $person_id
	 * @param integer $projectteam_id
	 * @param integer $project_id
	 * @param integer $sports_type_id
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
	 * @param $team_id
	 * @param $project_id
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
	 * @param mixed $project_id
	 * @param mixed $division_id
	 * @param mixed $team_id
	 * @param integer $limit
	 * @param integer $limitstart
	 * @param mixed $order
	 * @return
	 */
	function getPlayersRanking($project_id, $division_id, $team_id, $limit = 20, $limitstart = 0, $order=null)
	{		
		$sids = SMStatistic::getQuotedSids($this->_ids);
		$app = JFactory::getApplication();
		$db = sportsmanagementHelper::getDBConnection();
        $query_num = JFactory::getDbo()->getQuery(true);

		$query_select_count = 'COUNT(DISTINCT tp.id) as count';

		$query_select_details	= 'SUM(ms.value) AS total,'
								. ' tp.id AS teamplayer_id, tp.person_id, tp.picture AS teamplayerpic,'
								. ' p.firstname, p.nickname, p.lastname, p.picture, p.country,'
								. ' st.team_id, pt.picture AS projectteam_picture, t.picture AS team_picture,'
								. ' t.name AS team_name, t.short_name AS team_short_name';

//		$query_core	= ' FROM #__joomleague_team_player AS tp'
//					. ' INNER JOIN #__joomleague_person AS p ON p.id = tp.person_id'
//					. ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id'
//					. ' INNER JOIN #__joomleague_team AS t ON pt.team_id = t.id'
//					. ' INNER JOIN #__joomleague_match_statistic AS ms ON ms.teamplayer_id = tp.id'
//					. '   AND ms.statistic_id IN ('. implode(',', $sids) .')'
//					. ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id'
//					. '   AND m.published = 1'
//					. ' WHERE pt.project_id = '. $db->Quote($project_id)
//					. '   AND p.published = 1 '
//		;
//		if ($division_id != 0)
//		{
//			$query .= ' AND pt.division_id = '. $db->Quote($division_id);
//		}
//		if ($team_id != 0)
//		{
//			$query_core .= '   AND pt.team_id = ' . $db->Quote($team_id);
//		}
//		$query_end_details	= ' GROUP BY tp.id '
//							. ' ORDER BY total '.(!empty($order) ? $order : $this->getParam('ranking_order', 'DESC')).', tp.id';
        
        $query_core	= SMStatistic::getPlayersRankingStatisticQuery($project_id, $division_id, $team_id, $sids, $query_select_count,'statistic');

		$res = new stdclass;
		$db->setQuery($query_core);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query_core<br><pre>'.print_r($query_core->dump(),true).'</pre>'),'');
        }
        
		$res->pagination_total = $db->loadResult();
        
        $query_core->clear('select');
        $query_core->select($query_select_details);
        //$query_core->group('tp.id');
		$query_core->order('total '.(!empty($order) ? $order : $this->getParam('ranking_order', 'DESC')).', tp.id');

		$db->setQuery($query_core, $limitstart, $limit);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query_core<br><pre>'.print_r($query_core->dump(),true).'</pre>'),'');
        }
        
		$res->ranking = $db->loadObjectList();
	
		if ($res->ranking)
		{
			$precision = SMStatistic::getPrecision();
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

				$res->ranking[$k]->total = self::formatValue($res->ranking[$k]->total, $precision);
			}
		}

		return $res;
	}

	/**
	 * SMStatisticSumstats::getTeamsRanking()
	 * 
	 * @param mixed $project_id
	 * @param integer $limit
	 * @param integer $limitstart
	 * @param mixed $order
	 * @param string $select
	 * @param integer $statistic_id
	 * @return
	 */
	function getTeamsRanking($project_id, $limit = 20, $limitstart = 0, $order=null, $select = '', $statistic_id = 0)
	{		
		$sids = SMStatistic::getQuotedSids($this->_ids);
		$db = sportsmanagementHelper::getDBConnection();
		
		$select = 'SUM(ms.value) AS total, st.team_id ';
        $statistic_id = $this->id;
        $query = SMStatistic::getTeamsRanking($project_id, $limit, $limitstart, $order, $select,$statistic_id) ;
        $query->order('total '.(!empty($order) ? $order : $this->getParam('ranking_order', 'DESC')).', tp.id ');
	$query->group('st.team_id');
try{        
		$db->setQuery($query, $limitstart, $limit);
		$res = $db->loadObjectList();
} catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
}
		
//JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' query<pre>'.print_r($query->dump(),true).'</pre>', 'error');
//JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' res<pre>'.print_r($res,true).'</pre>', 'error');
		
		if ($res)
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
		$stat_ids = SMStatistic::getSids($this->_ids);
		
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
		$sids = SMStatistic::getQuotedSids($this->_ids);
		
		$db = &sportsmanagementHelper::getDBConnection();
		
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
		$sids = SMStatistic::getQuotedSids($this->_ids);
		
		$db = &sportsmanagementHelper::getDBConnection();
		
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

	/**
	 * SMStatisticSumstats::formatValue()
	 * 
	 * @param mixed $value
	 * @param mixed $precision
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
