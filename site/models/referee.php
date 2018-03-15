<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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

/**
 * sportsmanagementModelReferee
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementModelReferee extends JModelLegacy
{
	static $projectid = 0;
	static $personid = 0;
    static $cfg_which_database = 0;

    
    /**
	 * cache for data query
	 * @var object
	 */
	var $_data = null;

	/**
	 * data array for history
	 * @var array
	 */
	static $_history = null;

	/**
	 * sportsmanagementModelReferee::__construct()
	 * 
	 * @return void
	 */
	function __construct()
	{
	   $option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
        
		parent::__construct();
		self::$projectid = JFactory::getApplication()->input->getInt('p',0);
		self::$personid = JFactory::getApplication()->input->getInt('pid',0);
        self::$cfg_which_database = JFactory::getApplication()->input->getInt( 'cfg_which_database', 0 );
        sportsmanagementModelPerson::$projectid = JFactory::getApplication()->input->getInt('p',0);
		sportsmanagementModelPerson::$personid = JFactory::getApplication()->input->getInt('pid',0);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projectid <br><pre>'.print_r(self::$projectid,true).'</pre>'),'');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' personid <br><pre>'.print_r(self::$personid,true).'</pre>'),'');
	}

	/**
	 * get person history across all projects,with team,season,position,... info
	 *
	 * @param int $person_id,linked to player_id from Person object
	 * @param int $order ordering for season and league,default is ASC ordering
	 * @param string $filter e.g. "s.name=2007/2008",default empty string
	 * @return array of objects
	 */
	function getHistory($order='ASC')
	{
	   $app = JFactory::getApplication();
       $option = JFactory::getApplication()->input->getCmd('option');
       // Create a new query object.		
	   $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
       
	   $query = $db->getQuery(true);
       
		if (empty(self::$_history))
		{
			//$personid = self::$personid;
            
            $query->select('per.id AS pid,per.firstname,per.lastname,CONCAT_WS(\':\',per.id,per.alias) AS person_slug');
            $query->select('pr.person_id,pr.project_id');
            $query->select('pos.name AS position_name');
            $query->select('p.name AS project_name,CONCAT_WS(\':\',p.id,p.alias) AS project_slug');
            $query->select('s.name AS season_name');
            $query->from('#__sportsmanagement_person AS per ');
            $query->join('INNER','#__sportsmanagement_season_person_id AS o ON per.id = o.person_id');
            $query->join('INNER','#__sportsmanagement_project_referee AS pr ON pr.person_id = o.id');
            $query->join('INNER','#__sportsmanagement_project AS p ON p.id = pr.project_id');
            $query->join('INNER','#__sportsmanagement_season AS s ON s.id = p.season_id');
            $query->join('INNER','#__sportsmanagement_league AS l ON l.id = p.league_id');
            $query->join('LEFT','#__sportsmanagement_project_position AS ppos ON pr.project_position_id = ppos.id');
            $query->join('LEFT','#__sportsmanagement_position AS pos ON ppos.position_id = pos.id');
            $query->where('per.id = '.self::$personid);
            $query->where('per.published = 1');
            
            $query->order('s.ordering ASC');
            $query->order('l.ordering ASC');
            $query->order('p.name '.$order);
			
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            
            $db->setQuery($query);
			self::$_history = $db->loadObjectList();
		}
		return self::$_history;
	}

	/**
	 * sportsmanagementModelReferee::getPresenceStats()
	 * 
	 * @param mixed $project_id
	 * @param mixed $person_id
	 * @return
	 */
	function getPresenceStats($project_id,$person_id)
	{
	   $app = JFactory::getApplication();
       $option = JFactory::getApplication()->input->getCmd('option');
//       // Create a new query object.		
	   $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
	   $query = $db->getQuery(true);
       
       $query->select('count(mr.id) AS present');
       $query->from('#__sportsmanagement_match_referee AS mr ');
       $query->join('INNER','#__sportsmanagement_match AS m ON mr.match_id=m.id');
       $query->join('INNER','#__sportsmanagement_project_referee AS pr ON pr.id=mr.project_referee_id');
       $query->where('pr.person_id = '.$personid);
       $query->where('pr.project_id = '.$project_id);
		
        $db->setQuery($query,0,1);
		$inoutstat = $db->loadResult();
		return $inoutstat;
	}

	/**
	 * sportsmanagementModelReferee::getGames()
	 * 
	 * @return
	 */
	function getGames()
	{
	   $app = JFactory::getApplication();
       $option = JFactory::getApplication()->input->getCmd('option');
       // Create a new query object.		
	   $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
	   $query = $db->getQuery(true);
       
       //$query->select('m.*');
       $query->select('m.id,m.match_date,m.projectteam1_id,m.projectteam2_id,m.team1_result,m.team2_result');
       $query->select('t1.id AS team1,t1.name AS home_name');
       $query->select('t2.id AS team2,t2.name AS away_name');
       $query->select('r.roundcode,r.project_id');
       $query->select('c1.logo_big as home_logo');
       $query->select('c2.logo_big as away_logo');
       $query->from('#__sportsmanagement_match AS m ');
       $query->join('INNER','#__sportsmanagement_match_referee AS mr ON mr.match_id = m.id');
       $query->join('INNER','#__sportsmanagement_project_referee AS pr ON pr.id = mr.project_referee_id');
       $query->join('INNER','#__sportsmanagement_season_person_id AS o ON o.id = pr.person_id');
       
       $query->join('INNER','#__sportsmanagement_round as r ON m.round_id = r.id');
       
       $query->join('INNER','#__sportsmanagement_project_team AS pt1 ON m.projectteam1_id = pt1.id');
       $query->join('INNER','#__sportsmanagement_season_team_id as st1 ON st1.id = pt1.team_id ');
       $query->join('INNER','#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
       $query->join('INNER','#__sportsmanagement_club AS c1 ON t1.club_id = c1.id  ');
       
       $query->join('INNER','#__sportsmanagement_project_team AS pt2 ON m.projectteam2_id = pt2.id');
       $query->join('INNER','#__sportsmanagement_season_team_id as st2 ON st2.id = pt2.team_id ');
       $query->join('INNER','#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');
       $query->join('INNER','#__sportsmanagement_club AS c2 ON t2.club_id = c2.id  ');
       
       $query->join('INNER','#__sportsmanagement_project AS p ON p.id = r.project_id');
       
       $query->where('o.person_id = '.self::$personid);
       $query->where('r.project_id = '.self::$projectid);
       $query->where('m.published = 1');
       
       $query->order('m.match_date');

       //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');             
                    
		$db->setQuery($query);
		return $db->loadObjectList();
	}

}
?>