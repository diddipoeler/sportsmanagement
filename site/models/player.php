<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
 *
* @version    1.0.05
* @file       player.php
* @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license    GNU General Public License version 2 or later; see LICENSE.txt
* @subpackage player
*/

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementModelPlayer
 *
 * @package 
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelPlayer extends BaseDatabaseModel
{

    static $projectid = 0;
    static $personid = 0;
    static $teamplayerid = 0;

    /**
     * data array for player history
     *
     * @var array
     */
    static $_playerhistory = null;
    static $_playerhistorystaff = null;
    static $_teamplayers = null;
    static $_inproject = null;
    static $cfg_which_database = 0;

    /**
     * sportsmanagementModelPlayer::__construct()
     *
     * @return
     */
    function __construct()
    {
        // Reference global application object
        $app = Factory::getApplication();

        parent::__construct();
        self::$projectid = Factory::getApplication()->input->get('p', 0, 'INT');
        self::$personid = Factory::getApplication()->input->get('pid', 0, 'INT');
        self::$teamplayerid = Factory::getApplication()->input->get('pt', 0, 'INT');
        self::$cfg_which_database = Factory::getApplication()->input->get('cfg_which_database', 0, 'INT');
        sportsmanagementModelProject::$cfg_which_database = self::$cfg_which_database;
        $getDBConnection = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
        parent::setDbo($getDBConnection);
    }

    /**
     * sportsmanagementModelPlayer::getTeamPlayers()
     *
     * @param  integer $cfg_which_database
     * @return
     */
    function getTeamPlayers($cfg_which_database = 0)
    {
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
        $query = $db->getQuery(true);

      
        $query->select('tp.*');
        $query->select('pt.project_id,pt.team_id,pt.id as projectteam_id,pt.picture as team_picture');
        $query->select('pos.name AS position_name');
        $query->select('ppos.position_id,pos.picture AS position_image');
        $query->select('rinjuryfrom.round_date_first as injury_date,rinjuryfrom.name as rinjury_from');
        $query->select('rinjuryto.round_date_last as injury_end,rinjuryto.name as rinjury_to');
        $query->select('rsuspfrom.round_date_first as suspension_date,rsuspfrom.name as rsusp_from');
        $query->select('rsuspto.round_date_last as suspension_end,rsuspto.name as rsusp_to');
        $query->select('rawayfrom.round_date_first as away_date,rawayfrom.name as raway_from');
        $query->select('rawayto.round_date_last as away_end,rawayto.name as raway_to');
        $query->from('#__sportsmanagement_season_team_person_id AS tp ');
        $query->join('INNER', '#__sportsmanagement_person AS pe ON pe.id = tp.person_id');
        $query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id and st.season_id = tp.season_id');
        $query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $query->join('INNER', '#__sportsmanagement_project AS p ON p.id = pt.project_id and p.season_id = st.season_id');
        $query->join('LEFT', '#__sportsmanagement_person_project_position AS perpos ON perpos.project_id = p.id AND perpos.person_id = pe.id');
        $query->join('LEFT', '#__sportsmanagement_project_position AS ppos ON ppos.id = perpos.project_position_id and ppos.project_id = perpos.project_id');
        $query->join('LEFT', '#__sportsmanagement_position AS pos ON pos.id = ppos.position_id');
        $query->join('LEFT', '#__sportsmanagement_round AS rinjuryfrom ON pe.injury_date = rinjuryfrom.id');
        $query->join('LEFT', '#__sportsmanagement_round AS rinjuryto ON pe.injury_end = rinjuryto.id');
        $query->join('LEFT', '#__sportsmanagement_round AS rsuspfrom ON pe.suspension_date = rsuspfrom.id');
        $query->join('LEFT', '#__sportsmanagement_round AS rsuspto ON pe.suspension_end = rsuspto.id');
        $query->join('LEFT', '#__sportsmanagement_round AS rawayfrom ON pe.away_date = rawayfrom.id');
        $query->join('LEFT', '#__sportsmanagement_round AS rawayto ON pe.away_end = rawayto.id');
        $query->where('pt.project_id = ' . self::$projectid);
        $query->where('tp.person_id = ' . self::$personid);
        $query->where('pe.id = ' . self::$personid);
        $query->where('p.published = 1');
        try {
               $db->setQuery($query);
               $result = $db->loadObjectList('projectteam_id');
        }
        catch (Exception $e)
        {
            $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
                 $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getCode()), 'error');
            $result = false;
        }
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        self::$_teamplayers = $result;
        return self::$_teamplayers;
    }

    /**
     * sportsmanagementModelPlayer::getTeamPlayer()
     *
     * @return
     */
    static function getTeamPlayer($projectid = 0, $personid = 0, $teamplayerid = 0)
    {
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
        $query = $db->getQuery(true);

        if ($projectid) {
            self::$projectid = $projectid;
        }
        if ($personid) {
            self::$personid = $personid;
        }
        if ($teamplayerid) {
            self::$teamplayerid = $teamplayerid;
        }

      
        $query->select('tp.*');
        $query->select('pt.project_id,pt.team_id,pt.notes AS ptnotes,pt.picture as team_picture');
        $query->select('pos.name AS position_name');
        $query->select('ppos.position_id,pos.picture AS position_image');
        $query->select('ps.firstname, ps.lastname');
        $query->from('#__sportsmanagement_season_team_person_id AS tp ');
        $query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id');
        $query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $query->join('INNER', '#__sportsmanagement_project_position AS ppos ON ppos.id = tp.project_position_id');
        $query->join('INNER', '#__sportsmanagement_project AS p ON p.id = pt.project_id');
        $query->join('LEFT', '#__sportsmanagement_position AS pos ON pos.id = ppos.position_id');
        $query->join('INNER', '#__sportsmanagement_person AS ps ON ps.id = tp.person_id');
        $query->where('pt.project_id = ' . self::$projectid);
        if (self::$personid) {
            $query->where('tp.person_id = ' . self::$personid);
        }

        if (self::$teamplayerid) {
            $query->where('tp.id = ' . self::$teamplayerid);
        }

        $query->where('p.published = 1');
        $query->where('tp.persontype = 1');
        try {
                $db->setQuery($query);
                $result = $db->loadObjectList();
        }
        catch (Exception $e)
        {
            $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
                 $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getCode()), 'error');
            $result = false;
        }
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        self::$_inproject = $result;
      
        return self::$_inproject;
    }

    /**
     * sportsmanagementModelPlayer::getTeamStaff()
     *
     * @return
     */
    function getTeamStaff()
    {
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
        $query = $db->getQuery(true);

      
        $query->select('tp.*');
        $query->select('pt.project_id,pt.team_id,pt.notes AS ptnotes,pt.picture as team_picture');
        $query->select('pos.name AS position_name');
        $query->select('ppos.position_id,pos.picture AS position_image');
        $query->from('#__sportsmanagement_season_team_person_id AS tp ');
        $query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id');
        $query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $query->join('INNER', '#__sportsmanagement_project_position AS ppos ON ppos.id = tp.project_position_id');
        $query->join('INNER', '#__sportsmanagement_project AS p ON p.id = pt.project_id');
        $query->join('LEFT', '#__sportsmanagement_position AS pos ON pos.id = ppos.position_id');
        $query->where('pt.project_id = ' . self::$projectid);
        $query->where('tp.person_id = ' . self::$personid);
        $query->where('p.published = 1');
        $query->where('tp.persontype = 2');
        try {
                $db->setQuery($query);
                $result = $db->loadObject();
        }
        catch (Exception $e)
        {
            $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
                 $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getCode()), 'error');
            $result = false;
        }
        self::$_inproject = $result;
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        return self::$_inproject;
    }

    /**
     * sportsmanagementModelPlayer::getPlayerHistory()
     *
     * @param  integer $sportstype
     * @param  string  $order
     * @param  integer $persontype
     * @param  integer $cfg_which_database
     * @return
     */
    function getPlayerHistory($sportstype = 0, $order = 'ASC', $persontype = 1, $cfg_which_database = 0)
    {
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
        $query = $db->getQuery(true);

        $query->select('pr.id AS pid,pr.firstname,pr.lastname');
        $query->select('CONCAT_WS(\':\',pr.id,pr.alias) AS person_slug');
        $query->select('tp.person_id,tp.id AS tpid,tp.project_position_id,tp.market_value');
        $query->select('p.name AS project_name,CONCAT_WS(\':\',p.id,p.alias) AS project_slug');
        $query->select('s.name AS season_name,s.id AS season_id');
        $query->select('t.name AS team_name,t.id AS team_id,CONCAT_WS(\':\',t.id,t.alias) AS team_slug');
        $query->select('pos.name AS position_name,pos.id AS posID');
        $query->select('pt.id AS ptid,pt.project_id,pt.picture as team_picture');
        $query->select('ppos.position_id,pos.picture AS position_image');
        $query->select('tp.picture as season_picture');
        $query->select('p.picture as project_picture');
        $query->select('p.game_regular_time,p.add_time');
        $query->select('c.logo_big as club_picture');
        $query->from('#__sportsmanagement_person AS pr');
        $query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON tp.person_id = pr.id');
        $query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id AND st.season_id = tp.season_id');
        $query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $query->join('INNER', '#__sportsmanagement_team AS t ON t.id = st.team_id');
        $query->join('INNER', '#__sportsmanagement_club AS c ON c.id = t.club_id');
        $query->join('INNER', '#__sportsmanagement_project AS p ON p.id = pt.project_id');
        $query->join('INNER', '#__sportsmanagement_season AS s ON s.id = p.season_id');
        $query->join('INNER', '#__sportsmanagement_league AS l ON l.id = p.league_id');
        $query->join('LEFT', '#__sportsmanagement_person_project_position AS perpos ON perpos.project_id = p.id AND perpos.person_id = pr.id');
        $query->join('LEFT', '#__sportsmanagement_project_position AS ppos ON ppos.id = perpos.project_position_id');
        $query->join('LEFT', '#__sportsmanagement_position AS pos ON pos.id = ppos.position_id');
        $query->where('pr.id = ' . self::$personid);
        $query->where('p.published = 1');
        $query->where('perpos.published = 1');
        $query->where('pr.published = 1');
        $query->where('tp.persontype = ' . $persontype);
        if ($sportstype > 0) {
            $query->where('p.sports_type_id = ' . $sportstype);
        }

        $query->order('s.ordering ' . $order . ',l.ordering ASC,p.name ASC ');
        try {
                $db->setQuery($query);
                $result = $db->loadObjectList();
        }
        catch (Exception $e)
        {
            $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
                 $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getCode()), 'error');
            $result = false;
        }
        switch ($persontype) {
        case 1:
            self::$_playerhistory = $result;
            return self::$_playerhistory;
                break;
        case 2:
            self::$_playerhistorystaff = $result;
            return self::$_playerhistorystaff;
                break;
        }

    }

    /**
     * sportsmanagementModelPlayer::getAllEvents()
     *
     * @param  integer $sportstype
     * @return
     */
    function getAllEvents($sportstype = 0)
    {
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
        $query = $db->getQuery(true);

        $history = self::getPlayerHistory($sportstype);
        $positionhistory = array();
        foreach ($history as $h) {
            if (!in_array($h->posID, $positionhistory) && $h->posID != null) {
                $positionhistory[] = $h->posID;
            }
        }
        if (!count($positionhistory)) {
            return array();
        }

        $query->select('DISTINCT et.*,pet.ordering');
        $query->from('#__sportsmanagement_eventtype AS et');
        $query->join('INNER', '#__sportsmanagement_position_eventtype AS pet ON pet.eventtype_id = et.id');
        $query->join('INNER', '#__sportsmanagement_project_position AS ppos ON ppos.position_id = pet.position_id');
        $query->where('pet.position_id IN (' . implode(',', $positionhistory) . ')');
        $query->where('et.published = 1');
        $query->order('pet.ordering ');
        try {
                $db->setQuery($query);
                $info = $db->loadObjectList();
        }
        catch (Exception $e)
        {
            $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
                 $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getCode()), 'error');
            $info = false;
        }
      
        return $info;
    }

  
    /**
     * sportsmanagementModelPlayer::getTimePlayed()
     *
     * @param  mixed   $player_id
     * @param  mixed   $game_regular_time
     * @param  mixed   $match_id
     * @param  mixed   $cards
     * @param  integer $project_id
     * @return
     */
    public static function getTimePlayed($player_id, $game_regular_time, $match_id = null, $cards = null, $project_id = 0,$add_time = 0)
    {
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        /**
*
 * Create a new query object.
*/      
        $db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
        $query = $db->getQuery(true);

        $result = 0;

        /**
 * startaufstellung ohne ein und auswechselung
*/
        $query->select('COUNT(distinct mp.match_id) as totalmatch');
        $query->from('#__sportsmanagement_match_player as mp');
        $query->where('mp.teamplayer_id = ' . $player_id);
        $query->where('mp.came_in = 0');

        if ($project_id) {
            $query->join('INNER', '#__sportsmanagement_match as m ON m.id = mp.match_id');
            $query->join('INNER', '#__sportsmanagement_round as r ON r.id = m.round_id');
            $query->where('r.project_id = ' . $project_id);
            if ($add_time ) {
                $query->where('m.match_result_type = 0');
            }
        }

        if ($match_id) {
            $query->where('mp.match_id = ' . $match_id);
        }


        $db->setQuery($query);
        $totalresult = $db->loadObject();
    
        if ($add_time ) {
            $query->clear('where');
            $query->clear('join');
            $query->where('mp.teamplayer_id = ' . $player_id);
            $query->where('mp.came_in = 0');
            $query->join('INNER', '#__sportsmanagement_match as m ON m.id = mp.match_id');
            $query->join('INNER', '#__sportsmanagement_round as r ON r.id = m.round_id');
            $query->where('r.project_id = ' . $project_id);
            $query->where('m.match_result_type = 1');
            $db->setQuery($query);
            $totalresult2 = $db->loadObject();
            if ($totalresult2) {
                $result += $totalresult2->totalmatch * ($game_regular_time + $add_time);
            }  
          
          
        }
        if ($totalresult) {
            $result += $totalresult->totalmatch * $game_regular_time;
        }
      
        /**
 * einwechselung
*/
        $query = $db->getQuery(true);
        $query->clear();
        $query->select('count(distinct mp.match_id) as totalmatch, SUM(mp.in_out_time) as totalin');
        $query->from('#__sportsmanagement_match_player as mp');
        $query->where('mp.teamplayer_id = ' . $player_id);
        $query->where('mp.came_in = 1');
        $query->where('mp.in_for IS NOT NULL');

        if ($project_id) {
            $query->join('INNER', '#__sportsmanagement_match as m ON m.id = mp.match_id');
            $query->join('INNER', '#__sportsmanagement_round as r ON r.id = m.round_id');
            $query->where('r.project_id = ' . $project_id);
            if ($add_time ) {
                $query->where('m.match_result_type = 0');
            }
        }

        if ($match_id) {
            $query->where('mp.match_id = ' . $match_id);
        }

        $db->setQuery($query);
        $cameinresult = $db->loadObject();

        if ($add_time ) {
            $query->clear('where');
            $query->clear('join');
            $query->where('mp.teamplayer_id = ' . $player_id);
            $query->where('mp.came_in = 0');
            $query->join('INNER', '#__sportsmanagement_match as m ON m.id = mp.match_id');
            $query->join('INNER', '#__sportsmanagement_round as r ON r.id = m.round_id');
            $query->where('r.project_id = ' . $project_id);
            $query->where('m.match_result_type = 1');
            $db->setQuery($query);
            $cameinresult2 = $db->loadObject();
            if ($cameinresult2) {
                $result += ( $cameinresult2->totalmatch * ($game_regular_time + $add_time) ) - ( $cameinresult2->totalin );
            }  
          
          
        }
    
    
        if ($cameinresult) {
            $result += ( $cameinresult->totalmatch * $game_regular_time ) - ( $cameinresult->totalin );
        }

        /**
 * auswechselung
*/
        $query = $db->getQuery(true);
        $query->clear();
        $query->select('count(distinct mp.match_id) as totalmatch, SUM(mp.in_out_time) as totalout');
        $query->from('#__sportsmanagement_match_player as mp');
        $query->where('mp.in_for = ' . $player_id);
        $query->where('mp.came_in = 1');

        if ($project_id) {
            $query->join('INNER', '#__sportsmanagement_match as m ON m.id = mp.match_id');
            $query->join('INNER', '#__sportsmanagement_round as r ON r.id = m.round_id');
            $query->where('r.project_id = ' . $project_id);
            if ($add_time ) {
                $query->where('m.match_result_type = 0');
            }
        }

        if ($match_id) {
            $query->where('mp.match_id = ' . $match_id);
        }

        $db->setQuery($query);
        $cameautresult = $db->loadObject();

        if ($add_time ) {
            $query->clear('where');
            $query->clear('join');
            $query->where('mp.teamplayer_id = ' . $player_id);
            $query->where('mp.came_in = 0');
            $query->join('INNER', '#__sportsmanagement_match as m ON m.id = mp.match_id');
            $query->join('INNER', '#__sportsmanagement_round as r ON r.id = m.round_id');
            $query->where('r.project_id = ' . $project_id);
            $query->where('m.match_result_type = 1');
            $db->setQuery($query);
            $cameautresult2 = $db->loadObject();
            if ($cameautresult2) {

                $result += ( $cameautresult2->totalout ) - ( $cameautresult2->totalmatch * ($game_regular_time + $add_time) );
            }  
          
        }
    
        if ($cameautresult) {
            $result += ( $cameautresult->totalout ) - ( $cameautresult->totalmatch * $game_regular_time );
        }

        /**
 * jetzt muss man noch die karten berücksichtigen, die zu einer hinausstellung führen
*/
        if ($cards) {
            $query = $db->getQuery(true);
            $query->clear();
            $query->select('me.*');
            $query->from('#__sportsmanagement_match_event as me');
            $query->where('me.teamplayer_id = ' . $player_id);
            $query->where('me.event_type_id IN (' . implode(',', $cards) . ')');

            if ($project_id) {
                $query->join('INNER', '#__sportsmanagement_match as m ON m.id = me.match_id');
                $query->join('INNER', '#__sportsmanagement_round as r ON r.id = m.round_id');
                $query->where('r.project_id = ' . $project_id);
            }

            if ($match_id) {
                $query->where('me.match_id = ' . $match_id);
            }

            try {
                $db->setQuery($query);
                $cardsresult = $db->loadObjectList();
                foreach ($cardsresult as $row) {
                    $result -= ( $game_regular_time - $row->event_time );
                }
            } catch (Exception $e) {
                //    // catch any database errors.
                //    $db->transactionRollback();
                //    JErrorPage::render($e);
            }

        }

        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        return $result;
    }

    /**
     * sportsmanagementModelPlayer::getInOutStats()
     *
     * @param  integer $project_id
     * @param  integer $projectteam_id
     * @param  integer $teamplayer_id
     * @param  integer $game_regular_time
     * @param  integer $match_id
     * @param  integer $cfg_which_database
     * @param  integer $team_id
     * @param  integer $person_id
     * @return
     */
    public static function getInOutStats($project_id = 0, $projectteam_id = 0, $teamplayer_id = 0, $game_regular_time = 90, $match_id = 0, $cfg_which_database = 0, $team_id = 0, $person_id = 0)
    {
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
        $query = $db->getQuery(true);

        $query->select('m.id AS mid, mp.came_in, mp.out, mp.teamplayer_id, mp.in_for, mp.in_out_time');
        $query->from('#__sportsmanagement_match AS m');
        $query->join('INNER', '#__sportsmanagement_match_player AS mp ON mp.match_id = m.id');
        $query->join('INNER', '#__sportsmanagement_project_team AS pt1 ON m.projectteam1_id = pt1.id');
        $query->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id');
        $query->join('INNER', '#__sportsmanagement_project_team AS pt2 ON m.projectteam2_id = pt2.id');
        $query->join('INNER', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id');
        $query->join('INNER', '#__sportsmanagement_project AS p ON p.id = pt1.project_id ');
        $query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp1 ON ( tp1.id = mp.teamplayer_id OR tp1.id = mp.in_for)');

        if ($team_id) {
            $query->where('( st1.team_id = ' . (int) $team_id . ' OR st2.team_id = ' . (int) $team_id . ' )');
        }
        if ($person_id) {
            $query->where('tp1.person_id = ' . (int) $person_id);
        }
        if ($match_id) {
            $query->where('m.id = ' . (int) $match_id);
        }
        if ($teamplayer_id) {
            $query->where('tp1.id = ' . (int) $teamplayer_id);
        }
        if ($project_id) {
            $query->where('( pt1.project_id = ' . (int) $project_id . ' OR pt2.project_id = ' . (int) $project_id . ' )');
        }
        if ($projectteam_id) {
            $query->where('( pt1.id = ' . (int) $projectteam_id . ' OR pt2.id = ' . (int) $projectteam_id . ' )');
        }
        $query->where('m.published = 1');
        $query->where('p.published = 1');

        $db->setQuery($query);
        $rows = $db->loadObjectList();

      

        $inoutstat = new stdclass;
        $inoutstat->played = 0;
        $inoutstat->started = 0;
        $inoutstat->sub_in = 0;
        $inoutstat->sub_out = 0;
        foreach ($rows AS $row) {
            $inoutstat->played += ($row->came_in == 0);
            $inoutstat->played += ($row->came_in == 1) && ($row->teamplayer_id == $teamplayer_id);
            $inoutstat->started += ($row->came_in == 0);
            $inoutstat->sub_in += ($row->came_in == 1) && ($row->teamplayer_id == $teamplayer_id);
            $inoutstat->sub_out += ($row->out == 1) || ($row->in_for == $teamplayer_id);
        }
     
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

        return $inoutstat;
    }

    /**
     * sportsmanagementModelPlayer::getStats()
     *
     * @return
     */
    function getStats()
    {
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');

        $stats = array();
        $players = self::getTeamPlayer();

        if (is_array($players)) {
            foreach ($players as $player) {
                /**
 *                  Remark: we cannot use array_merge because numerical keys will result in duplicate entries
 *                  so we check if a key already exists in the output array before adding it.
 */
                $projectStats = sportsmanagementModelProject::getProjectStats(0, $player->position_id);

                if (is_array($projectStats)) {
                    foreach ($projectStats as $key => $projectStat) {
                        if (!array_key_exists($key, $stats)) {
                            $stats[$key] = $projectStat;
                        }
                    }
                }
            }
        }
        return $stats;
    }

    /**
     * sportsmanagementModelPlayer::getCareerStats()
     *
     * @param  mixed $person_id
     * @param  mixed $sports_type_id
     * @return
     */
    function getCareerStats($person_id, $sports_type_id)
    {
        $app = Factory::getApplication();
        $db = Factory::getDBO();
        $query_core = Factory::getDbo()->getQuery(true);

        if (empty($this->_careerStats)) {
            $query_core->select('s.id,s.name,s.short,s.class,s.icon,s.calculated,ppos.id AS pposid,ppos.position_id AS position_id,s.params,s.baseparams');
            $query_core->from('#__sportsmanagement_person AS p');
            $query_core->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON p.id = tp.person_id ');
            $query_core->join('INNER', '#__sportsmanagement_project_position AS ppos ON tp.project_position_id = ppos.id ');
            $query_core->join('INNER', '#__sportsmanagement_position AS pos ON ppos.position_id = pos.id ');
            $query_core->join('INNER', '#__sportsmanagement_position_statistic AS ps ON ps.position_id = pos.id ');
            $query_core->join('INNER', '#__sportsmanagement_statistic AS s ON ps.statistic_id = s.id ');
            $query_core->where('p.id = ' . $person_id);

            if ($sports_type_id > 0) {
                $query_core->where('pos.sports_type_id = ' . $sports_type_id);
            }

            $query_core->group('s.id,ppos.id');
            try{
                        $db->setQuery($query_core);
                        $this->_careerStats = $db->loadObjectList();
            }
            catch (Exception $e)
            {
                $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
                         $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getCode()), 'error');
                $this->_careerStats = false;
            }          
          
        }

        $stats = array();
        if (count($this->_careerStats) > 0) {
            foreach ($this->_careerStats as $k => $row) {
                $stat = SMStatistic::getInstance($row->class);
                $stat->bind($row);
                $stat->set('position_id', $row->position_id);
                $stats[$row->id] = $stat;
            }
        }
        return $stats;
    }

    /**
     * sportsmanagementModelPlayer::getPlayerStatsByGame()
     *
     * @return
     */
    function getPlayerStatsByGame()
    {
        $app = Factory::getApplication();
        $teamplayers = self::getTeamPlayers();
        $displaystats = array();
        if (count($teamplayers)) {
            $project = sportsmanagementModelProject::getProject();
            $project_id = $project->id;
            /**
 *              Determine teamplayer id(s) of the player (plural if (s)he played in multiple teams of the project
 *              and the position_id(s) where the player played
 */
            $teamplayer_ids = array();
            $position_ids = array();
            foreach ($teamplayers as $teamplayer) {
                $teamplayer_ids[] = $teamplayer->id;
                if (!in_array($teamplayer->position_id, $position_ids)) {
                    $position_ids[] = $teamplayer->position_id;
                }
            }
            /**
 *              For each position_id get the statistics types and merge the results (prevent duplicate statistics ids)
 *              ($pos_stats is an array indexed by statistic_id)
 */
            $pos_stats = array();
            foreach ($position_ids as $position_id) {
                $stats_for_position_id = sportsmanagementModelProject::getProjectStats(0, $position_id);
                foreach ($stats_for_position_id as $id => $stat) {
                    if (!array_key_exists($id, $pos_stats)) {
                        $pos_stats[$id] = $stat;
                    }
                }
            }
            if ($pos_stats ) {
                foreach ($pos_stats as $stat) {
                    if (!empty($stat)) {
                        if (isset($stat->_showinsinglematchreports) ) {
                                            include_once JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. JSM_PATH .DIRECTORY_SEPARATOR. 'statistics' .DIRECTORY_SEPARATOR.$stat->_name. '.php';  
                                            $mdlstats = BaseDatabaseModel::getInstance($stat->_name, "SMStatistic"); 
                                            $mdlstats->id = $stat->id;  
                                            $stat->gamesstats = $mdlstats->getPlayerStatsByGame($teamplayer_ids, $project_id);
                                            $displaystats[] = $stat;
                        }                  
                    }
                }
            }
        }
        return $displaystats;
    }

    /**
     * sportsmanagementModelPlayer::getPlayerStatsByProject()
     *
     * @param  integer $sportstype
     * @return
     */
    function getPlayerStatsByProject($sportstype = 0)
    {
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');

        $teamplayer = self::getTeamPlayer();
        $result = array();
        if (is_array($teamplayer) && !empty($teamplayer)) {
            /**
 *              getTeamPlayer can return multiple teamplayers, because a player can be transferred from
 *              one team to another inside a season, but they are all the same person so have same person_id.
 *              So we get the player_id from the first array entry.
 */
            $stats = self::getCareerStats($teamplayer[0]->person_id, $sportstype);
            $history = self::getPlayerHistory($sportstype);
          
            if (count($history) > 0) {
                foreach ($stats as $stat) {
                    if (!empty($stat)) {
                        foreach ($history as $player) {
                            $result[$stat->id][$player->project_id][$player->ptid] = $stat->getPlayerStatsByProject($player->person_id, $player->ptid, $player->project_id, $sportstype);
                            if (!isset($result[$stat->id]['totals']) ) {
                                $result[$stat->id]['totals'] = 0;  
                            }
                            $result[$stat->id]['totals'] += $result[$stat->id][$player->project_id][$player->ptid];
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * sportsmanagementModelPlayer::getGames()
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
        $teamplayers = self::getTeamPlayers();
        $games = array();

        if (count($teamplayers)) {
            $quoted_tpids = array();
            foreach ($teamplayers as $teamplayer) {
                $quoted_tpids[] = $db->Quote($teamplayer->id);
            }
            $tpid_list = '(' . implode(',', $quoted_tpids) . ')';

            /**
 *              Get all games played by the player (possible of multiple teams in the project)
 *              A player was in a match if:
 *              1. He is defined as a match player in the match
 *              2. There is one or more statistic on his name for the match
 *              3. There is one or more event on his name for the match
 */
            $query->select('m.id,m.match_date,m.projectteam1_id,m.projectteam2_id,m.team1_result,m.team2_result');
            $query->select('m.match_result_type');  
            $query->select('t1.id AS team1,t1.name AS home_name');
            $query->select('t2.id AS team2,t2.name AS away_name');
            $query->select('mp.teamplayer_id');
            $query->select('r.roundcode,r.project_id');
            $query->select('CONCAT_WS(\':\',m.id,CONCAT_WS("_",t1.alias,t2.alias)) AS match_slug ');
            $query->select('c1.logo_big as home_logo');
            $query->select('c2.logo_big as away_logo');
            $query->from('#__sportsmanagement_match AS m');
            $query->join('INNER', '#__sportsmanagement_match_player AS mp ON mp.match_id = m.id');
            $query->join('INNER', '#__sportsmanagement_round r ON m.round_id = r.id ');
            $query->join('INNER', '#__sportsmanagement_project AS p ON p.id = r.project_id ');
            $query->join('INNER', '#__sportsmanagement_project_team AS pt1 ON m.projectteam1_id = pt1.id ');
            $query->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id');
            $query->join('INNER', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id ');
            $query->join('INNER', '#__sportsmanagement_club AS c1 ON c1.id = t1.club_id ');

            $query->join('INNER', '#__sportsmanagement_project_team AS pt2 ON m.projectteam2_id = pt2.id ');
            $query->join('INNER', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id');
            $query->join('INNER', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');
            $query->join('INNER', '#__sportsmanagement_club AS c2 ON c2.id = t2.club_id ');

            $query->join('INNER', '#__sportsmanagement_season_team_person_id AS stp ON stp.id = mp.teamplayer_id');

            $query->where('mp.teamplayer_id IN ' . $tpid_list . '');
            $query->where('p.id = ' . self::$projectid);
            $query->where('m.published = 1');
            $query->where('p.published = 1');

            $query->order('m.match_date');

            $db->setQuery($query);
            $games = $db->loadObjectList();
        }

        foreach ($games as $game) {

            $inoutstats = self::getInOutStats($game->project_id, $game->projectteam1_id, $game->teamplayer_id, 0, $game->id);
            $game->started = $inoutstats->started;
            $game->sub_in = $inoutstats->sub_in;
            $game->sub_out = $inoutstats->sub_out;
            $game->playedtime = 0;
        }

      
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        return $games;
    }

    /**
     * sportsmanagementModelPlayer::getGamesEvents()
     *
     * @return
     */
    function getGamesEvents()
    {
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        // Create a new query object.		
        $db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
        $query = $db->getQuery(true);


        $teamplayers = self::getTeamPlayers();
        $gameevents = array();

        if (count($teamplayers)) {
            $quoted_tpids = array();
            foreach ($teamplayers as $teamplayer) {
                $quoted_tpids[] = $this->_db->Quote($teamplayer->id);
            }

            $query->select('SUM(me.event_sum) as value,me.match_id, me.event_type_id');
            $query->from('#__sportsmanagement_match_event AS me ');
            $query->where('me.teamplayer_id IN (' . implode(',', $quoted_tpids) . ')');
            $query->group('me.match_id, me.event_type_id');
            try {
                        $db->setQuery($query);
                        $events = $db->loadObjectList();
            }
            catch (Exception $e)
            {
                $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
                         $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' '.$e->getCode()), 'error');
                $events = false;
            }

            foreach ((array) $events as $ev) {
                if (isset($gameevents[$ev->match_id])) {
                    $gameevents[$ev->match_id][$ev->event_type_id] = $ev->value;
                } else {
                    $gameevents[$ev->match_id] = array($ev->event_type_id => $ev->value);
                }
            }
        }
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect  
        return $gameevents;
    }

}

?>
