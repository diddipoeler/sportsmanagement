<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      basic.php
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
 * SMStatisticBasic
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class SMStatisticBasic extends SMStatistic 
{
/**
 * also the name of the associated xml file
 */	
	var $_name = 'basic';
	
	/**
	 * SMStatisticBasic::getMatchPlayerStat()
	 * 
	 * @param mixed $gamemodel
	 * @param mixed $teamplayer_id
	 * @return
	 */
	function getMatchPlayerStat(&$gamemodel, $teamplayer_id)
	{
		$gamestats = $gamemodel->getPlayersStats();
		$res = 0;
		if (isset($gamestats[$teamplayer_id][$this->id])) {
			$res = $gamestats[$teamplayer_id][$this->id];
		}
		return self::formatValue($res, SMStatistic::getPrecision());
	}

	function getMatchPlayersStats($match_id)
	{
		$db = &sportsmanagementHelper::getDBConnection();
		
		$query = ' SELECT SUM(ms.value) AS value, tp.id '
		       . ' FROM #__joomleague_team_player AS tp '
		       . ' INNER JOIN #__joomleague_match_statistic AS ms ON ms.teamplayer_id = tp.id '
		       . ' WHERE ms.statistic_id = '. $db->Quote($this->id)
		       . '   AND ms.match_id = '. $db->Quote($match_id)
		       . '   AND tp.published = 1 '
		       . ' GROUP BY tp.id '
		       ;
		$db->setQuery($query);
		$res = $db->loadObjectList('id');

		if (!empty($res))
		{
			$precision = SMStatistic::getPrecision();
			foreach ($res as $player)
			{
				$player->value = self::formatValue($player->value, $precision);
			}
		}
		return $res;
	}
	
	/**
	 * SMStatisticBasic::getPlayerStatsByGame()
	 * 
	 * @param mixed $teamplayer_ids
	 * @param mixed $project_id
	 * @return
	 */
	function getPlayerStatsByGame($teamplayer_ids, $project_id)
	{
		$res = SMStatistic::getPlayerStatsByGameForIds($teamplayer_ids, $project_id, array($this->id));
		if (!empty($res))
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
	 * SMStatisticBasic::getPlayerStatsByProject()
	 * 
	 * @param mixed $person_id
	 * @param integer $projectteam_id
	 * @param integer $project_id
	 * @param integer $sports_type_id
	 * @return
	 */
	function getPlayerStatsByProject($person_id, $projectteam_id = 0, $project_id = 0, $sports_type_id = 0)
	{
		$res = SMStatistic::getPlayerStatsByProjectForIds($person_id, $projectteam_id, $project_id, $sports_type_id, array($this->id));
		return self::formatValue($res, $this->getPrecision());
	}

	/**
	 * Get players stats
	 * @param $team_id
	 * @param $project_id
	 * @return array
	 */
	function getRosterStats($team_id, $project_id, $position_id)
	{
		$res = SMStatistic::GetRosterStatsForIds($team_id, $project_id, $position_id, array($this->id));
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
	 * SMStatisticBasic::getPlayersRanking()
	 * 
	 * @param integer $project_id
	 * @param integer $division_id
	 * @param integer $team_id
	 * @param integer $limit
	 * @param integer $limitstart
	 * @param mixed $order
	 * @return
	 */
	function getPlayersRanking($project_id = 0, $division_id = 0, $team_id = 0, $limit = 20, $limitstart = 0, $order = null)
	{		
		$app = JFactory::getApplication();
		$db = sportsmanagementHelper::getDBConnection();
		$query_core = JFactory::getDbo()->getQuery(true);
		
		$query_select_count = ' SELECT COUNT(DISTINCT tp.id) as count';

		$query_select_details	= ' SELECT SUM(ms.value) AS total,'
								. ' tp.id AS teamplayer_id, tp.person_id, tp.picture AS teamplayerpic,'
								. ' p.firstname, p.nickname, p.lastname, p.picture, p.country,'
								. ' pt.team_id, pt.picture AS projectteam_picture,'
								. ' t.picture AS team_picture, t.name AS team_name, t.short_name AS team_short_name';

		
        
        $query_core->select($query_select_count);
        $query_core->from('#__sportsmanagement_season_team_person_id AS tp');
        $query_core->join('INNER','#__sportsmanagement_person AS p ON p.id = tp.person_id ');
        $query_core->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
        $query_core->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $query_core->join('INNER','#__sportsmanagement_team AS t ON st.team_id = t.id');
        
        $query_core->join('INNER','#__sportsmanagement_match_statistic AS ms ON ms.teamplayer_id = tp.id AND ms.statistic_id = '. $db->Quote($this->id));
        $query_core->join('INNER','#__sportsmanagement_match AS m ON m.id = ms.match_id AND m.published = 1');
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
        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query_core<br><pre>'.print_r($query_core->dump(),true).'</pre>'),'');
        
		$res->pagination_total = $db->loadResult();
        
        $query_core->clear('select');
        $query_core->select($query_select_details);
        $query_core->group('tp.id');
        $query_core->order('total '.(!empty($order) ? $order : $this->getParam('ranking_order', 'DESC')).', tp.id '); 

		$db->setQuery($query_core, $limitstart, $limit);
        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query_core<br><pre>'.print_r($query_core->dump(),true).'</pre>'),'');
        
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
	 * SMStatisticBasic::getTeamsRanking()
	 * 
	 * @param integer $project_id
	 * @param integer $limit
	 * @param integer $limitstart
	 * @param mixed $order
	 * @param string $select
	 * @param integer $statistic_id
	 * @return
	 */
	function getTeamsRanking($project_id = 0, $limit = 20, $limitstart = 0, $order = null, $select = '', $statistic_id = 0)
	{		
		$app = JFactory::getApplication();
		$db = sportsmanagementHelper::getDBConnection();
		
        $select = 'SUM(ms.value) AS total, st.team_id ';
        $statistic_id = $this->id;
        $query = SMStatistic::getTeamsRanking($project_id, $limit, $limitstart, $order, $select,$statistic_id) ;
	
        $query->order('total '.(!empty($order) ? $order : $this->getParam('ranking_order', 'DESC')).', tp.id ');
        
        $db->setQuery($query, $limitstart, $limit);
        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query_core<br><pre>'.print_r($query->dump(),true).'</pre>'),'');

try{        
		$res = $db->loadObjectList();
} catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
}
		
		if ($res)
		{
			$precision = SMStatistic::getPrecision();
			// get ranks
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

	/**
	 * SMStatisticBasic::getMatchStaffStat()
	 * 
	 * @param mixed $gamemodel
	 * @param mixed $team_staff_id
	 * @return
	 */
	function getMatchStaffStat(&$gamemodel, $team_staff_id)
	{
		$gamestats = $gamemodel->getMatchStaffStats();
		$res = 0;
		if (isset($gamestats[$team_staff_id][$this->id])) {
			$res = $gamestats[$team_staff_id][$this->id];
		}
		return self::formatValue($res, $this->getPrecision());
	}
	
	/**
	 * SMStatisticBasic::getStaffStats()
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
	$db = sportsmanagementHelper::getDBConnection();
		$select = 'SUM(ms.value) AS value ';
        $query = SMStatistic::getStaffStatsQuery($person_id, $team_id, $project_id, $this->id,$select,FALSE);
              
		$db->setQuery($query, 0, 1);
        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        
		$res = $db->loadResult();
		return self::formatValue($res, $this->getPrecision());
	}

	/**
	 * SMStatisticBasic::getHistoryStaffStats()
	 * 
	 * @param mixed $person_id
	 * @return
	 */
	function getHistoryStaffStats($person_id)
	{
		$option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection();
		
        $select = 'SUM(ms.value) AS value ';
        $query = SMStatistic::getStaffStatsQuery($person_id, 0, 0, $this->id,$select,TRUE);
        
		$db->setQuery($query);
        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        
		$res = $db->loadResult();
		return self::formatValue($res, SMStatistic::getPrecision());
	}

	/**
	 * SMStatisticBasic::formatValue()
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
