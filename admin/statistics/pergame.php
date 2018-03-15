<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      pergame.php
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
 * SMStatisticPergame
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class SMStatisticPergame extends SMStatistic 
{
//also the name of the associated xml file	
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
	
//	/**
//	 * SMStatisticPergame::getSids()
//	 * 
//	 * @return
//	 */
//	function getSids($id_field = 'numerator_ids')
//	{
//	   $app = JFactory::getApplication();
//		$params = SMStatistic::getParams();
//		$numerator_ids = explode(',', $params->get('numerator_ids'));
//		if (!count($numerator_ids)) {
//			JError::raiseWarning(0, JText::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id));
//			return(array(0));
//		}
//				
//		$db = sportsmanagementHelper::getDBConnection();
//		$ids = array();
//		foreach ($numerator_ids as $s) {
//			$ids[] = (int)$s;
//		}		
//		return $ids;
//	}

//	/**
//	 * SMStatisticPergame::getQuotedSids()
//	 * 
//	 * @return
//	 */
//	function getQuotedSids($id_field = 'numerator_ids')
//	{
//	   $app = JFactory::getApplication();
//		$params = SMStatistic::getParams();
//		$numerator_ids = explode(',', $params->get('numerator_ids'));
//		if (!count($numerator_ids)) {
//			JError::raiseWarning(0, JText::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id));
//			return(array(0));
//		}
//				
//		$db = sportsmanagementHelper::getDBConnection();
//		$ids = array();
//		foreach ($numerator_ids as $s) {
//			$ids[] = $db->Quote((int)$s);
//		}		
//		return $ids;
//	}
	
	function getPlayerStatsByProject($person_id, $projectteam_id = 0, $project_id = 0, $sports_type_id = 0)
	{
		$sids = SMStatistic::getSids($this->_ids);
		
		$num = $this->getPlayerStatsByProjectForIds($person_id, $projectteam_id, $project_id, $sports_type_id, $sids);
		$den = $this->getGamesPlayedByPlayer($person_id, $projectteam_id, $project_id, $sports_type_id);

		return self::formatValue($num, $den, $this->getPrecision());
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
		$num = $this->getRosterStatsForIds($team_id, $project_id, $position_id, $sids);
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

	/**
	 * SMStatisticPergame::getPlayersRanking()
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
		$sids = SMStatistic::getQuotedSids($this->_ids);
		
		$option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
		$db = sportsmanagementHelper::getDBConnection();

		$query_num = SMStatistic::getPlayersRankingStatisticNumQuery($project_id, $division_id, $team_id,$sids);
        $query_den = SMStatistic::getGamesPlayedQuery($project_id, $division_id, $team_id);
        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query_num<br><pre>'.print_r($query_num->dump(),true).'</pre>'),'');
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query_den<br><pre>'.print_r($query_den->dump(),true).'</pre>'),'');

		$query_select_details = '(n.num / d.played) AS total, n.person_id, 1 as rank,'
							  . ' tp.id AS teamplayer_id, tp.person_id, tp.picture AS teamplayerpic,'
							  . ' p.firstname, p.nickname, p.lastname, p.picture, p.country,'
							  . ' st.team_id, pt.picture AS projectteam_picture,'
							  . ' t.picture AS team_picture, t.name AS team_name, t.short_name AS team_short_name';
                              
		$res = new stdclass;
		$query_core = SMStatistic::getPlayersRankingStatisticCoreQuery($project_id, $division_id, $team_id,$query_num,$query_den);
        $db->setQuery($query_core);
        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query_core<br><pre>'.print_r($query_core->dump(),true).'</pre>'),'');
        
		$res->pagination_total = $db->loadResult();
        
        $query_core->clear('select');
        $query_core->select($query_select_details);
        $query_core->group('tp.id');
		$query_core->order('total '.(!empty($order) ? $order : SMStatistic::getParam('ranking_order', 'DESC')).' ');

		$db->setQuery($query_core, $limitstart, $limit);
        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query_core<br><pre>'.print_r($query_core->dump(),true).'</pre>'),'');
        
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

				$res->ranking[$k]->total = self::formatValue($res->ranking[$k]->total, 1, $precision);
			}
		}

		return $res;
	}
	
	function getTeamsRanking($project_id, $limit = 20, $limitstart = 0, $order = null)
	{
		$sids = SMStatistic::getQuotedSids($this->_ids);
		
		$db = &sportsmanagementHelper::getDBConnection();
		$query_num = ' SELECT SUM(ms.value) AS num, pt.id '
		       . ' FROM #__joomleague_team_player AS tp '
		       . ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
		       . ' INNER JOIN #__joomleague_match_statistic AS ms ON ms.teamplayer_id = tp.id '
		       . '   AND ms.statistic_id IN ('. implode(',', $sids) .')'
		       . ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
		       . '   AND m.published = 1 '
		       . ' WHERE pt.project_id = '. $db->Quote($project_id)
		       . '   AND tp.published = 1 '
		       . ' GROUP BY pt.id '
		       ;
		
		$query_den = ' SELECT COUNT(m.id) AS value, pt.id '
		       . ' FROM #__joomleague_project_team AS pt '
		       . ' INNER JOIN #__joomleague_match AS m ON m.projectteam1_id = pt.id OR m.projectteam2_id = pt.id'
		       . '   AND m.published = 1 '
		       . '   AND m.team1_result IS NOT NULL '
		       . ' WHERE pt.project_id = '. $db->Quote($project_id)
		       . ' GROUP BY pt.id '
		       ;
		       
		$query = ' SELECT (n.num / d.value) AS total, pt.team_id ' 
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
		$sids = SMStatistic::getQuotedSids($this->_ids);
		
		$db = &sportsmanagementHelper::getDBConnection();
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
		$sids = SMStatistic::getQuotedSids($this->_ids);
		
		$db = &sportsmanagementHelper::getDBConnection();
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

	/**
	 * SMStatisticPergame::formatValue()
	 * 
	 * @param mixed $num
	 * @param mixed $den
	 * @param mixed $precision
	 * @return
	 */
	function formatValue($num, $den, $precision)
	{
		$value = (!empty($num) && !empty($den)) ? $num / $den : 0;
		return number_format($value, $precision);
	}
}