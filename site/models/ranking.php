<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage ranking
 * @file       ranking.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementModelRanking
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelRanking extends BaseDatabaseModel
{
	static $projectid = 0;
	static $round = 0;
	static $rounds = array(0);
	static $part = 0;
	static $type = 0;
	static $last = 0;
	static $from = 0;
	static $to = 0;
	static $divLevel = 0;
	static $currentRanking = array();
	static $paramconfig = array();
	static $previousRanking = array();
	static $homeRank = array();
	static $awayRank = array();
	static $current_round = 0;
	static $viewName = '';
	static $selDivision = 0;
	static $season = 0;
	var $colors = array();
	var $result = array();
	var $pageNav = array();

	/**
	 * sportsmanagementModelRanking::__construct()
	 *
	 * @return
	 */
	function __construct()
	{
		$app = Factory::getApplication();

		// JInput object
		$jinput                 = $app->input;
		$from                   = 0;
		$to                     = 0;
		self::$projectid        = (int) $jinput->get('p', 0, '');
		self::$paramconfig['p'] = self::$projectid;

		// $this->round = Factory::getApplication()->input->getInt("r", $this->current_round);
		self::$round = $jinput->get('r', self::$current_round, '');
		self::$part  = $jinput->getInt("part", 0);

		// $this->from  = Factory::getApplication()->input->getInt('from', 0 );
		// $this->to  = Factory::getApplication()->input->getInt('to', $this->round);
		self::$from = $jinput->post->get('from', 0, '');
		self::$to   = $jinput->post->get('to', self::$round, '');

		self::$type        = $jinput->post->get('type', 0, '');
		self::$last        = $jinput->getInt('last', 0);
		self::$viewName    = $jinput->get('view', '', 'STR');
		self::$selDivision = $jinput->getInt('division', 0);

		sportsmanagementModelProject::$cfg_which_database = $jinput->get('cfg_which_database', 0, '');
		self::$season                                     = $jinput->get('s', 0, '');

		sportsmanagementModelProject::$projectid = self::$projectid;

		if (empty(self::$from))
		{
			$from                      = sportsmanagementModelRounds::getFirstRound(self::$projectid, sportsmanagementModelProject::$cfg_which_database);
			self::$from                = $from['id'];
			self::$paramconfig['from'] = self::$from;
		}

		if (empty(self::$to))
		{
			$to                      = sportsmanagementModelRounds::getLastRound(self::$projectid, sportsmanagementModelProject::$cfg_which_database);
			self::$to                = $to['id'];
			self::$paramconfig['to'] = self::$to;
		}
		else
		{
			self::$round = self::$to;
		}

		if (empty(self::$round))
		{
			self::$round                             = sportsmanagementModelProject::getCurrentRound(null, sportsmanagementModelProject::$cfg_which_database);
			self::$current_round                     = self::$round;
			sportsmanagementModelProject::$_round_to = sportsmanagementModelProject::$roundslug;
			self::$paramconfig['r']                  = sportsmanagementModelProject::$roundslug;
		}
		else
		{
			sportsmanagementModelProject::$_round_to = self::$round;
		}

		sportsmanagementModelProject::$_round_from = self::$from;
		parent::__construct();
	}



	/**
	 * sportsmanagementModelRanking::setFinalStanding()
	 * 
	 * @param mixed $current_ranking
	 * @param string $project_type
	 * @return void
	 */
	public static function setFinalStanding($current_ranking = array(),$project_type = 'SIMPLE_LEAGUE' )
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');
		$jinput                 = $app->input;
		$db        = sportsmanagementHelper::getDBConnection(true, $jinput->get('cfg_which_database', 0, '') );
		$query     = $db->getQuery(true);
		
//echo '<pre>'.print_r($current_ranking,true).'</pre>';
      foreach ($current_ranking as $division => $cu_rk) 
{
        foreach ($cu_rk as $key => $value) 
{
          //echo '<pre>'.print_r($key,true).'</pre>';
          //echo '<pre>'.print_r($value,true).'</pre>';
          // Create an object for the record we are going to update.
				$object = new stdClass;

				// Must be a valid primary key value.
				$object->id      = $key;
                
                $object->cache_points_finally = $value->sum_points;
                $object->cache_neg_points_finally = $value->neg_points;
                $object->cache_matches_finally = $value->cnt_matches;
                $object->cache_won_finally = $value->cnt_won;
                $object->cache_draws_finally = $value->cnt_draw;
                $object->cache_lost_finally = $value->cnt_lost;
				$object->cache_homegoals_finally = $value->sum_team1_result;
                $object->cache_guestgoals_finally = $value->sum_team2_result;
                $object->cache_diffgoals_finally = $value->diff_team_results;
  
            
            $object->finaltablerank = $value->rank;
            
            switch ($project_type)
            {
            case 'SIMPLE_LEAGUE':
            switch ($object->finaltablerank)
            {
            case 1:
            $object->champion = 1;
            break;
            }
            break;
            }

				// Update their details in the users table using id as the primary key.
				$result_update = Factory::getDbo()->updateObject('#__sportsmanagement_project_team', $object, 'id');
          
          
          
        }
      
      
      
      }		
		
	}
	
	/**
	 * sportsmanagementModelRanking::getPreviousGames()
	 * 
	 * @param integer $cfg_which_database
	 * @param string $sports_type_name
	 * @return
	 */
	public static function getPreviousGames($cfg_which_database = 0,$sports_type_name='')
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');
		$db        = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query     = $db->getQuery(true);
		$starttime = microtime();
        $division = array();
        $prevgames = array();

        $query->select('*');
		$query->from('#__sportsmanagement_division');
		$query->where('project_id = ' . (int) self::$projectid);
        $query->where('published = 1');
		$db->setQuery($query);
		$divisions = $db->loadObjectList();
                    
		if (!self::$round)
		{
			sportsmanagementModelProject::$_current_round = 0;
			self::$round = sportsmanagementModelProject::getCurrentRound(__METHOD__ . ' ' . self::$viewName, $cfg_which_database);
		}
		else
		{
			$query->clear();
			$query->select('r.id, r.roundcode,CONCAT_WS( \':\', r.id, r.alias ) AS round_slug');
			$query->from('#__sportsmanagement_round AS r ');
			$query->where('r.id = ' . (int) self::$round);
			$query->where('r.project_id = ' . (int) self::$projectid);

			try
			{
				$db->setQuery($query);
				$result = $db->loadObject();
			}
			catch (Exception $e)
			{
				echo $e->getMessage();
			}

			if (!$result)
			{
				sportsmanagementModelProject::$_current_round = 0;
				self::$round                                  = sportsmanagementModelProject::getCurrentRound(__METHOD__ . ' ' . self::$viewName, $cfg_which_database);
			}
		}

		if (!self::$round)
		{
			return false;
		}

		// Current round roundcode
		$rounds  = sportsmanagementModelProject::getRounds('ASC', $cfg_which_database);
		$current = null;

		foreach ($rounds as $r)
		{
			if ((int) $r->id == (int) self::$round)
			{
				$current = $r;
				break;
			}
		}

		if (!$current)
		{
			return false;
		}

		$query->clear();
		$query->select('m.*, r.roundcode');
		$query->select('CASE WHEN CHAR_LENGTH(t1.alias) AND CHAR_LENGTH(t2.alias) THEN CONCAT_WS(\':\',m.id,CONCAT_WS("_",t1.alias,t2.alias)) ELSE m.id END AS slug');
		$query->select('CONCAT_WS(\':\',p.id,p.alias) AS project_slug');
		$query->from('#__sportsmanagement_match AS m ');
		$query->join('INNER', '#__sportsmanagement_round AS r ON r.id = m.round_id ');
		$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = r.project_id ');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt1 ON m.projectteam1_id = pt1.id ');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt2 ON m.projectteam2_id = pt2.id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id');
		$query->join('INNER', '#__sportsmanagement_team AS t1 ON st1.team_id = t1.id ');
		$query->join('INNER', '#__sportsmanagement_team AS t2 ON st2.team_id = t2.id ');
		$query->where('r.project_id = ' . self::$projectid);
		$query->where('r.roundcode <= ' . $db->Quote($current->roundcode));
		$query->where('m.team1_result IS NOT NULL');
		$query->order('r.roundcode ASC ');

		$db->setQuery($query);

		$games = $db->loadObjectList();

		$teams = sportsmanagementModelProject::getTeamsIndexedByPtid(0, 'name', $cfg_which_database, __METHOD__);

		/** Get games per team */
		$res = array();

		foreach ($teams as $ptid => $team)
		{
			$teamgames = array();

			foreach ((array) $games as $g)
			{
				if ($g->projectteam1_id == $team->projectteamid || $g->projectteam2_id == $team->projectteamid)
				{
					$teamgames[$g->division_id][] = $g;
				}
			}

			if (!count($teamgames))
			{
				$res[$ptid] = array();
				continue;
			}

			/** Get last x games $nb_games = 5; */
			$config     = sportsmanagementModelProject::getTemplateConfig('ranking', $cfg_which_database, __METHOD__);
			$nb_games   = $config['nb_previous'];
            $res[$ptid] = $teamgames;
			//$res[$ptid] = array_slice($teamgames, -$nb_games);
		}
        
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        
        foreach ($res as $ptid => $value )
		{
        //$ptid[0] = array_slice($ptid[0], -$nb_games); 
        foreach ($value as $g => $neu)
        {
        $neu = array_slice($neu, -$nb_games);   
          //echo __LINE__.'<pre>'.print_r($g,true).'</pre>';  
          $prevgames[$ptid][$g] = $neu;
        }
       
      }
      
      //echo __LINE__.'<pre>'.print_r($prevgames,true).'</pre>'; 

		return $prevgames;
	}

	
	/**
	 * sportsmanagementModelRanking::computeRanking()
	 * 
	 * @param integer $cfg_which_database
	 * @param integer $s
	 * @param string $sports_type_name
	 * @return
	 */
	public static function computeRanking($cfg_which_database = 0, $s = 0,$sports_type_name='')
	{
		$app   = Factory::getApplication();
		$input = $app->input;

		$project = sportsmanagementModelProject::getProject($cfg_which_database, __METHOD__);

		if ($project)
		{
			sportsmanagementModelRounds::$_project_id = $project->id;

			$firstRound = sportsmanagementModelRounds::getFirstRound($project->id, $cfg_which_database);
			$lastRound  = sportsmanagementModelRounds::getLastRound($project->id, $cfg_which_database);

			/** Url if no sef link comes along (ranking form) */
			$routeparameter                       = array();
			$routeparameter['cfg_which_database'] = $cfg_which_database;
			$routeparameter['s']                  = $s;
			$routeparameter['p']                  = self::$projectid;
			$url                                  = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking', $routeparameter);

			$tableconfig = sportsmanagementModelProject::getTemplateConfig("ranking", $cfg_which_database, __METHOD__);

			self::$round = (self::$round == 0 || self::$round == '') ? sportsmanagementModelProject::getCurrentRound(__METHOD__ . ' ' . self::$viewName, $cfg_which_database) : self::$round;

			self::$rounds = sportsmanagementModelProject::getRounds($cfg_which_database);

			if (self::$part == 1)
			{
				self::$from = $firstRound['id'];
                if ( !array_key_exists(intval(count(self::$rounds) / 2) - 1, self::$rounds) ) {
                    self::$to = 0;
                }
                else
                {
				self::$to = self::$rounds[intval(count(self::$rounds) / 2) ]->id;
                }
			}
			elseif (self::$part == 2)
			{
			 if ( !array_key_exists(intval(count(self::$rounds) / 2), self::$rounds) ) {
                    self::$from = 0;
                }
                else
                {
                self::$from = self::$rounds[intval(count(self::$rounds) / 2) -1]->id;    
                }
				
				self::$to   = $lastRound['id'];
			}
			else
			{
				self::$from = $input->getInt('from', $firstRound['id']);
				self::$to   = $input->getInt('to', self::$round, $lastRound['id']);
			}

			if (self::$part > 0)
			{
				$url .= '&amp;part=' . self::$part;
			}
			elseif (self::$from != 1 || self::$to != self::$round)
			{
				$url .= '&amp;from=' . self::$from . '&amp;to=' . self::$to;
			}

			self::$type = $input->getInt('type', 0);

			if (self::$type > 0)
			{
				$url .= '&amp;type=' . self::$type;
			}

			self::$divLevel = 0;

			/** For sub division ranking tables */
			if ($project->project_type == 'DIVISIONS_LEAGUE')
			{
				$selDivision    = $input->getInt('division', 0);
				self::$divLevel = $input->getInt('divLevel', $tableconfig['default_division_view']);

				if ($selDivision > 0)
				{
					$url       .= '&amp;division=' . $selDivision;
					$divisions = array($selDivision);
				}
				else
				{
					/** Check if division level view is allowed. if not, replace with default */
					if ((self::$divLevel == 0 && $tableconfig['show_project_table'] == 0)
						|| (self::$divLevel == 1 && $tableconfig['show_level1_table'] == 0)
						|| (self::$divLevel == 2 && $tableconfig['show_level2_table'] == 0)
					)
					{
						self::$divLevel = $tableconfig['default_division_view'];
					}

					$url .= '&amp;divLevel=' . self::$divLevel;

					if (self::$divLevel)
					{
						$divisions = sportsmanagementModelProject::getDivisionsId(self::$divLevel, $cfg_which_database);
					}
					else
					{
						$divisions = array(0);
					}
				}
			}
			else
			{
				$divisions = array(0); // Project
			}

			$selectedvalue = 0;

			$last = $input->getInt('last', 0);

			if ($last > 0)
			{
				$url .= '&amp;last=' . $last;
			}

			if ($input->getInt('sef', 0) == 1)
			{
				$app->redirect(Route::_($url));
			}

			/** create ranking object */
			$ranking = JSMRanking::getInstance($project, $cfg_which_database);
			$ranking->setProjectId(self::$projectid, $cfg_which_database);

			foreach ($divisions as $division)
			{
				/** Away rank */
				if (self::$type == 2)
				{
					self::$currentRanking[$division] = $ranking->getRankingAway(self::$from, self::$to, $division, $cfg_which_database,$sports_type_name);
				}
				/** Home rank */
				elseif (self::$type == 1)
				{
					self::$currentRanking[$division] = $ranking->getRankingHome(self::$from, self::$to, $division, $cfg_which_database,$sports_type_name);
				}
				/** Total rank */
				else
				{
					self::$currentRanking[$division] = $ranking->getRanking(self::$from, self::$to, $division, $cfg_which_database,$sports_type_name);
					self::$homeRank[$division]       = $ranking->getRankingHome(self::$from, self::$to, $division, $cfg_which_database,$sports_type_name);
					self::$awayRank[$division]       = $ranking->getRankingAway(self::$from, self::$to, $division, $cfg_which_database,$sports_type_name);
				}
				
				
				if ( $division && !self::$currentRanking[$division] )
				{
					//echo 'gruppentabelle ist leer <br>';
					//self::$currentRanking[$division] = self::getProjectTeamsDivision($division);
				}

				self::_sortRanking(self::$currentRanking[$division]);

				if (empty(self::$from))
				{
					$from       = sportsmanagementModelRounds::getFirstRound(self::$projectid, sportsmanagementModelProject::$cfg_which_database);
					self::$from = $from['id'];
				}

				if (empty(self::$to))
				{
					self::$to = sportsmanagementModelProject::getCurrentRound(null, sportsmanagementModelProject::$cfg_which_database);
				}

				/** Previous rank */
				if ($tableconfig['last_ranking'])
				{
					if (self::$to == 1 || (self::$to == self::$from))
					{
						self::$previousRanking[$division] = &self::$currentRanking[$division];
					}
					else
					{
						/** Away rank */
						if (self::$type == 2)
						{
							self::$previousRanking[$division] = $ranking->getRankingAway(self::$from, self::_getPreviousRoundId(self::$to, $cfg_which_database), $division, $cfg_which_database);
						}
						/** Home rank */
						elseif (self::$type == 1)
						{
							self::$previousRanking[$division] = $ranking->getRankingHome(self::$from, self::_getPreviousRoundId(self::$to, $cfg_which_database), $division, $cfg_which_database);
						}
						/** Total rank */
						else
						{
							self::$previousRanking[$division] = $ranking->getRanking(self::$from, self::_getPreviousRoundId(self::$to, $cfg_which_database), $division, $cfg_which_database);
						}

						self::_sortRanking(self::$previousRanking[$division]);
					}
				}
			}

			self::$current_round = self::$round;
		}

		return;
	}
		
	/**
	 * sportsmanagementModelRanking::_sortRanking()
	 *
	 * @param   mixed  $ranking
	 *
	 * @return
	 */
	public static function _sortRanking(&$ranking)
	{
		$app       = Factory::getApplication();
		$jinput    = $app->input;
		$order     = $jinput->get('order', '', 'STR');
		$order_dir = $jinput->get('dir', 'ASC', 'STR');

		//		$order     = Factory::getApplication()->input->getVar( 'order', '' );
		//		$order_dir = Factory::getApplication()->input->getVar( 'dir', 'ASC' );

		switch ($order)
		{
			case 'played':
				uasort($ranking, array(__CLASS__, "playedCmp"));
				break;
			case 'name':
				uasort($ranking, array(__CLASS__, "teamNameCmp"));
				break;
			case 'rank':
				break;
			case 'won':
				uasort($ranking, array(__CLASS__, "wonCmp"));
				break;
			case 'draw':
				uasort($ranking, array(__CLASS__, "drawCmp"));
				break;
			case 'loss':
				uasort($ranking, array(__CLASS__, "lossCmp"));
				break;
			case 'wot':
				uasort($ranking, array(__CLASS__, "wotCmp"));
				break;
			case 'wso':
				uasort($ranking, array(__CLASS__, "wsoCmp"));
				break;
			case 'lot':
				uasort($ranking, array(__CLASS__, "lotCmp"));
				break;
			case 'lso':
				uasort($ranking, array(__CLASS__, "lsoCmp"));
				break;
			case 'winpct':
				uasort($ranking, array(__CLASS__, "winpctCmp"));
				break;
			case 'quot':
				uasort($ranking, array(__CLASS__, "quotCmp"));
				break;
			case 'goalsp':
				uasort($ranking, array(__CLASS__, "goalspCmp"));
				break;
			case 'goalsfor':
				uasort($ranking, array(__CLASS__, "goalsforCmp"));
				break;
			case 'goalsagainst':
				uasort($ranking, array(__CLASS__, "goalsagainstCmp"));
				break;
			case 'legsdiff':
				uasort($ranking, array(__CLASS__, "legsdiffCmp"));
				break;
			case 'legsratio':
				uasort($ranking, array(__CLASS__, "legsratioCmp"));
				break;
			case 'diff':
				uasort($ranking, array(__CLASS__, "diffCmp"));
				break;
			case 'points':
				uasort($ranking, array(__CLASS__, "pointsCmp"));
				break;

			case 'penaltypoints':
				uasort($ranking, array(__CLASS__, "penaltypointsCmp"));
				break;

			case 'start':
				uasort($ranking, array(__CLASS__, "startCmp"));
				break;
			case 'bonus':
				uasort($ranking, array(__CLASS__, "bonusCmp"));
				break;
			case 'negpoints':
				uasort($ranking, array(__CLASS__, "negpointsCmp"));
				break;
			case 'pointsratio':
				uasort($ranking, array(__CLASS__, "pointsratioCmp"));
				break;

			default:
				if (method_exists(__CLASS__, $order . 'Cmp'))
				{
					uasort($ranking, array(__CLASS__, $order . 'Cmp'));
				}
				break;
		}

		if ($order_dir == 'DESC')
		{
			$ranking = array_reverse($ranking, true);
		}

		return true;
	}

	/**
	 * sportsmanagementModelRanking::_getPreviousRoundId()
	 *
	 * @param   mixed  $round_id
	 *
	 * @return
	 */
	public static function _getPreviousRoundId($round_id, $cfg_which_database = 0)
	{
		$app       = Factory::getApplication();
		$option    = $app->input->getCmd('option');
		$db        = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query     = $db->getQuery(true);
		$starttime = microtime();

		$query->select('id');
		$query->from('#__sportsmanagement_round');
		$query->where('project_id = ' . self::$projectid);
		$query->order('roundcode ASC');

		$db->setQuery($query);

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			// Joomla! 3.0 code here
			$res = $db->loadColumn();
		}
		elseif (version_compare(JVERSION, '2.5.0', 'ge'))
		{
			// Joomla! 2.5 code here
			$res = $db->loadResultArray();
		}

		if (!$res)
		{
			return $round_id;
		}

		$index = array_search($round_id, $res);

		if ($index && $index > 0)
		{
			return $res[$index - 1];
		}

		// If not found, return same round
		return $round_id;
	}

	/**
	 * sportsmanagementModelRanking::limitText()
	 *
	 * @param   mixed  $text
	 * @param   mixed  $wordcount
	 *
	 * @return
	 */
	function limitText($text, $wordcount)
	{
		if (!$wordcount)
		{
			return $text;
		}

		$texts = explode(' ', $text);
		$count = count($texts);

		if ($count > $wordcount)
		{
			$texts = array_slice($texts, 0, $wordcount);
			$text  = implode(' ', $texts);
			$text  .= '...';
		}

		return $text;
	}

	/**
	 * ***********************************
	 * Compare functions for ordering     *
	 **************************************/

	/**
	 * sportsmanagementModelRanking::getRssFeeds()
	 *
	 * @param   mixed  $rssfeedlink
	 * @param   mixed  $rssitems
	 *
	 * @return
	 */
	function getRssFeeds($rssfeedlink, $rssitems)
	{
		$rssIds = array();
		$rssIds = explode(',', $rssfeedlink);

		//  get RSS parsed object
		$options               = array();
		$options['cache_time'] = null;

		$lists = array();

		foreach ($rssIds as $rssId)
		{
			$options['rssUrl'] = $rssId;

			if (version_compare(JVERSION, '4.0.0', 'ge'))
			{
			}
			elseif (version_compare(JVERSION, '3.0.0', 'ge'))
            {
				// Joomla! 3.0 code here
				$rssDoc = Factory::getFeedParser($options);
			}
			elseif (version_compare(JVERSION, '2.5.0', 'ge'))
			{
				// Joomla! 2.5 code here
				$rssDoc = Factory::getXMLparser('RSS', $options);
			}
			elseif (version_compare(JVERSION, '1.7.0', 'ge'))
			{
				// Joomla! 1.7 code here
			}
			elseif (version_compare(JVERSION, '1.6.0', 'ge'))
			{
				// Joomla! 1.6 code here
			}
			else
			{
				// Joomla! 1.5 code here
			}



if (version_compare(JVERSION, '4.0.0', 'ge'))
			{
				try
				{
					$feed = new \FeedFactory;

					// $feeds = new stdclass();
					$rssDoc = $feed->getFeed($rssId);

					return $rssDoc;
				}
				catch (\InvalidArgumentException $e)
				{
					Factory::getApplication()->enqueueMessage(Text::_('COM_NEWSFEEDS_ERRORS_FEED_NOT_RETRIEVED'), 'Notice');
				}
				catch (\RuntimeException $e)
				{
					Factory::getApplication()->enqueueMessage(Text::_('COM_NEWSFEEDS_ERRORS_FEED_NOT_RETRIEVED'), 'Notice');
				}
			}
else
			{
			$feed = new stdclass;
			if ($rssDoc != false)
			{
				// Channel header and link
				$feed->title       = $rssDoc->get_title();
				$feed->link        = $rssDoc->get_link();
				$feed->description = $rssDoc->get_description();

				// Channel image if exists
				$feed->image->url   = $rssDoc->get_image_url();
				$feed->image->title = $rssDoc->get_image_title();

				// Items
				$items = $rssDoc->get_items();

				// Feed elements
				$feed->items = array_slice($items, 0, $rssitems);
				$lists[]     = $feed;
			}
            }
		}

		return $lists;
	}

	/**
	 * sportsmanagementModelRanking::playedCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function playedCmp(&$a, &$b)
	{
		$res = $a->cnt_matches - $b->cnt_matches;

		return $res;
	}

	/**
	 * sportsmanagementModelRanking::teamNameCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function teamNameCmp(&$a, &$b)
	{
		return strcasecmp($a->_name, $b->_name);
	}

	/**
	 * sportsmanagementModelRanking::wonCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function wonCmp(&$a, &$b)
	{
		$res = $a->cnt_won - $b->cnt_won;

		return $res;
	}

	/**
	 * sportsmanagementModelRanking::drawCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function drawCmp(&$a, &$b)
	{
		$res = ($a->cnt_draw - $b->cnt_draw);

		return $res;
	}

	/**
	 * sportsmanagementModelRanking::lossCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function lossCmp(&$a, &$b)
	{
		$res = ($a->cnt_lost - $b->cnt_lost);

		return $res;
	}

	/**
	 * sportsmanagementModelRanking::wotCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function wotCmp(&$a, &$b)
	{
		$res = $a->cnt_wot - $b->cnt_wot;

		return $res;
	}

	/**
	 * sportsmanagementModelRanking::wsoCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function wsoCmp(&$a, &$b)
	{
		$res = $a->cnt_wso - $b->cnt_wso;

		return $res;
	}

	/**
	 * sportsmanagementModelRanking::lotCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function lotCmp(&$a, &$b)
	{
		$res = $a->cnt_lot - $b->cnt_lot;

		return $res;
	}

	/**
	 * sportsmanagementModelRanking::lsoCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function lsoCmp(&$a, &$b)
	{
		$res = $a->cnt_lso - $b->cnt_lso;

		return $res;
	}

	/**
	 * sportsmanagementModelRanking::winpctCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function winpctCmp(&$a, &$b)
	{
		$pct_a = $a->cnt_won / ($a->cnt_won + $a->cnt_lost + $a->cnt_draw);
		$pct_b = $b->cnt_won / ($b->cnt_won + $b->cnt_lost + $b->cnt_draw);
		$res   = ($pct_a < $pct_b);

		return $res;
	}

	/**
	 * sportsmanagementModelRanking::quotCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function quotCmp(&$a, &$b)
	{
		$pct_a = $a->cnt_won / ($a->cnt_won + $a->cnt_lost + $a->cnt_draw);
		$pct_b = $b->cnt_won / ($b->cnt_won + $b->cnt_lost + $b->cnt_draw);
		$res   = ($pct_a < $pct_b);

		return $res;
	}

	/**
	 * sportsmanagementModelRanking::goalspCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function goalspCmp(&$a, &$b)
	{
		$res = ($a->sum_team1_result - $b->sum_team1_result);

		return $res;
	}

	/**
	 * sportsmanagementModelRanking::goalsforCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function goalsforCmp(&$a, &$b)
	{
		$res = ($a->sum_team1_result - $b->sum_team1_result);

		return $res;
	}

	/**
	 * sportsmanagementModelRanking::goalsagainstCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function goalsagainstCmp(&$a, &$b)
	{
		$res = ($a->sum_team2_result - $b->sum_team2_result);

		return $res;
	}

	/**
	 * sportsmanagementModelRanking::legsdiffCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function legsdiffCmp(&$a, &$b)
	{
		$res = ($a->diff_team_legs - $b->diff_team_legs);

		return $res;
	}

	/**
	 * sportsmanagementModelRanking::legsratioCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function legsratioCmp(&$a, &$b)
	{
		$res = ($a->legsRatio - $b->legsRatio);

		return $res;
	}

	/**
	 * sportsmanagementModelRanking::diffCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function diffCmp(&$a, &$b)
	{
		$res = ($a->diff_team_results - $b->diff_team_results);

		return $res;
	}

	/**
	 * sportsmanagementModelRanking::pointsCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function pointsCmp(&$a, &$b)
	{
		$res = ($a->getPoints() - $b->getPoints());

		return $res;
	}

	/**
	 * sportsmanagementModelRanking::startCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function startCmp(&$a, &$b)
	{
		$res = ($a->team->start_points * $b->team->start_points);

		return $res;
	}

	/**
	 * sportsmanagementModelRanking::bonusCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function bonusCmp(&$a, &$b)
	{
		$res = ($a->bonus_points - $b->bonus_points);

		return $res;
	}

	/**
	 * sportsmanagementModelRanking::penaltypointsCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function penaltypointsCmp(&$a, &$b)
	{
		$res = ($a->penalty_points - $b->penalty_points);

		return $res;
	}

	/**
	 * sportsmanagementModelRanking::negpointsCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function negpointsCmp(&$a, &$b)
	{
		$res = ($a->neg_points - $b->neg_points);

		return $res;
	}

	/**
	 * sportsmanagementModelRanking::pointsratioCmp()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function pointsratioCmp(&$a, &$b)
	{
		$res = ($a->pointsRatio - $b->pointsRatio);

		return $res;
	}

}
