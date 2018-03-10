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
 * SMStatisticComplexsumpergame
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class SMStatisticComplexsumpergame extends SMStatistic 
{
//also the name of the associated xml file	
	var $_name = 'complexsumpergame';
	
	var $_calculated = 1;
	
	var $_showinsinglematchreports = 0;
	
    var $_ids = 'stat_ids';
    
	/**
	 * SMStatisticComplexsumpergame::__construct()
	 * 
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();
	}
	
//	/**
//	 * SMStatisticComplexsumpergame::getSids()
//	 * 
//	 * @return
//	 */
//	function getSids()
//	{
//		$params = SMStatistic::getParams();
//		$stat_ids = explode(',', $params->get('stat_ids'));
//		if (!count($stat_ids)) {
//			JError::raiseWarning(0, JText::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id));
//			return(array(0));
//		}
//				
//		$db = sportsmanagementHelper::getDBConnection();
//		$sids = array();
//		foreach ($stat_ids as $s) {
//			$sids[] = (int)$s;
//		}		
//		return $sids;
//	}
	
	/**
	 * SMStatisticComplexsumpergame::getFactors()
	 * 
	 * @return
	 */
	function getFactors()
	{
		$params  = SMStatistic::getParams();
		$factors = explode(',', $params->get('factors'));
		$stat_ids = SMStatistic::getSids($this->_ids);
		
		if (count($stat_ids) != count($factors)) 
        {
			JError::raiseWarning(0, JText::sprintf('STAT %s/%s WRONG CONFIGURATION - BAD FACTORS COUNT', $this->_name, $this->id));
			return(array(0));
		}
				
		$sids = array();
		foreach ($factors as $s) 
        {
			$sids[] = (float)$s;
		}		
		return $sids;
	}
	
	
//	/**
//	 * SMStatisticComplexsumpergame::getQuotedSids()
//	 * 
//	 * @return
//	 */
//	function getQuotedSids()
//	{
//		$params = SMStatistic::getParams();
//		$stat_ids = explode(',', $params->get('stat_ids'));
//		if (!count($stat_ids)) {
//			JError::raiseWarning(0, JText::sprintf('STAT %s/%s WRONG CONFIGURATION', $this->_name, $this->id));
//			return(array(0));
//		}
//				
//		$db = sportsmanagementHelper::getDBConnection();
//		$sids = array();
//		foreach ($stat_ids as $s) 
//        {
//			$sids[] = $db->Quote($s);
//		}		
//		return $sids;
//	}

	function getPlayerStatsByProject($person_id, $projectteam_id = 0, $project_id = 0, $sports_type_id = 0)
	{
		$sids = SMStatistic::getSids($this->_ids);
		$factors = self::getFactors();
		
		$num = $this->getPlayerStatsByProjectForIds($person_id, $projectteam_id, $project_id, $sports_type_id, $sids, $factors);
		$den = $this->getGamesPlayedByPlayer($person_id, $projectteam_id, $project_id, $sports_type_id);
		return $this->formatValue($num, $den, $this->getPrecision());
	}

	/**
	 * Get players stats
	 * @param $team_id
	 * @param $project_id
	 * @param $position_id
	 * @return array
	 */
	function getRosterStats($team_id, $project_id, $position_id)
	{
		$sids = SMStatistic::getSids($this->_ids);
		$factors  = self::getFactors();
		$num = SMStatistic::getRosterStatsForIds($team_id, $project_id, $position_id, $sids, $factors);
		$den = SMStatistic::getGamesPlayedByProjectTeam($team_id, $project_id, $position_id);

		$precision = SMStatistic::getPrecision();
		$res = array();
		foreach (array_unique(array_merge(array_keys($num), array_keys($den))) as $person_id) 
		{
			$res[$person_id] = new stdclass();
			$res[$person_id]->person_id = $person_id;
			$n = isset($num[$person_id]->value) ? $num[$person_id]->value : 0;
			$d = isset($den[$person_id]->value) ? $den[$person_id]->value : 0;
			$res[$person_id]->value = self::formatValue($n, $d, $precision);
		}
		return $res;
	}

	/**
	 * SMStatisticComplexsumpergame::getPlayersRanking()
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
		$sids = SMStatistic::getSids($this->_ids);
		$sqids = SMStatistic::getQuotedSids($this->_ids);
		$factors  = self::getFactors();
		$app = JFactory::getApplication();
		$db = sportsmanagementHelper::getDBConnection();
		$query = JFactory::getDbo()->getQuery(true);
        
		// get all stats
        $query->select('ms.value, ms.statistic_id, tp.id AS tpid');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_statistic AS ms');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ON tp.id = ms.teamplayer_id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = tp.team_id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m ON m.id = ms.match_id');
        
//		$query  = ' SELECT ms.value, ms.statistic_id, tp.id AS tpid'
//				. ' FROM #__joomleague_match_statistic AS ms'
//				. ' INNER JOIN #__joomleague_team_player AS tp ON ms.teamplayer_id = tp.id'
//				. ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id'
//				. ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id'
//				. ' WHERE pt.project_id = '. $db->Quote($project_id);
                
        $query->where('pt.project_id = ' . $project_id);        
		if ($division_id != 0)
		{
			//$query .= ' AND pt.division_id = '. $db->Quote($division_id);
            $query->where('pt.division_id = ' . $division_id);
		}
		if ($team_id != 0)
		{
			//$query .= '   AND pt.team_id = ' . $db->Quote($team_id);
            $query->where('st.team_id = ' . $team_id);
		}
//		$query .= '   AND ms.statistic_id IN ('. implode(',', $sqids) .')'
//				. '   AND m.published = 1';

		$query->where('ms.statistic_id IN ('. implode(',', $sqids) .')');
        $query->where('m.published = 1');
        
        $db->setQuery($query);
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        
		$stats = $db->loadObjectList();
		
		$query = SMStatistic::getGamesPlayedQuery($project_id, $division_id, $team_id);
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');

		$db->setQuery($query);
		$gp = $db->loadObjectList('tpid');
		
		// now calculate per player
		$players = array();		
		// first, the numerator complex sum
		foreach ($stats as $stat)
		{
			$key = array_search($stat->statistic_id, $sids);
			if ($key !== FALSE)	
            {
				@$players[$stat->tpid] += $factors[$key]*$stat->value;
			}
		}
		// then divide by games played
		foreach ($players as $k => $value)
		{
			if (isset($gp[$k]) && $gp[$k]->played) {
				$players[$k] = $players[$k] / $gp[$k]->played;
			}
			else {
				unset($players[$k]);
			}
		}
		
		// now we reorder
		$order = (!empty($order) ? $order : $this->getParam('ranking_order', 'DESC'));
		if ($order == 'ASC') {
			asort($players);
		}
		else {
			arsort($players);
		}

		$res = new stdclass;
		$res->pagination_total = count($players);

		$players = array_slice($players, $limitstart, $limit, true);
		$ids = array_keys($players);
		
        $query->clear();
        $query->select('tp.id AS teamplayer_id, tp.person_id, tp.picture AS teamplayerpic,p.firstname, p.nickname, p.lastname, p.picture, p.country,st.team_id, pt.picture AS projectteam_picture');
        $query->select('t.picture AS team_picture,t.name AS team_name, t.short_name AS team_short_name');
        
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p ON p.id = tp.person_id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = tp.team_id ');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t ON st.team_id = t.id');
        
//		$query  = ' SELECT tp.id AS teamplayer_id, tp.person_id, tp.picture AS teamplayerpic,'
//				. ' p.firstname, p.nickname, p.lastname, p.picture, p.country,'
//				. ' pt.team_id, pt.picture AS projectteam_picture, t.picture AS team_picture,'
//				. ' t.name AS team_name, t.short_name AS team_short_name'
//				. ' FROM #__joomleague_team_player AS tp'
//				. ' INNER JOIN #__joomleague_person AS p ON p.id = tp.person_id'
//				. ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id'
//				. ' INNER JOIN #__joomleague_team AS t ON pt.team_id = t.id'
//				. ' WHERE pt.project_id = '. $db->Quote($project_id)
//				. '   AND p.published = 1'
//				. '   AND tp.id IN ('. implode(',', $ids) .')';
                
                
        $query->where('pt.project_id = ' . $project_id);
        $query->where('p.published = 1');
        $query->where('tp.id IN ('. implode(',', $ids) .')');
                
		if ($division_id != 0)
		{
			//$query .= ' AND pt.division_id = '. $db->Quote($division_id);
            $query->where('pt.division_id = ' . $division_id);
		}
		if ($team_id != 0)
		{
			//$query .= '   AND pt.team_id = ' . $db->Quote($team_id);
            $query->where('st.team_id = ' . $team_id);
		}

		$db->setQuery($query);
        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        
		$details = $db->loadObjectList('teamplayer_id');

		$res->ranking = array();
		if (!empty($details))
		{
			$precision = SMStatistic::getPrecision();
			// get ranks
			$previousval = 0;
			$currentrank = 1 + $limitstart;
			$i = 0;
			foreach ($players as $k => $value) 
			{
				$res->ranking[$i] = $details[$k];
				$res->ranking[$i]->total = $value;

				if ($value == $previousval) {
					$res->ranking[$i]->rank = $currentrank;
				}
				else {
					$res->ranking[$i]->rank = $i + 1 + $limitstart;
				}
				
				$previousval = $value;
				$currentrank = $res->ranking[$i]->rank;

				$res->ranking[$i]->total = self::formatValue($res->ranking[$i]->total, 1, $precision);

				$i++;
			}
		}
		return $res;
	}

	function getTeamsRanking($project_id, $limit = 20, $limitstart = 0, $order=null)
	{		
		$sids = SMStatistic::getSids($this->_ids);
		$sqids = SMStatistic::getQuotedSids($this->_ids);
		$factors  = self::getFactors();
		
		$db = &sportsmanagementHelper::getDBConnection();
	
		// team games
		$query = ' SELECT COUNT(m.id) AS value, pt.team_id '
		       . ' FROM #__joomleague_project_team AS pt '
		       . ' INNER JOIN #__joomleague_match AS m ON m.projectteam1_id = pt.id OR m.projectteam2_id = pt.id'
		       . '   AND m.published = 1 '
		       . '   AND m.team1_result IS NOT NULL '
		       . ' WHERE pt.project_id = '. $db->Quote($project_id)
		       . ' GROUP BY pt.id '
		       ;
		$db->setQuery($query);
		$gp = $db->loadObjectList('team_id');
				
		// get all stats
		$query = ' SELECT ms.value, ms.statistic_id, pt.team_id '
		       . ' FROM #__joomleague_match_statistic AS ms '
		       . ' INNER JOIN #__joomleague_team_player AS tp ON ms.teamplayer_id = tp.id '
		       . ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
		       . ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
		       . ' WHERE pt.project_id = '. $db->Quote($project_id)
		       . '   AND ms.statistic_id IN ('. implode(',', $sqids) .')'
		       . '   AND tp.published = 1 '
		       . '   AND m.published = 1 '
		       ;
		$db->setQuery($query);
		$stats = $db->loadObjectList();
		
		// now calculate per team
		$teams = array();		
		foreach ($stats as $stat)
		{
			$key = array_search($stat->statistic_id, $sids);
			if ($key !== FALSE)	{
				@$teams[$stat->team_id] += $factors[$key]*$stat->value;
			}
		}
		// then divide by games played
		foreach ($teams as $k => $value)
		{
			if (isset($gp[$k]) && $gp[$k]->value) {
				$teams[$k] = $teams[$k] / $gp[$k]->value;
			}
			else {
				unset($teams[$k]);
			}
		}
		
		// now we reorder
		$order = (!empty($order) ? $order : $this->getParam('ranking_order', 'DESC'));
		if ($order == 'ASC') {
			asort($teams);
		}
		else {
			arsort($teams);
		}
		
		$teams = array_slice($teams, $limitstart, $limit, true);
		
		$res = array();
		foreach ($teams as $id => $value)
		{
			$obj = new stdclass;
			$obj->team_id = $id;
			$obj->total   = $value;
			$res[] = $obj;
		}

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
		$sids = SMStatistic::getSids($this->_ids);
		$sqids = SMStatistic::getQuotedSids($this->_ids);
		$factors  = self::getFactors();
		
		$db = &sportsmanagementHelper::getDBConnection();
		
		$query = ' SELECT ms.value, ms.statistic_id '
		       . ' FROM #__joomleague_team_staff AS tp '
		       . ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
		       . ' INNER JOIN #__joomleague_match_staff_statistic AS ms ON ms.team_staff_id = tp.id '
		       . '   AND ms.statistic_id IN ('. implode(',', $sqids) .')'
		       . ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
		       . '   AND m.published = 1 '
		       . ' WHERE pt.team_id = '. $db->Quote($team_id)
		       . '   AND pt.project_id = '. $db->Quote($project_id)
		       . '   AND tp.person_id = '. $db->Quote($person_id)
		       ;
		$db->setQuery($query);
		$stats = $db->loadObjectList();
		
		$num = 0;
		foreach ($stats as $stat)
		{
			$key = array_search($stat->statistic_id, $sids);
			if ($key !== FALSE)	{
				$num += $factors[$key]*$stat->value;
			}
		}
		
		//games
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
		
		return $this->formatValue($num, $den, $this->getPrecision());;
	}
	
	function getHistoryStaffStats($person_id)
	{
		$sids = SMStatistic::getSids($this->_ids);
		$sqids = $this->getQuotedSids();
		$factors  = $this->getFactors();
		
		$db = &sportsmanagementHelper::getDBConnection();
		
		$query = ' SELECT ms.value AS value, ms.statistic_id '
		       . ' FROM #__joomleague_team_staff AS tp '
		       . ' INNER JOIN #__joomleague_project_team AS pt ON pt.id = tp.projectteam_id '
		       . ' INNER JOIN #__joomleague_project AS p ON p.id = pt.project_id '
		       . ' INNER JOIN #__joomleague_match_staff_statistic AS ms ON ms.team_staff_id = tp.id '
		       . '  AND ms.statistic_id IN ('. implode(',', $sqids) .')'
		       . ' INNER JOIN #__joomleague_match AS m ON m.id = ms.match_id '
		       . '   AND m.published = 1 '
		       . ' WHERE tp.person_id = '. $db->Quote($person_id)
		       . '   AND p.published = 1 '
		       ;
		$db->setQuery($query);
		$stats = $db->loadObjectList();	
		
		$num = 0;
		foreach ($stats as $stat)
		{
			$key = array_search($stat->statistic_id, $sids);
			if ($key !== FALSE)	{
				$num += $factors[$key]*$stat->value;
			}
		}
		
		//games
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
		
		return self::formatValue($num, $den, $this->getPrecision());;
	}

	/**
	 * SMStatisticComplexsumpergame::formatValue()
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