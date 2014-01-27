<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
//require_once('person.php');

class sportsmanagementModelReferee extends JModel
{
	/**
	 * cache for data query
	 * @var object
	 */
	var $_data=null;

	/**
	 * data array for history
	 * @var array
	 */
	var $_history=null;

	function __construct()
	{
		parent::__construct();
		$this->projectid=JRequest::getInt('p',0);
		$this->personid=JRequest::getInt('pid',0);
	}

	function &getReferee()
	{
	   $mainframe = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
		if (is_null($this->_data))
		{
			$query='	SELECT	p.*,
								CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\',p.id,p.alias) ELSE p.id END AS slug,
								pr.id,
								pr.notes AS prnotes,
								pos.name AS position_name,
								pr.picture
						FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS pr
						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p ON p.id=pr.person_id
						LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON ppos.id=pr.project_position_id
						LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON pos.id=ppos.position_id						
						WHERE pr.project_id='.$this->_db->Quote($this->projectid).' 
						  AND p.published = 1 
						  AND pr.person_id='.$this->_db->Quote($this->personid);
			$this->_db->setQuery($query);
			$this->_data=$this->_db->loadObject();
		}
		return $this->_data;
	}

	/**
	 * get person history across all projects,with team,season,position,... info
	 *
	 * @param int $person_id,linked to player_id from Person object
	 * @param int $order ordering for season and league,default is ASC ordering
	 * @param string $filter e.g. "s.name=2007/2008",default empty string
	 * @return array of objects
	 */
	function &getHistory($order='ASC')
	{
	   $mainframe = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
		if (empty($this->_history))
		{
			$personid=$this->personid;
			$query='	SELECT	per.id AS pid,
								pr.person_id,
								pr.project_id,
								pos.name AS position_name,
								per.firstname,
								per.lastname,
								p.name AS project_name,
								s.name AS season_name,
								CASE WHEN CHAR_LENGTH(per.alias) THEN CONCAT_WS(\':\',per.id,per.alias) ELSE per.id END AS person_slug,
								CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\',p.id,p.alias) ELSE p.id END AS project_slug
						FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS per
						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS pr ON pr.person_id=per.id
						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id=pr.project_id
						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_season AS s ON s.id=p.season_id
						INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_league AS l ON l.id=p.league_id
						LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS ppos ON pr.project_position_id=ppos.id
						LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_position AS pos ON ppos.position_id=pos.id
						WHERE per.id='.$this->_db->Quote($personid).' AND per.published = 1 ORDER BY s.ordering ASC,l.ordering ASC,p.name ASC ';
			$this->_db->setQuery($query);
			$this->_history=$this->_db->loadObjectList();
		}
		return $this->_history;
	}

	function getPresenceStats($project_id,$person_id)
	{
	   $mainframe = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
		$query='	SELECT	count(mr.id) AS present
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee AS mr
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m ON mr.match_id=m.id
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS pr ON pr.id=mr.project_referee_id
					WHERE pr.person_id='.$this->_db->Quote((int)$person_id).' AND pr.project_id='.$this->_db->Quote((int)$project_id);
		$this->_db->setQuery($query,0,1);
		$inoutstat=$this->_db->loadResult();
		return $inoutstat;
	}

	function getGames()
	{
	   $mainframe = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       // Create a new query object.		
	   $db = JFactory::getDBO();
	   $query = $db->getQuery(true);
       
		$query='	SELECT	m.*,
							t1.id AS team1,
							t2.id AS team2,
							r.roundcode,
							r.project_id
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match AS m
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee AS mr ON mr.match_id=m.id
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS pr ON pr.id=mr.project_referee_id
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_round r ON m.round_id=r.id
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt1 ON m.projectteam1_id=pt1.id
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t1 ON t1.id=pt1.team_id
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt2 ON m.projectteam2_id=pt2.id
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t2 ON t2.id=pt2.team_id
					INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p ON p.id=r.project_id
					WHERE	pr.person_id='.$this->_db->Quote($this->personid).'
							AND r.project_id='.$this->_db->Quote($this->projectid).'
							AND m.published=1
					ORDER BY m.match_date ';
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

}
?>