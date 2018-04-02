<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      teaminfo.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage teaminfo
 */
 
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');

/**
 * sportsmanagementModelTeamInfo
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelTeamInfo extends JModelLegacy {

    var $project = null;
    static $projectid = 0;
    static $projectteamid = 0;
    static $teamid = 0;
    static $team = null;
    static $club = null;
    static $cfg_which_database = 0;

    /**
     * sportsmanagementModelTeamInfo::__construct()
     * 
     * @return void
     */
    function __construct() {
        // Reference global application object
        $app = JFactory::getApplication();

        self::$projectid = JFactory::getApplication()->input->get('p', 0, 'INT');
        self::$projectteamid = JFactory::getApplication()->input->get('ptid', 0, 'INT');
        self::$teamid = JFactory::getApplication()->input->get('tid', 0, 'INT');
        self::$cfg_which_database = JFactory::getApplication()->input->get('cfg_which_database', 0, 'INT');
        sportsmanagementModelProject::$projectid = self::$projectid;
        parent::__construct();
    }

    /**
     * sportsmanagementModelTeamInfo::updateHits()
     * 
     * @param integer $teamid
     * @param integer $inserthits
     * @return void
     */
    public static function updateHits($teamid = 0, $inserthits = 0) {
        $option = JFactory::getApplication()->input->getCmd('option');
        $app = JFactory::getApplication();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if ($inserthits) {
            $query->update($db->quoteName('#__sportsmanagement_team'))->set('hits = hits + 1')->where('id = ' . $teamid);

            $db->setQuery($query);

            $result = $db->execute();
            $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect	 
        }
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');     
    }

    /**
     * Method to return a team trainingdata array
     * @param int projectid
     * @return	array
     */
    public static function getTrainigData($projectid) {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database);
        $query = $db->getQuery(true);

//       $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($query->dump(),true).'</pre>' ),'');
//       $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($query->dump(),true).'</pre>' ),'');

        $trainingData = array();
        if (self::$projectteamid == 0) {
            $projectTeamID = sportsmanagementModelProject::getprojectteamID(self::$teamid, self::$cfg_which_database);
        } else {
            $projectTeamID = self::$projectteamid;
        }

        $query->select('*');
        $query->from('#__sportsmanagement_team_trainingdata');
        //$query->where('project_id = '. $projectid);  
        //$query->where('project_team_id = '. $projectTeamID);
        $query->where('team_id = ' . self::$teamid);
        $query->order('dayofweek ASC');

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' dump'.'<pre>'.print_r($query->dump(),true).'</pre>' ),'');

        $db->setQuery($query);
        $trainingData = $db->loadObjectList();
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        return $trainingData;
    }

    /**
     * sportsmanagementModelTeamInfo::getTeamByProject()
     * 
     * @param integer $inserthits
     * @return
     */
    public static function getTeamByProject($inserthits = 0) {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database);
        $query = $db->getQuery(true);
        $starttime = microtime();

        self::updateHits(self::$teamid, $inserthits);

        if (is_null(self::$team)) {
            $query->select('t.*,t.name AS tname, t.website AS team_website, t.email AS team_email, pt.*, pt.notes AS notes, pt.info AS info');
            $query->select('t.extended AS teamextended, t.picture AS team_picture, pt.picture AS projectteam_picture,pt.cr_picture AS cr_projectteam_picture, c.*');
            $query->select('CONCAT_WS( \':\', t.id, t.alias ) AS slug ');
            $query->from('#__sportsmanagement_team t ');
            $query->join('LEFT', '#__sportsmanagement_club c ON t.club_id = c.id ');
            $query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = t.id');
            $query->join('INNER', '#__sportsmanagement_project_team pt ON pt.team_id = st.id ');

            $query->where('pt.project_id = ' . self::$projectid);

            if (self::$projectteamid > 0) {
                $query->where('pt.id = ' . self::$projectteamid);
            } else {
                $query->where('t.id = ' . self::$teamid);
            }

            $db->setQuery($query);

            if (COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO) {
                $app->enqueueMessage(JText::_(get_class($this) . ' ' . __FUNCTION__ . ' ' . __LINE__ . '<br><pre>' . print_r($query->dump(), true) . '</pre>'), 'Notice');
                $app->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . ' Ausfuehrungszeit query<br><pre>' . print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()), true) . '</pre>'), 'Notice');
            }

            self::$team = $db->loadObject();

            if (!self::$team && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
                $my_text = 'getErrorMsg -><pre>' . print_r($db->getErrorMsg(), true) . '</pre>';
                $my_text .= 'dump -><pre>' . print_r($query->dump(), true) . '</pre>';
                sportsmanagementHelper::setDebugInfoText(__METHOD__, __FUNCTION__, __CLASS__, __LINE__, $my_text);
                //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
            } elseif (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
                $my_text = 'getErrorMsg -><pre>' . print_r($db->getErrorMsg(), true) . '</pre>';
                $my_text .= 'dump -><pre>' . print_r($query->dump(), true) . '</pre>';
                sportsmanagementHelper::setDebugInfoText(__METHOD__, __FUNCTION__, __CLASS__, __LINE__, $my_text);
                //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            }

//            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
//            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.'<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        return self::$team;
    }

    /**
     * get club info
     * @return object
     */
    public static function getClub() {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database);
        $query = $db->getQuery(true);
        $starttime = microtime();

        if (is_null(self::$club)) {
            $team = self::getTeamByProject();
            if ($team->club_id > 0) {
                $query->select('*');
                $query->select('CONCAT_WS( \':\', id, alias ) AS slug');
                $query->from('#__sportsmanagement_club');
                $query->where('id = ' . $team->club_id);

                $db->setQuery($query);

                if (COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO) {
                    $app->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . ' Ausfuehrungszeit query<br><pre>' . print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()), true) . '</pre>'), 'Notice');
                }

                self::$club = $db->loadObject();
            }
        }
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        return self::$club;
    }

    /**
     * sportsmanagementModelTeamInfo::getSeasons()
     * 
     * @param mixed $config
     * @param integer $history
     * @return
     */
    public static function getSeasons($config, $history = 0) {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database);
        $query = $db->getQuery(true);
        $starttime = microtime();



        $seasons = array();
        if ($config['ordering_teams_seasons'] == "1") {
            $season_ordering = "DESC";
        } else {
            $season_ordering = "ASC";
        }

        $query->select('pt.id as ptid, pt.team_id as season_team_id, pt.picture, pt.info, pt.project_id AS projectid');
        $query->select('p.name as projectname,p.season_id,p.current_round, pt.division_id');
        $query->select('s.name as season');
        $query->select('t.id as team_id');
        $query->select('st.picture as season_picture');
        $query->select('l.name as league, t.extended as teamextended');
        $query->select('CONCAT_WS( \':\', p.id, p.alias ) AS project_slug');
        $query->select('CONCAT_WS( \':\', t.id, t.alias ) AS team_slug');
        $query->select('CONCAT_WS( \':\', d.id, d.alias ) AS division_slug');
        $query->select('CONCAT_WS( \':\', r.id, r.alias ) AS round_slug');
        $query->select('d.name AS division_name');
        $query->select('d.shortname AS division_short_name');
        $query->from('#__sportsmanagement_project_team AS pt ');
        $query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.id = pt.team_id');
        $query->join('INNER', '#__sportsmanagement_team AS t ON t.id = st.team_id ');
        $query->join('LEFT', '#__sportsmanagement_division AS d ON d.id = pt.division_id ');
        $query->join('INNER', '#__sportsmanagement_project AS p ON p.id = pt.project_id ');
        $query->join('INNER', '#__sportsmanagement_season AS s ON s.id = p.season_id ');
        $query->join('INNER', '#__sportsmanagement_league AS l ON l.id = p.league_id ');
        $query->join('LEFT', '#__sportsmanagement_round AS r ON r.id = p.current_round ');

        if ($history) {
            $query->where('t.id = ' . self::$teamid);
        } else {
            if (self::$projectteamid > 0) {
                $query->where('pt.id = ' . self::$projectteamid);
            } else {
                $query->where('t.id = ' . self::$teamid);
            }
        }

        $query->order('s.name ' . $season_ordering);

        $db->setQuery($query);

        if (COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO) {
            $app->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . ' Ausfuehrungszeit query<br><pre>' . print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()), true) . '</pre>'), 'Notice');
        }

        $seasons = $db->loadObjectList();

        if (!$seasons && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            $my_text = 'getErrorMsg -><pre>' . print_r($db->getErrorMsg(), true) . '</pre>';
            $my_text .= 'dump -><pre>' . print_r($query->dump(), true) . '</pre>';
            sportsmanagementHelper::setDebugInfoText(__METHOD__, __FUNCTION__, __CLASS__, __LINE__, $my_text);
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
        } elseif (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            $my_text = 'getErrorMsg -><pre>' . print_r($db->getErrorMsg(), true) . '</pre>';
            $my_text .= 'dump -><pre>' . print_r($query->dump(), true) . '</pre>';
            $my_text .= 'seasons -><pre>' . print_r($seasons, true) . '</pre>';
            $my_text .= 'history -><pre>' . print_r($history, true) . '</pre>';
            sportsmanagementHelper::setDebugInfoText(__METHOD__, __FUNCTION__, __CLASS__, __LINE__, $my_text);

//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' seasons<br><pre>'.print_r($seasons,true).'</pre>'),'');
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' history<br><pre>'.print_r($history,true).'</pre>'),'');
        }

        foreach ($seasons as $k => $season) {
            $ranking = self::getTeamRanking($season->projectid, $season->division_id);
            if (!empty($ranking)) {
                $seasons[$k]->rank = $ranking['rank'];
                $seasons[$k]->leaguename = self::getLeague($season->projectid);
                $seasons[$k]->season_picture = $season->season_picture;
                $seasons[$k]->ptid = $season->ptid;
                $seasons[$k]->games = $ranking['games'];
                $seasons[$k]->points = $ranking['points'];
                $seasons[$k]->series = $ranking['series'];
                $seasons[$k]->goals = $ranking['goals'];
                $seasons[$k]->playercnt = self::getPlayerCount($season->projectid, $season->ptid, $season->season_id);
                $seasons[$k]->playermeanage = self::getPlayerMeanAge($season->projectid, $season->ptid, $season->season_id);
                $seasons[$k]->market_value = self::getPlayerMarketValue($season->projectid, $season->ptid, $season->season_id);
            } else {
                $seasons[$k]->rank = 0;
                $seasons[$k]->leaguename = '';
                $seasons[$k]->season_picture = $season->season_picture;
                $seasons[$k]->ptid = $season->ptid;
                $seasons[$k]->games = 0;
                $seasons[$k]->points = 0;
                $seasons[$k]->series = 0;
                $seasons[$k]->goals = 0;
                $seasons[$k]->playercnt = 0;
                $seasons[$k]->playermeanage = 0;
                $seasons[$k]->market_value = 0;
            }
        }
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        return $seasons;
    }

    /**
     * sportsmanagementModelTeamInfo::getPlayerMarketValue()
     * 
     * @param mixed $projectid
     * @param mixed $projectteamid
     * @param mixed $season_id
     * @return
     */
    public static function getPlayerMarketValue($projectid, $projectteamid, $season_id) {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database);
        $query = $db->getQuery(true);
        $starttime = microtime();

        $player = array();
        $query->select('SUM(stp.market_value) AS market_value');
        $query->from('#__sportsmanagement_season_team_person_id AS stp');
        $query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = stp.team_id');
        $query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');

        $query->where('pt.project_id = ' . $projectid);
        $query->where('pt.id = ' . $projectteamid);
        $query->where('stp.published = 1');
        //$query->where('ps.published = 1');

        $db->setQuery($query);

        if (COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO) {
            $app->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . ' Ausfuehrungszeit query<br><pre>' . print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()), true) . '</pre>'), 'Notice');
        }

        $player = $db->loadResult();
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        return $player;
    }

    /**
     * get ranking of current team in a project
     * @param int projectid
     * @param int division_id
     * @return array
     */
    public static function getTeamRanking($projectid, $division_id) {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database);
        $query = $db->getQuery(true);

        if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            $my_text = 'projectid -><pre>' . print_r($projectid, true) . '</pre>';
            //$my_text .= 'getErrorMsg -><pre>'.print_r($db->getErrorMsg(),true).'</pre>'; 
            sportsmanagementHelper::setDebugInfoText(__METHOD__, __FUNCTION__, __CLASS__, __LINE__, $my_text);
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projectid<br><pre>'.print_r($projectid,true).'</pre>'),'');
        }

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projectid<br><pre>'.print_r($projectid,true).'</pre>'),'');

        $rank = array();

        sportsmanagementModelProject::setProjectID($projectid, self::$cfg_which_database);
        $project = sportsmanagementModelProject::getProject(self::$cfg_which_database);
        $tableconfig = sportsmanagementModelProject::getTemplateConfig("ranking", self::$cfg_which_database);
        $ranking = JSMRanking::getInstance($project, self::$cfg_which_database);
        $ranking->setProjectId($project->id, self::$cfg_which_database);
        $temp_ranking = $ranking->getRanking(0, sportsmanagementModelProject::getCurrentRound(null, self::$cfg_which_database), $division_id, self::$cfg_which_database);
        foreach ($temp_ranking as $ptid => $value) {
//			if ($value->getPtid() == self::$projectteamid)
//			{
//				$rank['rank']   = $value->rank;
//				$rank['games']  = $value->cnt_matches;
//				$rank['points'] = $value->getPoints();
//				$rank['series'] = $value->cnt_won . "/" . $value->cnt_draw . "/" . $value->cnt_lost;
//				$rank['goals']  = $value->sum_team1_result . ":" . $value->sum_team2_result;
//				break;
//			} 
            if ($value->getTeamId() == self::$teamid) {
                $rank['rank'] = $value->rank;
                $rank['games'] = $value->cnt_matches;
                $rank['points'] = $value->getPoints();
                $rank['series'] = $value->cnt_won . "/" . $value->cnt_draw . "/" . $value->cnt_lost;
                $rank['goals'] = $value->sum_team1_result . ":" . $value->sum_team2_result;
                break;
            }
        }

        //}

        if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            $my_text = 'rank -><pre>' . print_r($rank, true) . '</pre>';
            //$my_text .= 'getErrorMsg -><pre>'.print_r($db->getErrorMsg(),true).'</pre>'; 
            sportsmanagementHelper::setDebugInfoText(__METHOD__, __FUNCTION__, __CLASS__, __LINE__, $my_text);
            //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' rank<br><pre>'.print_r($rank,true).'</pre>'),'');
        }
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

        return $rank;
    }

    /**
     * sportsmanagementModelTeamInfo::getMergeClubs()
     * 
     * @param mixed $merge_clubs
     * @return
     */
    function getMergeClubs($merge_clubs) {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database);
        $query = $db->getQuery(true);
        $query->select('*, CONCAT_WS( \':\', id, alias ) AS slug');
        $query->from('#__sportsmanagement_club');
        $query->where('id IN ( ' . $merge_clubs . ' )');

        $db->setQuery($query);
        $result = $db->loadObjectList();

        if (!$result && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            $my_text = 'dump -><pre>' . print_r($query->dump(), true) . '</pre>';
            $my_text .= 'getErrorMsg -><pre>' . print_r($db->getErrorMsg(), true) . '</pre>';
            sportsmanagementHelper::setDebugInfoText(__METHOD__, __FUNCTION__, __CLASS__, __LINE__, $my_text);
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
        } elseif (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            $my_text = 'dump -><pre>' . print_r($query->dump(), true) . '</pre>';
            //$my_text .= 'getErrorMsg -><pre>'.print_r($db->getErrorMsg(),true).'</pre>'; 
            sportsmanagementHelper::setDebugInfoText(__METHOD__, __FUNCTION__, __CLASS__, __LINE__, $my_text);
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        return $result;
    }

    /**
     * gets name of league associated to project
     * @param int $projectid
     * @return string
     */
    public static function getLeague($projectid) {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database);
        $query = $db->getQuery(true);
        $query->select('l.name AS league');
        $query->from('#__sportsmanagement_project AS p');
        $query->join('INNER', '#__sportsmanagement_league AS l ON l.id = p.league_id');
        $query->where('p.id =' . $projectid);

//		$query = 'SELECT l.name AS league FROM #__sportsmanagement_project AS p, #__sportsmanagement_league AS l WHERE p.id=' . $projectid . ' AND l.id=p.league_id ';

        $db->setQuery($query, 0, 1);
        $league = $db->loadResult();

        if (!$league && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            $my_text = 'dump -><pre>' . print_r($query->dump(), true) . '</pre>';
            $my_text .= 'getErrorMsg -><pre>' . print_r($db->getErrorMsg(), true) . '</pre>';
            sportsmanagementHelper::setDebugInfoText(__METHOD__, __FUNCTION__, __CLASS__, __LINE__, $my_text);
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
        } elseif (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            $my_text = 'dump -><pre>' . print_r($query->dump(), true) . '</pre>';
            //$my_text .= 'getErrorMsg -><pre>'.print_r($db->getErrorMsg(),true).'</pre>'; 
            sportsmanagementHelper::setDebugInfoText(__METHOD__, __FUNCTION__, __CLASS__, __LINE__, $my_text);
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect


        return $league;
    }

    /**
     * sportsmanagementModelTeamInfo::getLeagueRankOverviewDetail()
     * 
     * @param mixed $seasonsranking
     * @return
     */
    public static function getLeagueRankOverviewDetail($seasonsranking) {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');

        $leaguesoverviewdetail = array();

        foreach ($seasonsranking as $season) {
            $temp = new stdClass();
            $temp->match = 0;
            $temp->won = 0;
            $temp->draw = 0;
            $temp->loss = 0;
            $temp->goalsfor = 0;
            $temp->goalsagain = 0;
            $leaguesoverviewdetail[$season->league] = $temp;
        }

// $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' seasonsranking<br><pre>'.print_r($seasonsranking,true).'</pre>'),'');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' leaguesoverviewdetail<br><pre>'.print_r($leaguesoverviewdetail,true).'</pre>'),'');

        foreach ($seasonsranking as $season) {
            $leaguesoverviewdetail[$season->league]->match += $season->games;
            $teile = explode("/", $season->series);
            $leaguesoverviewdetail[$season->league]->won += $teile[0];

            if (array_key_exists('1', $teile)) {
                $leaguesoverviewdetail[$season->league]->draw += $teile[1];
            }
            if (array_key_exists('2', $teile)) {
                $leaguesoverviewdetail[$season->league]->loss += $teile[2];
            }
            $teile = explode(":", $season->goals);
            $leaguesoverviewdetail[$season->league]->goalsfor += $teile[0];

            if (array_key_exists('1', $teile)) {
                $leaguesoverviewdetail[$season->league]->goalsagain += $teile[1];
            }
        }

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' leaguesoverviewdetail<br><pre>'.print_r($leaguesoverviewdetail,true).'</pre>'),'');

        return $leaguesoverviewdetail;
    }

    /**
     * sportsmanagementModelTeamInfo::getLeagueRankOverview()
     * 
     * @param mixed $seasonsranking
     * @return
     */
    public static function getLeagueRankOverview($seasonsranking) {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');

        if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            $my_text = 'seasonsranking -><pre>' . print_r($seasonsranking, true) . '</pre>';
            //$my_text .= 'getErrorMsg -><pre>'.print_r($db->getErrorMsg(),true).'</pre>'; 
            sportsmanagementHelper::setDebugInfoText(__METHOD__, __FUNCTION__, __CLASS__, __LINE__, $my_text);
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' seasonsranking<br><pre>'.print_r($seasonsranking,true).'</pre>'),'');    
        }


        $leaguesoverview = array();

        foreach ($seasonsranking as $season) {

            if (isset($leaguesoverview[$season->league][(int) $season->rank])) {
                $leaguesoverview[$season->league][(int) $season->rank] += 1;
            } else {
                $leaguesoverview[$season->league][(int) $season->rank] = 0;
            }
        }

        ksort($leaguesoverview);

        foreach ($leaguesoverview as $key => $value) {
            ksort($leaguesoverview[$key]);
        }

        return $leaguesoverview;
    }

    /**
     * Get total number of players assigned to a team
     * @param int projectid
     * @param int projectteamid
     * @return int
     */
    public static function getPlayerMeanAge($projectid, $projectteamid, $season_id) {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database);
        $query = $db->getQuery(true);

        //$player = array();
        $meanage = 0;
        $countplayer = 0;
        $age = 0;

        $query->select('ps.*');
        $query->from('#__sportsmanagement_person AS ps');
        $query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON tp.person_id = ps.id');
        $query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id AND st.season_id = tp.season_id');
        $query->join('INNER', '#__sportsmanagement_project_team AS pt ON st.id = pt.team_id');

        $query->where('pt.project_id =' . $projectid);
        $query->where('pt.id =' . $projectteamid);
        $query->where('tp.published = 1');
        $query->where('ps.published = 1');


        $db->setQuery($query);
        $players = $db->loadObjectList();

        if (!$players && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            $my_text = 'dump -><pre>' . print_r($query->dump(), true) . '</pre>';
            $my_text .= 'getErrorMsg -><pre>' . print_r($db->getErrorMsg(), true) . '</pre>';
            sportsmanagementHelper::setDebugInfoText(__METHOD__, __FUNCTION__, __CLASS__, __LINE__, $my_text);
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getErrorMsg<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' dump<br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
        } elseif (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            $my_text = 'dump -><pre>' . print_r($query->dump(), true) . '</pre>';
            //$my_text .= 'getErrorMsg -><pre>'.print_r($db->getErrorMsg(),true).'</pre>'; 
            sportsmanagementHelper::setDebugInfoText(__METHOD__, __FUNCTION__, __CLASS__, __LINE__, $my_text);
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' dump<br><pre>'.print_r($query->dump(),true).'</pre>'),'');    
        }

        foreach ($players as $player) {
            if ($player->birthday != '0000-00-00') {
                $age += sportsmanagementHelper::getAge($player->birthday, $player->deathday);
                $countplayer++;
            }
        }

        // diddipoeler
        // damit kein fehler hochkommt: Warning: Division by zero
        if ($age != 0) {
            $meanage = round($age / $countplayer, 2);
        }
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        return $meanage;
    }

    /**
     * Get total number of players assigned to a team
     * @param int projectid
     * @param int projectteamid
     * @return int
     */
    public static function getPlayerCount($projectid, $projectteamid, $season_id) {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database);
        $query = $db->getQuery(true);

        $player = array();
        $query->select('COUNT(*) AS playercnt');
        $query->from('#__sportsmanagement_person AS ps');
        $query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON tp.person_id = ps.id');
        $query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id AND st.season_id = tp.season_id');
        $query->join('INNER', '#__sportsmanagement_project_team AS pt ON st.id = pt.team_id');

        $query->where('pt.project_id =' . $projectid);
        $query->where('pt.id =' . $projectteamid);
        $query->where('tp.season_id =' . $season_id);
        //$query->where('tp.published = 1');
        $query->where('ps.published = 1');


        $db->setQuery($query);
        $player = $db->loadResult();

        if (!$player && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            $my_text = 'dump -><pre>' . print_r($query->dump(), true) . '</pre>';
            $my_text .= 'getErrorMsg -><pre>' . print_r($db->getErrorMsg(), true) . '</pre>';
            sportsmanagementHelper::setDebugInfoText(__METHOD__, __FUNCTION__, __CLASS__, __LINE__, $my_text);
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
        } elseif (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            $my_text = 'dump -><pre>' . print_r($query->dump(), true) . '</pre>';
            //$my_text .= 'getErrorMsg -><pre>'.print_r($db->getErrorMsg(),true).'</pre>'; 
            sportsmanagementHelper::setDebugInfoText(__METHOD__, __FUNCTION__, __CLASS__, __LINE__, $my_text);
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        }
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

        return $player;
    }

    /**
     * sportsmanagementModelTeamInfo::hasEditPermission()
     * 
     * @param mixed $task
     * @return
     */
    function hasEditPermission($task = null) {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        //check for ACL permsission and project admin/editor
        $allowed = parent::hasEditPermission($task);
        $user = JFactory::getUser();
        if ($user->id > 0 && !$allowed) {
            // Check if user is the projectteam admin
            $team = self::getTeamByProject();
            if ($user->id == $team->admin) {
                $allowed = true;
            }
        }
        return $allowed;
    }

}

?>
