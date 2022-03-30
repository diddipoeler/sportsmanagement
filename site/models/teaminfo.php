<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage teaminfo
 * @file       teaminfo.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementModelTeamInfo
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelTeamInfo extends BaseDatabaseModel
{
	static $projectid = 0;
	static $projectteamid = 0;
	static $teamid = 0;
	static $team = null;
	static $club = null;
	static $cfg_which_database = 0;
	var $project = null;

	/**
	 * sportsmanagementModelTeamInfo::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		// Reference global application object
		$app = Factory::getApplication();

		self::$projectid                         = Factory::getApplication()->input->get('p', 0, 'INT');
		self::$projectteamid                     = Factory::getApplication()->input->get('ptid', 0, 'INT');
		self::$teamid                            = Factory::getApplication()->input->get('tid', 0, 'INT');
		self::$cfg_which_database                = Factory::getApplication()->input->get('cfg_which_database', 0, 'INT');
		sportsmanagementModelProject::$projectid = self::$projectid;
		parent::__construct();
	}
    
    
    /**
     * sportsmanagementModelTeamInfo::getTeam()
     * 
     * @param integer $inserthits
     * @param integer $teamid
     * @return
     */
    static function getTeam($inserthits = 0, $teamid = 0)
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db    = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);
		self::$projectid = $jinput->getInt("p", 0);

		if (empty(self::$projectid))
		{
			Log::add(Text::_('COM_SPORTSMANAGEMENT_NO_RANKING_PROJECTINFO'), Log::ERROR, 'jsmerror');
		}

		if ($teamid)
		{
			self::$teamid = $teamid;
		}
		else
		{
			self::$teamid = $jinput->getInt("cid", 0);
		}

		self::updateHits(self::$teamid, $inserthits);

		if (is_null(self::$team))
		{
			if (self::$teamid > 0)
			{
				$query->select('t.*');
				$query->from('#__sportsmanagement_team AS t');
				$query->where('t.id = ' . $db->Quote(self::$teamid));
				$db->setQuery($query);
				self::$team = $db->loadObject();
			}
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		return self::$team;
	}

	/**
	 * Method to return a team trainingdata array
	 *
	 * @param   int projectid
	 *
	 * @return array
	 */
	public static function getTrainigData($projectid)
	{
    	$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db    = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);
		$trainingData = array();

		if (self::$projectteamid == 0)
		{
			$projectTeamID = sportsmanagementModelProject::getprojectteamID(self::$teamid, self::$cfg_which_database);
		}
		else
		{
			$projectTeamID = self::$projectteamid;
		}

		$query->select('*');
		$query->from('#__sportsmanagement_team_trainingdata');
		$query->where('team_id = ' . self::$teamid);
		$query->order('dayofweek ASC');
		$db->setQuery($query);
		$trainingData = $db->loadObjectList();
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $trainingData;
	}

	/**
	 * get club info
	 *
	 * @return object
	 */
	public static function getClub()
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db        = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query     = $db->getQuery(true);
		$starttime = microtime();

		if (is_null(self::$club))
		{
			$team = self::getTeamByProject();

			if ($team->club_id > 0)
			{
				$query->select('*');
				$query->select('CONCAT_WS( \':\', id, alias ) AS slug');
				$query->from('#__sportsmanagement_club');
				$query->where('id = ' . $team->club_id);

				$db->setQuery($query);
				self::$club = $db->loadObject();
			}
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return self::$club;
	}

	/**
	 * sportsmanagementModelTeamInfo::getTeamByProject()
	 *
	 * @param   integer  $inserthits
	 *
	 * @return
	 */
	public static function getTeamByProject($inserthits = 0)
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db        = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query     = $db->getQuery(true);
		$starttime = microtime();

		self::updateHits(self::$teamid, $inserthits);

		if (is_null(self::$team))
		{
			$query->select('t.*,t.name AS tname, t.website AS team_website, t.email AS team_email, pt.*, pt.notes AS notes, pt.info AS info');
			$query->select('t.extended AS teamextended, t.picture AS team_picture, pt.picture AS projectteam_picture,pt.cr_picture AS cr_projectteam_picture, c.*');
			$query->select('CONCAT_WS( \':\', t.id, t.alias ) AS slug ');
			$query->select('pt.id as projectteamid, t.notes as teamnotes');
			$query->from('#__sportsmanagement_team t ');
			$query->join('LEFT', '#__sportsmanagement_club c ON t.club_id = c.id ');
			$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = t.id');
			$query->join('INNER', '#__sportsmanagement_project_team pt ON pt.team_id = st.id ');

			$query->where('pt.project_id = ' . self::$projectid);

			if (self::$projectteamid > 0)
			{
				$query->where('pt.id = ' . self::$projectteamid);
			}
			else
			{
				$query->where('t.id = ' . self::$teamid);
			}

			try
			{
				$db->setQuery($query);
				self::$team          = $db->loadObject();
				self::$projectteamid = self::$team->projectteamid;
			}
			catch (Exception $e)
			{
				$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
				$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), 'error');
			}
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return self::$team;
	}

	/**
	 * sportsmanagementModelTeamInfo::updateHits()
	 *
	 * @param   integer  $teamid
	 * @param   integer  $inserthits
	 *
	 * @return void
	 */
	public static function updateHits($teamid = 0, $inserthits = 0)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);

		if ($inserthits)
		{
			$query->update($db->quoteName('#__sportsmanagement_team'))->set('hits = hits + 1')->where('id = ' . $teamid);

			$db->setQuery($query);

			$result = $db->execute();
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		}

	}

	/**
	 * sportsmanagementModelTeamInfo::getSeasons()
	 *
	 * @param   mixed    $config
	 * @param   integer  $history
	 *
	 * @return
	 */
	public static function getSeasons($config, $history = 0)
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db        = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query     = $db->getQuery(true);
		$starttime = microtime();

		$seasons = array();

		if ($config['ordering_teams_seasons'] == "1")
		{
			$season_ordering = "DESC";
		}
		else
		{
			$season_ordering = "ASC";
		}

		$query->clear();
		$query->select('pt.id as ptid, pt.team_id as season_team_id, pt.picture, pt.info, pt.project_id AS projectid');
        if ( ComponentHelper::getParams('com_sportsmanagement')->get('force_ranking_cache', 0) )
			{
			 $query->select('pt.cache_points_finally as points_finally,
             pt.cache_neg_points_finally as neg_points_finally,
             pt.finaltablerank,
             pt.cache_matches_finally as matches_finally,
             pt.cache_won_finally as won_finally,
             pt.cache_draws_finally as draws_finally,
             pt.cache_lost_finally as lost_finally,
             pt.cache_homegoals_finally as homegoals_finally,
             pt.cache_guestgoals_finally as guestgoals_finally');
			}
			else
			{
		$query->select('pt.points_finally,pt.neg_points_finally,pt.finaltablerank,pt.matches_finally,pt.won_finally,pt.draws_finally,pt.lost_finally,pt.homegoals_finally,pt.guestgoals_finally');
        }
		$query->select('p.name as projectname,p.season_id,p.current_round, pt.division_id');
		$query->select('s.name as season');
		$query->select('t.id as team_id');
		$query->select('st.picture as season_picture');
		$query->select('l.name as league, t.extended as teamextended, l.country as leaguecountry');
		$query->select('CONCAT_WS( \':\', p.id, p.alias ) AS project_slug');
		$query->select('CONCAT_WS( \':\', t.id, t.alias ) AS team_slug');
		$query->from('#__sportsmanagement_project_team AS pt ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.id = pt.team_id');
		$query->join('INNER', '#__sportsmanagement_team AS t ON t.id = st.team_id ');
		$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = pt.project_id ');
		$query->join('INNER', '#__sportsmanagement_season AS s ON s.id = p.season_id ');
		$query->join('INNER', '#__sportsmanagement_league AS l ON l.id = p.league_id ');

		if ($history)
		{
			$query->where('t.id = ' . self::$teamid);
		}
		else
		{
			if (self::$projectteamid > 0)
			{
				$query->where('pt.id = ' . self::$projectteamid);
			}
			else
			{
				$query->where('t.id = ' . self::$teamid);
			}
		}

		$query->order('s.name ' . $season_ordering);

		$db->setQuery($query);
		$seasons = $db->loadObjectList();

if ( Factory::getConfig()->get('debug') )
{
	$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' data <pre>'.print_r($query->dump(),true).'</pre>'  ), ''); 
	$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' projectteamid <pre>'.print_r(self::$projectteamid,true).'</pre>'  ), '');
	$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' teamid <pre>'.print_r(self::$teamid,true).'</pre>'  ), '');
}



		foreach ($seasons as $k => $season)
		{
			$seasons[$k]->division_slug       = null;
			$seasons[$k]->division_name       = null;
			$seasons[$k]->division_short_name = null;
			$seasons[$k]->round_slug          = null;
			
			if ( ComponentHelper::getParams('com_sportsmanagement')->get('force_ranking_cache', 0) )
			{
			/** noch nicht freigeschaltet */
			$seasons[$k]->rank          = $season->finaltablerank;
          		$seasons[$k]->games          = $season->matches_finally;
          		$seasons[$k]->playercnt      = self::getPlayerCount($season->projectid, $season->ptid, $season->season_id);
			$seasons[$k]->playermeanage  = self::getPlayerMeanAge($season->projectid, $season->ptid, $season->season_id);
			$seasons[$k]->market_value   = self::getPlayerMarketValue($season->projectid, $season->ptid, $season->season_id);
          		$seasons[$k]->goals          = $season->homegoals_finally.':'.$season->guestgoals_finally;
          		$seasons[$k]->series          = $season->won_finally.'/'.$season->draws_finally.'/'.$season->lost_finally;
          		$seasons[$k]->points         = $season->points_finally;
			$seasons[$k]->leaguename     = self::getLeague($season->projectid);
			$seasons[$k]->season_picture = $season->season_picture;
			$seasons[$k]->ptid           = $season->ptid;

		}
			
			
			$query->clear();

			if ($season->division_id)
			{
				$query->select('CONCAT_WS( \':\', d.id, d.alias ) AS division_slug');
				$query->select('d.name AS division_name');
				$query->select('d.shortname AS division_short_name');
				$query->from('#__sportsmanagement_division AS d');
				$query->where('d.id = ' . $season->division_id);
				$query->where('d.project_id = ' . $season->projectid);
				$db->setQuery($query);
				$result                           = $db->loadObject();
				$seasons[$k]->division_slug       = $result->division_slug;
				$seasons[$k]->division_name       = $result->division_name;
				$seasons[$k]->division_short_name = $result->division_short_name;
			}

			$query->clear();

			if ($season->current_round)
			{
				$query->select('CONCAT_WS( \':\', r.id, r.alias ) AS round_slug');
				$query->from('#__sportsmanagement_round AS r');
				$query->where('r.id = ' . $season->current_round);
				$query->where('r.project_id = ' . $season->projectid);
				$db->setQuery($query);
				$result                  = $db->loadObject();
				$seasons[$k]->round_slug = $result->round_slug;
			}

			if ( ComponentHelper::getParams('com_sportsmanagement')->get('force_ranking_cache', 0) )
			{
			}
			else
			{
			$ranking = self::getTeamRanking($season->projectid, $season->division_id);
			if (!empty($ranking))
			{
				$seasons[$k]->rank           = $ranking['rank'];
				$seasons[$k]->leaguename     = self::getLeague($season->projectid);
				$seasons[$k]->season_picture = $season->season_picture;
				$seasons[$k]->ptid           = $season->ptid;
				$seasons[$k]->games          = $ranking['games'];
				$seasons[$k]->points         = $ranking['points'];
				$seasons[$k]->series         = $ranking['series'];
				$seasons[$k]->goals          = $ranking['goals'];
				$seasons[$k]->playercnt      = self::getPlayerCount($season->projectid, $season->ptid, $season->season_id);
				$seasons[$k]->playermeanage  = self::getPlayerMeanAge($season->projectid, $season->ptid, $season->season_id);
				$seasons[$k]->market_value   = self::getPlayerMarketValue($season->projectid, $season->ptid, $season->season_id);
			}
			else
			{
				$seasons[$k]->rank           = 0;
				$seasons[$k]->leaguename     = '';
				$seasons[$k]->season_picture = $season->season_picture;
				$seasons[$k]->ptid           = $season->ptid;
				$seasons[$k]->games          = 0;
				$seasons[$k]->points         = 0;
				$seasons[$k]->series         = 0;
				$seasons[$k]->goals          = 0;
				$seasons[$k]->playercnt      = 0;
				$seasons[$k]->playermeanage  = 0;
				$seasons[$k]->market_value   = 0;
			}
		}
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $seasons;
	}

	/**
	 * get ranking of current team in a project
	 *
	 * @param   int projectid
	 * @param   int division_id
	 *
	 * @return array
	 */
	public static function getTeamRanking($projectid, $division_id)
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db    = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);
		$rank = array();

		sportsmanagementModelProject::setProjectID($projectid, self::$cfg_which_database);
		$project     = sportsmanagementModelProject::getProject(self::$cfg_which_database);
		//$tableconfig = sportsmanagementModelProject::getTemplateConfig("ranking", self::$cfg_which_database);
		$ranking     = JSMRanking::getInstance($project, self::$cfg_which_database);
		//$ranking->setProjectId($project->id, self::$cfg_which_database);
		$temp_ranking = $ranking->getRanking(0, sportsmanagementModelProject::getCurrentRound(null, self::$cfg_which_database), $division_id, self::$cfg_which_database);

		foreach ($temp_ranking as $ptid => $value)
		{
			if ($value->getTeamId() == self::$teamid)
			{
				$rank['rank']   = $value->rank;
				$rank['games']  = $value->cnt_matches;
				$rank['points'] = $value->getPoints();
				$rank['series'] = $value->cnt_won . "/" . $value->cnt_draw . "/" . $value->cnt_lost;
				$rank['goals']  = $value->sum_team1_result . ":" . $value->sum_team2_result;
				break;
			}
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $rank;
	}

	/**
	 * gets name of league associated to project
	 *
	 * @param   int  $projectid
	 *
	 * @return string
	 */
	public static function getLeague($projectid)
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db    = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);
		$query->select('l.name AS league');
		$query->from('#__sportsmanagement_project AS p');
		$query->join('INNER', '#__sportsmanagement_league AS l ON l.id = p.league_id');
		$query->where('p.id =' . $projectid);

		$db->setQuery($query, 0, 1);
		$league = $db->loadResult();

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $league;
	}

	/**
	 * Get total number of players assigned to a team
	 *
	 * @param   int projectid
	 * @param   int projectteamid
	 *
	 * @return integer
	 */
	public static function getPlayerCount($projectid, $projectteamid, $season_id)
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db    = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);

		$player = array();
		$query->select('COUNT(*) AS playercnt');
		$query->from('#__sportsmanagement_person AS ps');
		$query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON tp.person_id = ps.id');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id AND st.season_id = tp.season_id');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt ON st.id = pt.team_id');

		$query->where('pt.project_id = ' . $projectid);
		$query->where('pt.id = ' . $projectteamid);
		$query->where('tp.season_id = ' . $season_id);
		$query->where('st.season_id = ' . $season_id);
		$query->where('ps.published = 1');

		$db->setQuery($query);
		$player = $db->loadResult();
		
if ( Factory::getConfig()->get('debug') )
{
		$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' data <pre>'.print_r($query->dump(),true).'</pre>'  ), ''); 
}
		
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $player;
	}

	/**
	 * sportsmanagementModelTeamInfo::getPlayerMeanAge()
	 *
	 * @param   mixed  $projectid
	 * @param   mixed  $projectteamid
	 * @param   mixed  $season_id
	 *
	 * @return
	 */
	public static function getPlayerMeanAge($projectid, $projectteamid, $season_id)
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db    = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);

		$meanage     = 0;
		$countplayer = 0;
		$age         = 0;

		$query->select('ps.birthday, ps.deathday');
		$query->from('#__sportsmanagement_person AS ps');
		$query->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON tp.person_id = ps.id');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id AND st.season_id = tp.season_id');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt ON st.id = pt.team_id');

		$query->where('pt.project_id = ' . $projectid);
		$query->where('pt.id = ' . $projectteamid);
		$query->where('tp.season_id = ' . $season_id);
		$query->where('st.season_id = ' . $season_id);
		$query->where('tp.published = 1');
		$query->where('ps.published = 1');

		$db->setQuery($query);
		$players = $db->loadObjectList();

if ( Factory::getConfig()->get('debug') )
{
		$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' data <pre>'.print_r($query->dump(),true).'</pre>'  ), ''); 
}
		
		foreach ($players as $player)
		{
			if ($player->birthday != '0000-00-00')
			{
				$age += sportsmanagementHelper::getAge($player->birthday, $player->deathday);
				$countplayer++;
			}
		}

		/** Diddipoeler */
		/** damit kein fehler hochkommt: Warning: Division by zero */
		if ($age != 0)
		{
			$meanage = round($age / $countplayer, 2);
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $meanage;
	}

	/**
	 * sportsmanagementModelTeamInfo::getPlayerMarketValue()
	 *
	 * @param   mixed  $projectid
	 * @param   mixed  $projectteamid
	 * @param   mixed  $season_id
	 *
	 * @return
	 */
	public static function getPlayerMarketValue($projectid, $projectteamid, $season_id)
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db        = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query     = $db->getQuery(true);
		$starttime = microtime();

		$player = array();
		$query->select('SUM(stp.market_value) AS market_value');
		$query->from('#__sportsmanagement_season_team_person_id AS stp');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = stp.team_id');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');

		$query->where('pt.project_id = ' . $projectid);
		$query->where('pt.id = ' . $projectteamid);
		$query->where('st.season_id = ' . $season_id);
		$query->where('stp.season_id = ' . $season_id);
		$query->where('stp.published = 1');

		$db->setQuery($query);

		$player = $db->loadResult();
		
if ( Factory::getConfig()->get('debug') )
{
		$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' data <pre>'.print_r($query->dump(),true).'</pre>'  ), ''); 
}
		
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $player;
	}

	/**
	 * sportsmanagementModelTeamInfo::getLeagueRankOverviewDetail()
	 *
	 * @param   mixed  $seasonsranking
	 *
	 * @return
	 */
	public static function getLeagueRankOverviewDetail($seasonsranking)
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$leaguesoverviewdetail = array();

		foreach ($seasonsranking as $season)
		{
			$temp                                   = new stdClass;
			$temp->match                            = 0;
			$temp->won                              = 0;
			$temp->draw                             = 0;
			$temp->loss                             = 0;
			$temp->goalsfor                         = 0;
			$temp->goalsagain                       = 0;
			$leaguesoverviewdetail[$season->league] = $temp;
		}

		foreach ($seasonsranking as $season)
		{
			$leaguesoverviewdetail[$season->league]->match += $season->games;
			$teile                                         = explode("/", $season->series);
			$leaguesoverviewdetail[$season->league]->won   += $teile[0];

			if (array_key_exists('1', $teile))
			{
				$leaguesoverviewdetail[$season->league]->draw += $teile[1];
			}

			if (array_key_exists('2', $teile))
			{
				$leaguesoverviewdetail[$season->league]->loss += $teile[2];
			}

			$teile                                            = explode(":", $season->goals);
			$leaguesoverviewdetail[$season->league]->goalsfor += $teile[0];

			if (array_key_exists('1', $teile))
			{
				$leaguesoverviewdetail[$season->league]->goalsagain += $teile[1];
			}
		}

		return $leaguesoverviewdetail;
	}

	/**
	 * sportsmanagementModelTeamInfo::getLeagueRankOverview()
	 *
	 * @param   mixed  $seasonsranking
	 *
	 * @return
	 */
	public static function getLeagueRankOverview($seasonsranking)
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		$leaguesoverview = array();

		foreach ($seasonsranking as $season)
		{
			if (isset($leaguesoverview[$season->league][(int) $season->rank]))
			{
				$leaguesoverview[$season->league][(int) $season->rank] += 1;
			}
			else
			{
				$leaguesoverview[$season->league][(int) $season->rank] = 0;
			}
		}

		ksort($leaguesoverview);

		foreach ($leaguesoverview as $key => $value)
		{
			ksort($leaguesoverview[$key]);
		}

		return $leaguesoverview;
	}

	/**
	 * sportsmanagementModelTeamInfo::getMergeClubs()
	 *
	 * @param   mixed  $merge_clubs
	 *
	 * @return
	 */
	function getMergeClubs($merge_clubs)
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		$db    = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);
		$query->select('*, CONCAT_WS( \':\', id, alias ) AS slug');
		$query->from('#__sportsmanagement_club');
		$query->where('id IN ( ' . $merge_clubs . ' )');

		$db->setQuery($query);
		$result = $db->loadObjectList();

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;
	}

	/**
	 * sportsmanagementModelTeamInfo::hasEditPermission()
	 *
	 * @param   mixed  $task
	 *
	 * @return
	 */
	function hasEditPermission($task = null)
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$allowed = parent::hasEditPermission($task);
		$user    = Factory::getUser();

		if ($user->id > 0 && !$allowed)
		{
			/** Check if user is the projectteam admin */
			$team = self::getTeamByProject();

			if ($user->id == $team->admin)
			{
				$allowed = true;
			}
		}

		return $allowed;
	}

}

