<?php
/** SportsManagement ein Programm zur Verwaltung f�r Sportarten
 * @version   1.0.05
 * @file      winpergame.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage statistics
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'statistics'.DS.'base.php');


/**
 * SMStatisticWinpergame
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class SMStatisticWinpergame extends SMStatistic 
{
//also the name of the associated xml file
	var $_name = 'winpergame';
	
	var $_calculated = 1;
	
	var $_showinsinglematchreports = 0;
	
	/**
	 * SMStatisticWinpergame::__construct()
	 * 
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();
	}
		
	/**
	 * (non-PHPdoc)
	 * @see administrator/components/com_sportsmanagement/statistics/JLGStatistic#getPlayerStatsByProject($person_id, $project_id)
	 */
	function getPlayerStatsByProject($person_id, $projectteam_id = 0, $project_id = 0, $sports_type_id = 0)
	{		
		$db = sportsmanagementHelper::getDBConnection();
        $app = JFactory::getApplication();
        $query = JFactory::getDbo()->getQuery(true);
        
        $query->select('COUNT(m.id) AS value, tp.person_id');
        $query->from('#__sportsmanagement_season_team_person_id AS tp');
        $query->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
        $query->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
             
		if ($sports_type_id)
		{
		  $query->join('INNER','#__sportsmanagement_project AS p ON p.id = pt.project_id');
          $query->where('p.sports_type_id = ' . $sports_type_id);
		}
        
        $query->join('INNER','#__sportsmanagement_match_player AS mp ON mp.teamplayer_id = tp.id');
        $query->join('INNER','#__sportsmanagement_match AS m ON m.id = mp.match_id AND m.published = 1');
        
        $query->where('CASE WHEN pt.id = m.projectteam1_id THEN m.team1_result > m.team2_result ELSE m.team1_result < m.team2_result END');
        $query->where('tp.person_id = '. $person_id);
        $query->where('tp.published = 1');
        
		if ($projectteam_id)
		{
               $query->where('pt.id = ' . $projectteam_id);
		}

		if ($project_id)
		{
               $query->where('pt.project_id = ' . $project_id);
		}

        $query->group('tp.id');
		$db->setQuery($query);
       
		$num = $db->loadResult();
		
		$den = SMStatistic::getGamesPlayedByPlayer($person_id, $projectteam_id, $project_id, $sports_type_id);
	
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
		$db = sportsmanagementHelper::getDBConnection();
        $app = JFactory::getApplication();
        $query = JFactory::getDbo()->getQuery(true);

		// Determine the wins per game for each project team player
        $query->select('COUNT(m.id) AS value, tp.person_id');
        $query->from('#__sportsmanagement_season_team_person_id AS tp');
        $query->join('INNER','#__sportsmanagement_match_player AS mp ON mp.teamplayer_id = tp.id ');
        $query->join('INNER','#__sportsmanagement_project_position AS ppos ON ppos.id = tp.project_position_id ');
        $query->join('INNER','#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id ');
        $query->join('INNER','#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $query->join('INNER','#__sportsmanagement_match AS m ON m.id = mp.match_id AND m.published = 1 ');
        $query->where('st.team_id = '. $team_id);
        $query->where('pt.project_id = ' . $project_id);
        $query->where('ppos.position_id = '. $position_id);
        $query->where('CASE WHEN pt.id = m.projectteam1_id THEN m.team1_result > m.team2_result ELSE m.team1_result < m.team2_result END');
        $query->group('tp.id');
                   
        $db->setQuery($query);
            
            
		$num = $db->loadObjectList('person_id');

		// Determine the wins per game for the project team
        $query->clear('select');
        $query->clear('group');
        $query->select('COUNT(m.id) AS value');
        $db->setQuery($query);
        
		$num['totals'] = new stdclass;
		$num['totals']->person_id = 'totals';
		$num['totals']->value = $db->loadResult();

		$den = SMStatistic::getGamesPlayedByProjectTeam($team_id, $project_id, $position_id);
		$precision = SMStatistic::getPrecision();
		
		$res = array();
		foreach (array_unique(array_merge(array_keys($num), array_keys($den))) as $person_id) 
		{
			$n = isset($num[$person_id]) ? $num[$person_id]->value : 0;
			$d = isset($den[$person_id]) ? $den[$person_id]->value : 0;
			$res[$person_id] = new stdclass();
			$res[$person_id]->person_id = $person_id;
			$res[$person_id]->value = self::formatValue($n, $d, $precision);
		}
		return $res;
	}

	/**
	 * Get players stats
	 * @param $team_id
	 * @param $project_id
	 * @return array
	 */
	function getRosterTotalStats($team_id, $project_id, $position_id = 0)
	{		
		$db = &sportsmanagementHelper::getDBConnection();
		$query = ' SELECT COUNT(m.id) AS value '
		       . ' FROM #__joomleague_project_team AS pt '
		       . ' INNER JOIN #__joomleague_match AS m ON m.projectteam1_id = pt.id OR m.projectteam2_id = pt.id'
		       . ' WHERE pt.team_id = '. $db->Quote($team_id)
		       . '   AND pt.project_id = '. $db->Quote($project_id)
		       . '   AND CASE WHEN pt.id = m.projectteam1_id THEN m.team1_result > m.team2_result ELSE m.team1_result < m.team2_result END'
		       . '   AND m.published = 1 '
		       . ' GROUP BY pt.team_id '
		       ;
		$db->setQuery($query);
		$num = $db->loadResult();

		// team games
		$query = ' SELECT COUNT(m.id) AS value '
		       . ' FROM #__joomleague_project_team AS pt '
		       . ' INNER JOIN #__joomleague_match AS m ON m.projectteam1_id = pt.id OR m.projectteam2_id = pt.id'
		       . ' WHERE pt.team_id = '. $db->Quote($team_id)
		       . '   AND pt.project_id = '. $db->Quote($project_id)
		       . '   AND m.published = 1 '
		       . '   AND m.team1_result IS NOT NULL '
		       . ' GROUP BY pt.id '
		       ;
		$db->setQuery($query);
		$den = $db->loadResult();
		
		return $this->formatValue($num, $den, $this->getPrecision());
	}
	

	function getPlayersRanking($project_id, $division_id, $team_id, $limit = 20, $limitstart = 0, $order=null)
	{		
		$db = &sportsmanagementHelper::getDBConnection();
		$query_num = ' SELECT COUNT(m.id) AS num, tp.id AS tpid, tp.person_id '
			. ' FROM #__joomleague_team_player AS tp '
			. ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
			. ' INNER JOIN #__joomleague_match_player AS mp ON mp.teamplayer_id = tp.id '
			. ' INNER JOIN #__joomleague_match AS m ON m.id = mp.match_id '
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
		$query_num .= '   AND CASE WHEN tp.projectteam_id = m.projectteam1_id THEN m.team1_result > m.team2_result'
					. '            ELSE m.team1_result < m.team2_result END'
					. ' GROUP BY tp.id ';

		$query_den = $this->getGamesPlayedQuery($project_id, $division_id, $team_id);

		$query_select_count = ' SELECT COUNT(DISTINCT tp.id) as count';

		$query_select_details	= ' SELECT (n.num / d.played) AS total, n.person_id, 1 as rank,'
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
		$query_end_details	= ' ORDER BY total '.(!empty($order) ? $order : $this->getParam('ranking_order', 'DESC')).' ';
		
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
		
	/**
	 * SMStatisticWinpergame::getTeamsRanking()
	 * 
	 * @param integer $project_id
	 * @param integer $limit
	 * @param integer $limitstart
	 * @param mixed $order
	 * @return
	 */
	function getTeamsRanking($project_id = 0, $limit = 20, $limitstart = 0, $order = null)
	{		
		$db = sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true);
        $query_num = $db->getQuery(true);
        $query_den = $db->getQuery(true);
        
        $query_num->select('COUNT(m.id) AS num, pt.id');
        $query_num->from('#__sportsmanagement_project_team AS pt');
        $query_num->join('INNER','#__sportsmanagement_match AS m ON m.projectteam1_id = pt.id OR m.projectteam2_id = pt.id AND m.published = 1');
        $query_num->where('pt.project_id = ' . $project_id);
        $query_num->where('CASE WHEN pt.id = m.projectteam1_id THEN m.team1_result > m.team2_result ELSE m.team1_result < m.team2_result END');
        $query_num->group('pt.id');
        
        $query_den->select('COUNT(m.id) AS value, pt.id');
        $query_den->from('#__sportsmanagement_project_team AS pt');
        $query_den->join('INNER','#__sportsmanagement_match AS m ON m.projectteam1_id = pt.id OR m.projectteam2_id = pt.id AND m.published = 1 AND m.team1_result IS NOT NULL');
        $query_den->where('pt.project_id = ' . $project_id);
        $query_den->group('pt.id');

	    $query->select('(n.num / d.value) AS total, st.team_id');
        $query->from('#__sportsmanagement_project_team AS pt');
        $query->join('INNER','#__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
        $query->join('INNER','#__sportsmanagement_team AS t ON st.team_id = t.id ');
        $query->join('INNER','('.$query_num.') AS n ON n.id = pt.id ');
        $query->join('INNER','('.$query_den.') AS d ON d.id = pt.id ');
        $query->where('pt.project_id = '. $projectid);
        $query->order('total '.(!empty($order) ? $order : $this->getParam('ranking_order', 'DESC')).' ');        
	
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
	
	/**
	 * SMStatisticWinpergame::getStaffStats()
	 * 
	 * @param mixed $person_id
	 * @param mixed $team_id
	 * @param mixed $project_id
	 * @return
	 */
	function getStaffStats($person_id, $team_id, $project_id)
	{		
		$db = sportsmanagementHelper::getDBConnection();
        //$query = $db->getQuery(true);
        
        $select = 'COUNT(m.id) AS value, tp.person_id ';
        $query = SMStatistic::getStaffStatsQuery($person_id, $team_id, $project_id, '',$select,FALSE,'match_staff');
        $query->where('CASE WHEN tp.projectteam_id = m.projectteam1_id THEN m.team1_result > m.team2_result ELSE m.team1_result < m.team2_result END');

		$db->setQuery($query);
		$num = $db->loadResult();
		
        $query->clear();
        $select = 'COUNT(ms.id) AS value, tp.person_id ';
        $query = SMStatistic::getStaffStatsQuery($person_id, $team_id, $project_id, '',$select,FALSE,'match_staff');
        
		$db->setQuery($query);
		$den = $db->loadResult();
	
		return $this->formatValue($num, $den, $this->getPrecision());
	}
	

	/**
	 * SMStatisticWinpergame::getHistoryStaffStats()
	 * 
	 * @param mixed $person_id
	 * @return
	 */
	function getHistoryStaffStats($person_id)
	{		
		$db = sportsmanagementHelper::getDBConnection();
        //$query = $db->getQuery(true);
        
        $select = 'COUNT(m.id) AS value, tp.person_id ';
        $query = SMStatistic::getStaffStatsQuery($person_id, 0, 0, $sqids,$select,TRUE,'match_staff');
        $query->where('CASE WHEN tp.projectteam_id = m.projectteam1_id THEN m.team1_result > m.team2_result ELSE m.team1_result < m.team2_result END');
        
		$db->setQuery($query);
		$num = $db->loadResult();
		
        $query->clear();
        $select = 'COUNT(ms.id) AS value, tp.person_id ';
        $query = SMStatistic::getStaffStatsQuery($person_id, 0, 0, $sqids,$select,TRUE,'match_staff');
        
		$db->setQuery($query);
		$den = $db->loadResult();
	
		return $this->formatValue($num, $den, $this->getPrecision());
	}

	/**
	 * SMStatisticWinpergame::formatValue()
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