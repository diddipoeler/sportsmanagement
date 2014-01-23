<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
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
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

//require_once( JLG_PATH_SITE . DS . 'models' . DS . 'project.php' );

class sportsmanagementModelClubPlan extends JModel
{
	var $clubid = 0;
	var $project_id = 0;
	var $club = null;
	var $startdate = null;
	var $enddate = null;
	var $awaymatches = null;
	var $homematches = null;
	
	function __construct()
	{
		parent::__construct();
		$this->clubid=JRequest::getInt("cid",0);
		$this->project_id=JRequest::getInt("p",0);
		$this->setStartDate(JRequest::getVar("startdate", $this->startdate,'request','string'));
		$this->setEndDate(JRequest::getVar("enddate",$this->enddate,'request','string'));
	}

/**
 * 	function getClub()
 * 	{
 * 		if (is_null($this->club))
 * 		{
 * 			if ($this->clubid > 0)
 * 			{
 * 				$this->club =& $this->getTable('Club','Table');
 * 				$this->club->load($this->clubid);
 * 			}
 * 		}
 * 		return $this->club;
 * 	}
 */

	function getTeams()
	{
		$teams=array(0);
		if ($this->clubid > 0)
		{
			$database = JFactory::getDBO();

			$query=' SELECT id,'
			. ' name as team_name,'
			. ' short_name as team_shortcut,'
			. ' info as team_description '
			. ' FROM #__joomleague_team '
			. ' WHERE club_id='.(int) $this->clubid;

			$this->_db->setQuery($query);
			$teams=$this->_db->loadObjectList();
		}
		return $teams;
	}

	function getStartDate()
	{
	   $mainframe = JFactory::getApplication();
       //$mainframe->enqueueMessage(JText::_('Model clubplan startdate vorher -> '.'<pre>'.print_r($this->startdate,true).'</pre>' ),'');
	
    	$config = sportsmanagementModelProject::getTemplateConfig("clubplan");
		if (empty($this->startdate))
		{
			$dayz = $config['days_before'];
			//$dayz=6;
			$prevweek = mktime(0,0,0,date("m"),date("d")- $dayz,date("y"));
			$this->startdate = date("Y-m-d",$prevweek);
		}
		if($config['use_project_start_date']=="1") {
			$project = sportsmanagementModelProject::getProject();
			$this->startdate = $project->start_date;
		}
        //$mainframe->enqueueMessage(JText::_('Model clubplan startdate nachher -> '.'<pre>'.print_r($this->startdate,true).'</pre>' ),'');
		return $this->startdate;
	}

	function getEndDate()
	{
	   $mainframe = JFactory::getApplication();
       //$mainframe->enqueueMessage(JText::_('Model clubplan enddate vorher -> '.'<pre>'.print_r($this->enddate,true).'</pre>' ),'');
		if (empty($this->enddate))
		{
			$config = sportsmanagementModelProject::getTemplateConfig("clubplan");
			$dayz = $config['days_after'];
			//$dayz=6;
			$nextweek = mktime(0,0,0,date("m"),date("d")+ $dayz,date("y"));
			$this->enddate = date("Y-m-d",$nextweek);
		}
        //$mainframe->enqueueMessage(JText::_('Model clubplan enddate vorher -> '.'<pre>'.print_r($this->enddate,true).'</pre>' ),'');
		return $this->enddate;
	}

	function setStartDate($date)
	{
	   $mainframe = JFactory::getApplication();
		// should be in proper sql format
		if (strtotime($date)) {
			$this->startdate=strftime("%Y-%m-%d",strtotime($date));
		}
		else {
			$this->startdate=null;
		}
	}

	function setEndDate($date)
	{
	   $mainframe = JFactory::getApplication();
		// should be in proper sql format
		if (strtotime($date)) {
			$this->enddate=strftime("%Y-%m-%d",strtotime($date));
		}
		else {
			$this->enddate=null;
		}
	}

	function getAllMatches($orderBy='ASC')
	{
		$result=array();
		$teams=$this->getTeams();
		$startdate=$this->getStartDate();
		$enddate=$this->getEndDate();

		if (is_null($teams)) {
			return null;
		}

		$query=' SELECT m.*,DATE_FORMAT(m.time_present,"%H:%i") time_present,'
		. ' p.name        AS project_name,'
		. ' p.id          AS project_id,'
		. ' r.id          AS roundid,'
		. ' r.roundcode   AS roundcode,'
		. ' r.name		  AS roundname,'		
		. ' t1.id         AS team1_id,'
		. ' t2.id         AS team2_id,'
		. ' t1.name       AS tname1,'
		. ' t2.name       AS tname2,'
		. ' t1.short_name AS tname1_short,'
		. ' t2.short_name AS tname2_short,'
		. ' t1.middle_name AS tname1_middle,'
		. ' t2.middle_name AS tname2_middle,'
		. ' t1.club_id    AS club1_id,'
		. ' t2.club_id    AS club2_id,'
		. ' p.id          AS prid,'
		. ' l.name        AS l_name,'
		. ' playground.name AS pl_name,'
		. ' c1.logo_small AS home_logo_small,'
		. ' c2.logo_small AS away_logo_small , tj1.division_id, t1.club_id as t1club_id, t2.club_id as t2club_id,'
		
		. ' d.name AS division_name, d.shortname AS division_shortname, d.parent_id AS parent_division_id,'

		. ' CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\',p.id,p.alias) ELSE p.id END AS project_slug,'
		. '	CASE WHEN CHAR_LENGTH(d.alias) THEN CONCAT_WS(\':\',d.id,d.alias) ELSE d.id END AS division_slug,'
		. ' CASE WHEN CHAR_LENGTH(c1.alias) THEN CONCAT_WS(\':\',c1.id,c1.alias) ELSE c1.id END AS club1_slug,'
		. ' CASE WHEN CHAR_LENGTH(c2.alias) THEN CONCAT_WS(\':\',c2.id,c2.alias) ELSE c2.id END AS club2_slug,'
		. ' CASE WHEN CHAR_LENGTH(t1.alias) THEN CONCAT_WS(\':\',t1.id,t1.alias) ELSE t1.id END AS team1_slug,'
		. ' CASE WHEN CHAR_LENGTH(t2.alias) THEN CONCAT_WS(\':\',t2.id,t2.alias) ELSE t2.id END AS team2_slug'
		
		. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team tj1 ON tj1.id=m.projectteam1_id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team tj2 ON tj2.id=m.projectteam2_id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team t1 ON t1.id=tj1.team_id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team t2 ON t2.id=tj2.team_id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id=tj1.project_id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_league l ON p.league_id=l.id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_club c1 ON c1.id=t1.club_id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_round r ON m.round_id=r.id '
		. ' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'club c2 ON c2.id=t2.club_id '
		. ' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_playground AS playground ON playground.id=m.playground_id '
		. ' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_division d ON d.id=tj1.division_id'
		. ' WHERE p.published=1 '
		. ' AND (m.match_date BETWEEN '.$this->_db->Quote($startdate).' AND '.$this->_db->Quote($enddate).')';
		if($this->project_id>0) {
			$query .=' AND p.id='. $this->_db->Quote($this->project_id);
		}
		if($this->clubid >0) {
			$query .=' AND (t1.club_id='.$this->_db->Quote($this->clubid);
			$query .=' OR t2.club_id='.$this->_db->Quote($this->clubid) . ')';
		}
		$query .='  '
		. ' AND m.published=1 '
		. ' ORDER BY m.match_date '.$orderBy;
		;
		$this->_db->setQuery($query);
		$this->allmatches = $this->_db->loadObjectList();
		return $this->allmatches;
	}

	function getHomeMatches($orderBy='ASC')
	{
		$result=array();
		$teams=$this->getTeams();
		$startdate=$this->getStartDate();
		$enddate=$this->getEndDate();

		if (is_null($teams)) {
			return null;
		}

		$query=' SELECT m.*,DATE_FORMAT(m.time_present,"%H:%i") time_present,'
		. ' p.name        AS project_name,'
		. ' p.id          AS project_id,'
		. ' r.id          AS roundid,'
		. ' r.roundcode   AS roundcode,'
		. ' r.name		  AS roundname,'			
		. ' t1.id         AS team1_id,'
		. ' t2.id         AS team2_id,'
		. ' t1.name       AS tname1,'
		. ' t2.name       AS tname2,'
		. ' t1.short_name AS tname1_short,'
		. ' t2.short_name AS tname2_short,'
		. ' t1.middle_name AS tname1_middle,'
		. ' t2.middle_name AS tname2_middle,'
		. ' t1.club_id    AS club1_id,'
		. ' t2.club_id    AS club2_id,'
		. ' p.id          AS prid,'
		. ' l.name        AS l_name,'		
		. ' playground.name AS pl_name,'
		. ' c1.logo_small AS home_logo_small,'
		. ' c2.logo_small AS away_logo_small , tj1.division_id, t1.club_id as t1club_id, t2.club_id as t2club_id,'
		
		. ' d.name AS division_name, d.shortname AS division_shortname, d.parent_id AS parent_division_id,'

		. ' CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\',p.id,p.alias) ELSE p.id END AS project_slug,'
		. '	CASE WHEN CHAR_LENGTH(d.alias) THEN CONCAT_WS(\':\',d.id,d.alias) ELSE d.id END AS division_slug,'
		. ' CASE WHEN CHAR_LENGTH(c1.alias) THEN CONCAT_WS(\':\',c1.id,c1.alias) ELSE c1.id END AS club1_slug,'
		. ' CASE WHEN CHAR_LENGTH(c2.alias) THEN CONCAT_WS(\':\',c2.id,c2.alias) ELSE c2.id END AS club2_slug,'
		. ' CASE WHEN CHAR_LENGTH(t1.alias) THEN CONCAT_WS(\':\',t1.id,t1.alias) ELSE t1.id END AS team1_slug,'
		. ' CASE WHEN CHAR_LENGTH(t2.alias) THEN CONCAT_WS(\':\',t2.id,t2.alias) ELSE t2.id END AS team2_slug'
		
		. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team tj1 ON tj1.id=m.projectteam1_id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team tj2 ON tj2.id=m.projectteam2_id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team t1 ON t1.id=tj1.team_id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team t2 ON t2.id=tj2.team_id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id=tj1.project_id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_league l ON p.league_id=l.id '				
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_club c1 ON c1.id=t1.club_id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_round r ON m.round_id=r.id '
		. ' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_club c2 ON c2.id=t2.club_id '
		. ' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'playground AS playground ON playground.id=m.playground_id '		
		. ' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_division d ON d.id=tj1.division_id'
		. ' WHERE p.published=1 '
		. ' AND (m.match_date BETWEEN '.$this->_db->Quote($startdate).' AND '.$this->_db->Quote($enddate).')';
		if($this->project_id>0) {
			$query .=' AND p.id='. $this->_db->Quote($this->project_id);
		}
		if($this->clubid >0) {
			$query .=' AND t1.club_id='.$this->_db->Quote($this->clubid);
		}
		$query .='  '
		. ' AND m.published=1 '
		. ' ORDER BY m.match_date '.$orderBy;
		;
		$this->_db->setQuery($query);
		$this->homematches = $this->_db->loadObjectList();
		return $this->homematches;
	}

	function getAwayMatches($orderBy='ASC')
	{
		$result=array();
		$teams=$this->getTeams();
		$startdate=$this->getStartDate();
		$enddate=$this->getEndDate();

		if (is_null($teams)) {
			return null;
		}


		$query=' SELECT m.*,DATE_FORMAT(m.time_present,"%H:%i") time_present,'
		. ' p.name        AS project_name,'
		. ' p.id          AS project_id,'
		. ' r.id          AS roundid,'
		. ' r.roundcode   AS roundcode,'
		. ' r.name		  AS roundname,'			
		. ' t1.id         AS team1_id,'
		. ' t2.id         AS team2_id,'
		. ' t1.name       AS tname1,'
		. ' t2.name       AS tname2,'
		. ' t1.short_name AS tname1_short,'
		. ' t2.short_name AS tname2_short,'
		. ' t1.middle_name AS tname1_middle,'
		. ' t2.middle_name AS tname2_middle,'
		. ' t1.club_id    AS club1_id,'
		. ' t2.club_id    AS club2_id,'
		. ' p.id          AS prid,'
		. ' l.name        AS l_name,'		
		. ' playground.name AS pl_name,'
		. ' c1.logo_small AS home_logo_small,'
		. ' c2.logo_small AS away_logo_small , tj1.division_id, t1.club_id as t1club_id, t2.club_id as t2club_id,'
		
		. ' d.name AS division_name, d.shortname AS division_shortname, d.parent_id AS parent_division_id,'

		. ' CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\',p.id,p.alias) ELSE p.id END AS project_slug,'
		. '	CASE WHEN CHAR_LENGTH(d.alias) THEN CONCAT_WS(\':\',d.id,d.alias) ELSE d.id END AS division_slug,'
		. ' CASE WHEN CHAR_LENGTH(c1.alias) THEN CONCAT_WS(\':\',c1.id,c1.alias) ELSE c1.id END AS club1_slug,'
		. ' CASE WHEN CHAR_LENGTH(c2.alias) THEN CONCAT_WS(\':\',c2.id,c2.alias) ELSE c2.id END AS club2_slug,'
		. ' CASE WHEN CHAR_LENGTH(t1.alias) THEN CONCAT_WS(\':\',t1.id,t1.alias) ELSE t1.id END AS team1_slug,'
		. ' CASE WHEN CHAR_LENGTH(t2.alias) THEN CONCAT_WS(\':\',t2.id,t2.alias) ELSE t2.id END AS team2_slug'
		
		. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team tj1 ON tj1.id=m.projectteam1_id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team tj2 ON tj2.id=m.projectteam2_id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team t1 ON t1.id=tj1.team_id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team t2 ON t2.id=tj2.team_id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id=tj1.project_id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_league l ON p.league_id=l.id '				
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_club c2 ON c2.id=t2.club_id '
		. ' INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_round r ON m.round_id=r.id '
		. ' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_club c1 ON c1.id=t1.club_id '
		. ' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'playground AS playground ON playground.id=m.playground_id '
		. ' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_division d ON d.id=tj1.division_id'
		. ' WHERE p.published=1 '
		. ' AND (m.match_date BETWEEN '.$this->_db->Quote($startdate).' AND '.$this->_db->Quote($enddate).')';

		if($this->project_id>0) {
			$query .=' AND p.id='. $this->_db->Quote($this->project_id);
		}
		if($this->clubid >0) {
			$query .=' AND t2.club_id='.$this->_db->Quote($this->clubid);
		}
		$arrMatchIds = array();
		$arrMatchIds[] = 0; //no home matches
		foreach ($this->homematches as $game) {
			$arrMatchIds[] = $game->id;
		}
		$query .= ' AND NOT m.id in ('.implode(",", $arrMatchIds).')';
		$query .= ' AND m.published=1 '
				. ' ORDER BY m.match_date '.$orderBy
		;

		$this->_db->setQuery($query);
		$this->awaymatches = $this->_db->loadObjectList();

		return $this->awaymatches ;
	}

	function getMatchReferees($matchID)
	{
		$query=' SELECT	p.id,'
		      .' p.firstname,'
		      .' p.lastname,'
		      .' mp.project_position_id,'
		    .' CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\',p.id,p.alias) ELSE p.id END AS person_slug '
		      .' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee AS mp '
		      .' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS pref ON mp.project_referee_id=pref.id '
		      .' INNER JOIN	#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p ON pref.person_id=p.id '
		      .' WHERE mp.match_id='.(int)$matchID
		      .' AND p.published = 1';

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	function getClubIconHtmlSimple($logo_small,$country,$type=1,$with_space=0)
	{
		if ($type==1)
		{
			$params=array();
			$params["align"]="top";
			$params["border"]=0;
			if ($with_space==1)
			{
				$params["style"]="padding:1px;";
			}
			if ($logo_small=="")
			{
				$logo_small = sportsmanagementHelper::getDefaultPlaceholder("clublogosmall");
			}

			return JHTML::image($logo_small,"",$params);
		}
		elseif ($type==2 && isset($country))
		{
			return Countries::getCountryFlag($team->country);
		}
	}

}
?>