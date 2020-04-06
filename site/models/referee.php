<?php 
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       referee.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage referee
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementModelReferee
 * 
 * @package 
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelReferee extends BaseDatabaseModel
{
    static $projectid = 0;
    static $personid = 0;
    static $cfg_which_database = 0;

    
    /**
     * cache for data query
     *
     * @var object
     */
    var $_data = null;

    /**
     * data array for history
     *
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
          $option = Factory::getApplication()->input->getCmd('option');
        $app = Factory::getApplication();
        
        parent::__construct();
        self::$projectid = Factory::getApplication()->input->getInt('p', 0);
        self::$personid = Factory::getApplication()->input->getInt('pid', 0);
        self::$cfg_which_database = Factory::getApplication()->input->getInt('cfg_which_database', 0);
        sportsmanagementModelPerson::$projectid = Factory::getApplication()->input->getInt('p', 0);
        sportsmanagementModelPerson::$personid = Factory::getApplication()->input->getInt('pid', 0);
        
    }

    /**
     * get person history across all projects,with team,season,position,... info
     *
     * @param  int    $person_id,linked to player_id from Person object
     * @param  int    $order            ordering for season and league,default is ASC ordering
     * @param  string $filter           e.g. "s.name=2007/2008",default empty string
     * @return array of objects
     */
    function getHistory($order='ASC')
    {
          $app = Factory::getApplication();
          $option = Factory::getApplication()->input->getCmd('option');
          // Create a new query object.		
          $db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
       
          $query = $db->getQuery(true);
       
        if (empty(self::$_history)) {
            //$personid = self::$personid;
            
            $query->select('per.id AS pid,per.firstname,per.lastname,CONCAT_WS(\':\',per.id,per.alias) AS person_slug');
            $query->select('pr.person_id,pr.project_id');
            $query->select('pos.name AS position_name');
            $query->select('p.name AS project_name,CONCAT_WS(\':\',p.id,p.alias) AS project_slug');
            $query->select('s.name AS season_name');
            $query->from('#__sportsmanagement_person AS per ');
            $query->join('INNER', '#__sportsmanagement_season_person_id AS o ON per.id = o.person_id');
            $query->join('INNER', '#__sportsmanagement_project_referee AS pr ON pr.person_id = o.id');
            $query->join('INNER', '#__sportsmanagement_project AS p ON p.id = pr.project_id');
            $query->join('INNER', '#__sportsmanagement_season AS s ON s.id = p.season_id');
            $query->join('INNER', '#__sportsmanagement_league AS l ON l.id = p.league_id');
            $query->join('LEFT', '#__sportsmanagement_project_position AS ppos ON pr.project_position_id = ppos.id');
            $query->join('LEFT', '#__sportsmanagement_position AS pos ON ppos.position_id = pos.id');
            $query->where('per.id = '.self::$personid);
            $query->where('per.published = 1');
            
            $query->order('s.ordering ASC');
            $query->order('l.ordering ASC');
            $query->order('p.name '.$order);
           
            $db->setQuery($query);
            self::$_history = $db->loadObjectList();
        }
        return self::$_history;
    }

    /**
     * sportsmanagementModelReferee::getPresenceStats()
     * 
     * @param  mixed $project_id
     * @param  mixed $person_id
     * @return
     */
    function getPresenceStats($project_id,$person_id)
    {
          $app = Factory::getApplication();
          $option = Factory::getApplication()->input->getCmd('option');
        //       // Create a new query object.		
          $db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
          $query = $db->getQuery(true);
       
          $query->select('count(mr.id) AS present');
          $query->from('#__sportsmanagement_match_referee AS mr ');
          $query->join('INNER', '#__sportsmanagement_match AS m ON mr.match_id=m.id');
          $query->join('INNER', '#__sportsmanagement_project_referee AS pr ON pr.id=mr.project_referee_id');
          $query->where('pr.person_id = '.$personid);
          $query->where('pr.project_id = '.$project_id);
        
        $db->setQuery($query, 0, 1);
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
          $app = Factory::getApplication();
          $option = Factory::getApplication()->input->getCmd('option');
          // Create a new query object.		
          $db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
          $query = $db->getQuery(true);
       
          //$query->select('m.*');
          $query->select('m.id,m.match_date,m.projectteam1_id,m.projectteam2_id,m.team1_result,m.team2_result');
          $query->select('t1.id AS team1,t1.name AS home_name');
          $query->select('t2.id AS team2,t2.name AS away_name');
          $query->select('r.roundcode,r.project_id');
          $query->select('c1.logo_big as home_logo');
          $query->select('c2.logo_big as away_logo');
          $query->from('#__sportsmanagement_match AS m ');
          $query->join('INNER', '#__sportsmanagement_match_referee AS mr ON mr.match_id = m.id');
          $query->join('INNER', '#__sportsmanagement_project_referee AS pr ON pr.id = mr.project_referee_id');
          $query->join('INNER', '#__sportsmanagement_season_person_id AS o ON o.id = pr.person_id');
       
          $query->join('INNER', '#__sportsmanagement_round as r ON m.round_id = r.id');
       
          $query->join('INNER', '#__sportsmanagement_project_team AS pt1 ON m.projectteam1_id = pt1.id');
          $query->join('INNER', '#__sportsmanagement_season_team_id as st1 ON st1.id = pt1.team_id ');
          $query->join('INNER', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
          $query->join('INNER', '#__sportsmanagement_club AS c1 ON t1.club_id = c1.id  ');
       
          $query->join('INNER', '#__sportsmanagement_project_team AS pt2 ON m.projectteam2_id = pt2.id');
          $query->join('INNER', '#__sportsmanagement_season_team_id as st2 ON st2.id = pt2.team_id ');
          $query->join('INNER', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');
          $query->join('INNER', '#__sportsmanagement_club AS c2 ON t2.club_id = c2.id  ');
       
          $query->join('INNER', '#__sportsmanagement_project AS p ON p.id = r.project_id');
       
          $query->where('o.person_id = '.self::$personid);
          $query->where('r.project_id = '.self::$projectid);
          $query->where('m.published = 1');
       
          $query->order('m.match_date');
                   
        $db->setQuery($query);
        return $db->loadObjectList();
    }

}
?>
