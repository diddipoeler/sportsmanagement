<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage helpers
 * @file       ranking.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

/**
 * JSMRanking
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JSMRanking
{
	static $rankingalltimenotes = array();
	static $rankingalltimewarnings = array();
	static $rankingalltimetips = array();
	
	/**
	 * project id
	 *
	 * @var integer
	 */
	var $_projectid = 0;
	
	static $_use_finaltablerank = 0;

	/**
	 * project model cache
	 *
	 * @var object
	 */
	var $_project = null;

	/**
	 * caching for the data
	 *
	 * @var object
	 */
	var $_data = null;

	/**
	 * ranking parameters
	 *
	 * @var array
	 */
	var $_params = null;

	/**
	 * criteria for ranking order
	 *
	 * @var array
	 */
	var $_criteria = null;

	/**
	 * ranking mode: 0/1/2 for normal/home/away ranking
	 *
	 * @var integer
	 */
	var $_mode = 0;

	/**
	 * starting roundid for the ranking
	 *
	 * @var integer
	 */
	var $_from = null;

	/**
	 * end roundid for the ranking
	 *
	 * @var integer
	 */
	var $_to = null;

	/**
	 * division id
	 *
	 * @var integer
	 */
	var $_division = null;

	/**
	 * caching for heat to head ranking
	 *
	 * @var array
	 */
	var $_h2h = null;

	/**
	 * storing current group id for caching h2h collect
	 *
	 * @var array
	 */
	var $_h2h_group = 0;

	/**
	 * divisions matching _division and childs
	 *
	 * @var unknown_type
	 */
	var $_divisions = null;

	/**
	 * array of roundcodes indexed by round id
	 *
	 * @var array
	 */
	var $_roundcodes = null;

	
	/**
	 * JSMRanking::getInstance()
	 * 
	 * @param mixed $project
	 * @param integer $cfg_which_database
	 * @return
	 */
	public static function getInstance($project = null, $cfg_which_database = 0)
	{
		if ($project)
		{
			$extensions = sportsmanagementHelper::getExtensions($project->id);
			foreach ($extensions as $type)
			{
				$classname = 'JSMRanking' . ucfirst($type);
				if (!class_exists($classname))
				{
					$file = JPATH_COMPONENT_SITE . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . 'ranking.php';
					if (file_exists($file))
					{
						include_once $file;
						$obj = new $classname;
						$obj->setProjectId($project->id, $cfg_which_database);
						return $obj;
					}
				}
				else
				{
					$obj = new $classname;
					$obj->setProjectId($project->id, $cfg_which_database);
					return $obj;
				}
			}

			$obj = new JSMRanking;
			$obj->setProjectId($project->id, $cfg_which_database);
			return $obj;
		}

		$obj = new JSMRanking;
		return $obj;
	}

	
	/**
	 * JSMRanking::setProjectId()
	 * 
	 * @param mixed $id
	 * @param integer $cfg_which_database
	 * @return void
	 */
	function setProjectId($id, $cfg_which_database = 0)
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');
		$this->_projectid = (int) $id;
		sportsmanagementModelProject::setProjectID($id, $cfg_which_database);
		$this->_params = sportsmanagementModelProject::getTemplateConfig('ranking', $cfg_which_database, __METHOD__);
		$this->_data = null;
	}

	
	/**
	 * JSMRanking::getRanking()
	 * 
	 * @param mixed $from
	 * @param mixed $to
	 * @param mixed $division
	 * @param integer $cfg_which_database
	 * @param string $sports_type_name
	 * @return
	 */
	function getRanking($from = null, $to = null, $division = null, $cfg_which_database = 0,$sports_type_name='')
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		$this->_from = $from;
		$this->_to   = $to;
		$this->_mode = 0;

		if ($division == '')
		{
			$division = 0;
		}

		self::setDivisionId($division, $cfg_which_database);

		$teams = self::_collect(null, $cfg_which_database,$sports_type_name);
		$rankings = self::_buildRanking($teams, $cfg_which_database);

		return $rankings;
	}

	/**
	 * sets division id
	 * if not null, the return ranking will be for this division
	 *
	 * @param $id
	 */
	function setDivisionId($id, $cfg_which_database = 0)
	{
		$this->_division  = $id;
		$this->_divisions = null;
	}

	/**
	 * return teams data according to match result in ranking object table
	 *
	 * @param   array int project team ids: only collect for games between specified teams (usefull for head to head)
	 */
	function _collect($ptids = null, $cfg_which_database = 0,$sports_type_name='')
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		$mode     = $this->_mode;
		$from     = $this->_from;
		$to       = $this->_to;
		$division = $this->_division;

		$project = sportsmanagementModelProject::getProject($cfg_which_database, __METHOD__);
		$data    = self::_initData($cfg_which_database,$sports_type_name);

// echo __METHOD__.' '.__LINE__ .' data <pre>'.print_r($data,true).'</pre>';


	
		  switch ($sports_type_name)
		{
		case 'COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION':
        foreach ((array) $data->_matches as $match)
		{
        $homeId = $match->projectteam1_id;
			$awayId = $match->projectteam2_id;
            if ( array_key_exists($homeId, $data->_teams) )
            {
            $home = $data->_teams[$homeId];
            $home->sum_team1_result  += $match->home_score;
			$home->sum_team2_result  += $match->away_score;
            $home->shooterrings  += $match->home_score;
            $home->shooterringsperround[$match->roundcode] = $match->home_score;
            }
            if ( array_key_exists($awayId, $data->_teams) )
            {
			$away = $data->_teams[$awayId];
            $away->sum_team1_result  += $match->away_score;
			$away->sum_team2_result  += $match->home_score;
            $away->shooterrings  += $match->away_score;
            
            
            }
//            $home = new JSMRankingTeamClass(0);
//            $away = new JSMRankingTeamClass(0);
            
            
            }
        break;
        default:
    	foreach ((array) $data->_matches as $match)
		{      
			if (!isset($data->_teams[$match->projectteam1_id]) || $data->_teams[$match->projectteam1_id]->_is_in_score === 0
				|| !isset($data->_teams[$match->projectteam2_id]) || $data->_teams[$match->projectteam2_id]->_is_in_score === 0
			)
			{
				continue;
			}

			if (!$this->_countGame($match, $from, $to, $ptids, $cfg_which_database))
			{
				continue;
			}

			if ($match->projectteam1_id == 0 || $match->projectteam2_id == 0)
			{
				continue;
			}

			$homeId = $match->projectteam1_id;
			$awayId = $match->projectteam2_id;

			if ($mode == 0 || $mode == 1)
			{
				$home = &$data->_teams[$homeId];
			}
			else
			{
				$home = new JSMRankingTeamClass(0);

				// In that case, $data wont be affected
				// $home::$sum_points = 0;
			}

			if ($mode == 0 || $mode == 2)
			{
				$away = &$data->_teams[$awayId];
			}
			else
			{
				$away = new JSMRankingTeamClass(0);

				// In that case, $data wont be affected
				// $away::$sum_points = 0;
			}

			$shownegpoints = 1;

			$decision = $match->decision;

			$mp1 = 0;
			$mp2 = 0;
			$se1 = 0;
			$se2 = 0;
			$ga1 = 0;
			$ga2 = 0;

			if ($decision == 0)
			{
				$home_score = $match->home_score;
				$away_score = $match->away_score;
				$leg1       = $match->l1;
				$leg2       = $match->l2;

				$mp1 = $match->mp1;
				$mp2 = $match->mp2;
				$se1 = $match->se1;
				$se2 = $match->se2;
				$ga1 = $match->ga1;
				$ga2 = $match->ga2;
			}
			else
			{
				$home_score = $match->home_score_decision;
				$away_score = $match->away_score_decision;
				$leg1       = 0;
				$leg2       = 0;
			}

			if ($sports_type_name == 'COM_SPORTSMANAGEMENT_ST_FAUSTBALL')
			{
				$balls1 = array_sum(explode(';',$match->ls1));
				$balls2 = array_sum(explode(';',$match->ls2));
			}
			
			$home->cnt_matches++;
			$away->cnt_matches++;

			$resultType = ($project->allow_add_time) ? $match->match_result_type : 0;

			// $resultType=2;

			$arr[0] = 0;
			$arr[1] = 0;
			$arr[2] = 0;

			switch ($resultType)
			{
				case 1:
					$arr = explode(",", $project->points_after_add_time);
					break;
				case 2:
					$arr = explode(",", $project->points_after_penalty);
					break;
				default:
					$arr = explode(",", $project->points_after_regular_time);
					break;
			}

			$win_points  = (isset($arr[0])) ? $arr[0] : 3;
			$draw_points = (isset($arr[1])) ? $arr[1] : 1;
			$loss_points = (isset($arr[2])) ? $arr[2] : 0;

			$home_ot = $match->home_score_ot;
			$away_ot = $match->away_score_ot;
			$home_so = $match->home_score_so;
			$away_so = $match->away_score_so;

			if ($decision != 1)
			{
				if ($home_score > $away_score)
				{
					switch ($resultType)
					{
						case 0:
							$home->cnt_won++;
							$home->cnt_won_home++;
							$away->cnt_lost++;
							$away->cnt_lost_away++;
							break;
						case 1:
							$home->cnt_wot++;
							$home->cnt_wot_home++;
							/** keine summierung für hockey */
							//						$home->cnt_won++;
							//						$home->cnt_won_home++;
							$away->cnt_lot++;
							$away->cnt_lot_away++;
							/** keine summierung für hockey */
							// $away->cnt_lost++;
							// $away->cnt_lost_home++;
							break;
						case 2:
							$home->cnt_wso++;
							$home->cnt_wso_home++;
							/**
							 * keine summierung für hockey
							 */
							//						$home->cnt_won++;
							//						$home->cnt_won_home++;
							$away->cnt_lso++;
							$away->cnt_lso_away++;
							$away->cnt_lot++;
							$away->cnt_lot_away++;
							/**
							 * keine summierung für hockey
							 */
							// $away->cnt_lost++;
							// $away->cnt_lost_home++;
							break;
					}

					$home->sum_points += $win_points; // Home_score can't be null...
					$away->sum_points += ($decision == 0 || isset($away_score) ? $loss_points : 0);

					// $home::$sum_points += $win_points; //home_score can't be null...
					// $away::$sum_points += ( $decision == 0 || isset($away_score) ? $loss_points : 0);

					if ($shownegpoints == 1)
					{
						$home->neg_points += $loss_points;
						$away->neg_points += ($decision == 0 || isset($away_score) ? $win_points : 0);
					}
				}
				elseif ($home_score == $away_score)
				{
					switch ($resultType)
					{
						case 0:
							$home->cnt_draw++;
							$home->cnt_draw_home++;

							$away->cnt_draw++;
							$away->cnt_draw_away++;
							break;
						case 1:
							if ($home_ot > $away_ot)
							{
								/**
								 * keine summierung für hockey
								 */
								//							$home->cnt_won++;
								//							$home->cnt_won_home++;
								$home->cnt_wot++;
								$home->cnt_wot_home++;
								/**
								 * keine summierung für hockey
								 */
								//							$away->cnt_lost++;
								//							$away->cnt_lost_away++;
								$away->cnt_lot++;
								$away->cnt_lot_away++;
							}

							if ($home_ot < $away_ot)
							{
								/**
								 * keine summierung für hockey
								 */
								//							$away->cnt_won++;
								//							$away->cnt_won_home++;
								$away->cnt_wot++;
								$away->cnt_wot_home++;
								/**
								 * keine summierung für hockey
								 */
								//							$home->cnt_lost++;
								//							$home->cnt_lost_away++;
								$home->cnt_lot++;
								$home->cnt_lot_away++;
							}
							break;
						case 2:
							if ($home_so > $away_so)
							{
								/**
								 * keine summierung für hockey
								 */
								//							$home->cnt_won++;
								//							$home->cnt_won_home++;
								$home->cnt_wso++;
								$home->cnt_wso_home++;
								/**
								 * keine summierung für hockey
								 */
								//							$away->cnt_lost++;
								//							$away->cnt_lost_away++;
								$away->cnt_lso++;
								$away->cnt_lso_away++;
							}

							if ($home_so < $away_so)
							{
								/**
								 * keine summierung für hockey
								 */
								//							$away->cnt_won++;
								//							$away->cnt_won_home++;
								$away->cnt_wso++;
								$away->cnt_wso_home++;
								/**
								 * keine summierung für hockey
								 */
								//							$home->cnt_lost++;
								//							$home->cnt_lost_away++;
								$home->cnt_lso++;
								$home->cnt_lso_away++;
							}
							break;
					}

					$home->sum_points += ($decision == 0 || isset($home_score) ? $draw_points : 0);
					$away->sum_points += ($decision == 0 || isset($away_score) ? $draw_points : 0);

					if ($shownegpoints == 1)
					{
						$home->neg_points += ($decision == 0 || isset($home_score) ? ($win_points - $draw_points) : 0); // Bug fixed, timoline 250709
						$away->neg_points += ($decision == 0 || isset($away_score) ? ($win_points - $draw_points) : 0);// ex. for soccer, your loss = 2 points not 1 point
					}
				}
				elseif ($home_score < $away_score)
				{
					switch ($resultType)
					{
						case 0:
							$home->cnt_lost++;
							$home->cnt_lost_home++;

							$away->cnt_won++;
							$away->cnt_won_away++;
							break;
						case 1:
							$home->cnt_lot++;
							$home->cnt_lot_home++;
							/**
							 * keine summierung für hockey
							 */
							// $home->cnt_lost++;
							// $home->cnt_lost_home++;
							$away->cnt_wot++;
							$away->cnt_wot_away++;
							/**
							 * keine summierung für hockey
							 */
							//						$away->cnt_won++;
							//                      $away->cnt_won_away++;
							break;
						case 2:
							$home->cnt_lso++;
							$home->cnt_lso_home++;
							$home->cnt_lot++;
							$home->cnt_lot_home++;
							/**
							 * keine summierung für hockey
							 */
							// $home->cnt_lost++;
							// $home->cnt_lost_home++;
							$away->cnt_wso++;
							$away->cnt_wso_away++;
							/**
							 * keine summierung für hockey
							 */
							//						$away->cnt_won++;
							//                      $away->cnt_won_away++;
							break;
					}

					$home->sum_points += ($decision == 0 || isset($home_score) ? $loss_points : 0);
					$away->sum_points += $win_points;

					// $home::$sum_points += ( $decision == 0 || isset($home_score) ? $loss_points : 0);
					// $away::$sum_points += $win_points;

					if ($shownegpoints == 1)
					{
						$home->neg_points += ($decision == 0 || isset($home_score) ? $win_points : 0);
						$away->neg_points += $loss_points;
					}
				}
			}
			else
			{
				if ($shownegpoints == 1)
				{
					$home->neg_points += $loss_points;
					$away->neg_points += $loss_points;
				}

				// Final Win/Loss Decision
				if ($match->team_won == 0)
				{
					$home->cnt_lost++;
					$away->cnt_lost++;

					// Record a won on the home team
				}
				elseif ($match->team_won == 1)
				{
					$home->cnt_won++;
					$away->cnt_lost++;
					$home->sum_points += $win_points;
					$away->cnt_lost_home++;

					// Record a won on the away team
				}
				elseif ($match->team_won == 2)
				{
					$away->cnt_won++;
					$home->cnt_lost++;
					$away->sum_points += $win_points;
					$home->cnt_lost_home++;

					// Record a loss on both teams
				}
				elseif ($match->team_won == 3)
				{
					$home->cnt_lost++;
					$away->cnt_lost++;
					$away->cnt_lost_home++;
					$home->cnt_lost_home++;

					// Record a won on both teams
				}
				elseif ($match->team_won == 4)
				{
					$home->cnt_won++;
					$away->cnt_won++;
					$home->sum_points += $win_points;
					$away->sum_points += $win_points;
				}
			}

			// Winpoints

			$home->winpoints = $win_points;

			// Bonus points

			$home->sum_points += $match->home_bonus;

			// $home::$sum_points += $match->home_bonus;
			$home->bonus_points += $match->home_bonus;

			$away->sum_points += $match->away_bonus;

			// $away::$sum_points += $match->away_bonus;
			$away->bonus_points += $match->away_bonus;

			// Goals for/against/diff

			$home->sum_team1_result  += $home_score;
			$home->sum_team2_result  += $away_score;
			$home->diff_team_results = $home->sum_team1_result - $home->sum_team2_result;
			$home->sum_team1_legs    += $leg1;
			$home->sum_team2_legs    += $leg2;
			$home->diff_team_legs    = $home->sum_team1_legs - $home->sum_team2_legs;
			
			if ($sports_type_name == 'COM_SPORTSMANAGEMENT_ST_FAUSTBALL')
			{
				$home->sum_team1_balls   += $balls1;
				$home->sum_team2_balls   += $balls2;
				$home->diff_team_balls   = $home->sum_team1_balls - $home->sum_team2_balls;
			}

			$home->sum_team1_matchpoint += $mp1;
			$home->sum_team2_matchpoint += $mp2;
			$home->diff_team_matchpoint = $home->sum_team1_matchpoint - $home->sum_team2_matchpoint;
			$home->sum_team1_sets       += $se1;
			$home->sum_team2_sets       += $se2;
			$home->diff_team_sets       = $home->sum_team1_sets - $home->sum_team2_sets;
			$home->sum_team1_games      += $ga1;
			$home->sum_team2_games      += $ga2;
			$home->diff_team_games      = $home->sum_team1_games - $home->sum_team2_games;

			$away->sum_team1_result  += $away_score;
			$away->sum_team2_result  += $home_score;
			$away->diff_team_results = $away->sum_team1_result - $away->sum_team2_result;
			$away->sum_team1_legs    += $leg2;
			$away->sum_team2_legs    += $leg1;
			$away->diff_team_legs    = $away->sum_team1_legs - $away->sum_team2_legs;
			
			if ($sports_type_name == 'COM_SPORTSMANAGEMENT_ST_FAUSTBALL')
			{
				$away->sum_team1_balls    += $balls2;
				$away->sum_team2_balls    += $balls1;
				$away->diff_team_balls   = $away->sum_team1_balls - $away->sum_team2_balls;
			}

			$away->sum_team1_matchpoint += $mp1;
			$away->sum_team2_matchpoint += $mp1;
			$away->diff_team_matchpoint = $away->sum_team1_matchpoint - $away->sum_team2_matchpoint;
			$away->sum_team1_sets       += $se2;
			$away->sum_team2_sets       += $se1;
			$away->diff_team_sets       = $away->sum_team1_sets - $away->sum_team2_sets;
			$away->sum_team1_games      += $ga2;
			$away->sum_team2_games      += $ga1;
			$away->diff_team_games      = $away->sum_team1_games - $away->sum_team2_games;

			$away->sum_away_for += $away_score;
        }    
            break;
            }
            
            
		

		return $data->_teams;
	}

	/**
	 * return games and initial team objects
	 *
	 * @return object with properties _teams and _matches
	 */
	function _initData($cfg_which_database = 0,$sports_type_name='')
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		if (!$this->_projectid)
		{
			$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_RANKING_ERROR_PROJECTID_REQUIRED'), 'error');

			return false;
		}

		if (version_compare(JSM_JVERSION, '4', 'eq'))
		{
			$data = self::_cachedGetData($this->_projectid, $this->_division, $cfg_which_database,$sports_type_name);
		}

		if (version_compare(JSM_JVERSION, '3', 'eq'))
		{
			$data = self::_cachedGetData($this->_projectid, $this->_division, $cfg_which_database,$sports_type_name);
		}

		return $data;
	}

	/**
	 * cached method to collect the results
	 *
	 * @param   int project id
	 */
	public static function _cachedGetData($pid, $division, $cfg_which_database = 0,$sports_type_name='')
	{
		$data = new stdclass;

		$data->_teams   = self::_initTeams($pid, $division, $cfg_which_database,$sports_type_name);
		$data->_matches = self::_getMatches($pid, $division, $cfg_which_database,$sports_type_name);

 //echo '<pre>'.print_r($data->_teams,true).'</pre>';
 //echo '<pre>'.print_r($data->_matches,true).'</pre>';
		if ( sizeof($data->_matches) == 0 )
		{
		self::$_use_finaltablerank = 1;	
		}
        
        foreach( $data->_teams as $key => $value )
        {
            if ($value->_points_finally)
            {
                self::$_use_finaltablerank = 0;
            }
        }
		
		return $data;
	}

	/**
	 * gets team info from db
	 *
	 * @return array of JSMRankingTeam objects
	 */
	public static function _initTeams($pid, $division, $cfg_which_database = 0,$sports_type_name='')
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');
		$db        = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query     = $db->getQuery(true);
		$starttime = microtime();

		$query->select('pt.id AS ptid, pt.is_in_score, pt.start_points, pt.division_id');
		$query->select('t.name, t.id as teamid, pt.neg_points_finally');
		$query->select('pt.use_finally, pt.points_finally,pt.matches_finally,pt.won_finally,pt.draws_finally,pt.lost_finally');
		$query->select('pt.homegoals_finally, pt.guestgoals_finally,pt.diffgoals_finally,pt.penalty_points,pt.finaltablerank');
		$query->select('CONCAT_WS(\':\',pt.id,t.alias) AS ptid_slug');
		$query->from('#__sportsmanagement_project_team AS pt ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt.team_id');
		$query->join('INNER', '#__sportsmanagement_team AS t ON st1.team_id = t.id ');
		$query->where('pt.project_id = ' . $pid);
		$query->where('pt.is_in_score = 1');

		if ($division)
		{
			$query->join('INNER', '#__sportsmanagement_match AS m ON ( m.projectteam1_id = pt.id OR m.projectteam2_id = pt.id ) ');
			$query->where('m.division_id = ' . $division);
		}
		else
		{
		}

		$db->setQuery($query);
		$res = $db->loadObjectList();

		/**
		 * es kann aber auch vorkommen, dass nur abschlusstabellen zu den gruppen vorhanden sind.
		 * deshalb muss die komponente auch in der lage das darzustellen.
		 */
		if (!$res && $division)
		{
			$query->clear();
			$query->select('pt.id AS ptid, pt.is_in_score, pt.start_points, pt.division_id');
			$query->select('t.name, t.id as teamid, pt.neg_points_finally');
			$query->select('pt.use_finally, pt.points_finally,pt.matches_finally,pt.won_finally,pt.draws_finally,pt.lost_finally');
			$query->select('pt.homegoals_finally, pt.guestgoals_finally,pt.diffgoals_finally,pt.penalty_points,pt.finaltablerank');
			$query->select('CONCAT_WS(\':\',pt.id,t.alias) AS ptid_slug');
			$query->from('#__sportsmanagement_project_team AS pt ');
			$query->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt.team_id');
			$query->join('INNER', '#__sportsmanagement_team AS t ON st1.team_id = t.id ');
			$query->where('pt.project_id = ' . $pid);
			$query->where('pt.is_in_score = 1');
			$db->setQuery($query);
			$res = $db->loadObjectList();
		}

		$teams = array();

		foreach ((array) $res as $r)
		{
			$t = new JSMRankingTeamClass($r->ptid);
			$t->setTeamid($r->teamid);
			$t->setPtid($r->ptid);

			// Diddipoeler
			$t->ptid_slug = $r->ptid_slug;

			if ($division && !$r->division_id)
			{
				$t->setDivisionid($division);
			}
			else
			{
				$t->setDivisionid($r->division_id);
			}

			$t->setStartpoints($r->start_points);
			$t->setNegpoints($r->neg_points_finally);
			$t->setName($r->name);

			// New for is_in_score
			$t->setIs_In_Score($r->is_in_score);
			$t->setuse_finaltablerank($r->finaltablerank);

			// New for use_finally
			$t->setuse_finally($r->use_finally);
			$t->setpoints_finally($r->points_finally);
			$t->setneg_points_finally($r->neg_points_finally);
			$t->setmatches_finally($r->matches_finally);
			$t->setwon_finally($r->won_finally);
			$t->setdraws_finally($r->draws_finally);
			$t->setlost_finally($r->lost_finally);
			$t->sethomegoals_finally($r->homegoals_finally);
			$t->setguestgoals_finally($r->guestgoals_finally);
			$t->setdiffgoals_finally($r->diffgoals_finally);

			$t->penalty_points = $r->penalty_points;

			if ($r->use_finally)
			{
				$t->sum_points        = $r->points_finally;
				$t->neg_points        = $r->neg_points_finally;
				$t->cnt_matches       = $r->matches_finally;
				$t->cnt_won           = $r->won_finally;
				$t->cnt_draw          = $r->draws_finally;
				$t->cnt_lost          = $r->lost_finally;
				$t->sum_team1_result  = $r->homegoals_finally;
				$t->sum_team2_result  = $r->guestgoals_finally;
				$t->diff_team_results = $r->diffgoals_finally;
			}

			$teams[$r->ptid] = $t;
		}

		return $teams;
	}

	/**
	 * gets games from db
	 *
	 * @return array
	 */
	public static function _getMatches($pid, $division, $cfg_which_database = 0,$sports_type_name='')
	{
		$app       = Factory::getApplication();
		$option    = $app->input->getCmd('option');
		$db        = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query     = $db->getQuery(true);
		$starttime = microtime();
		$viewName = $app->input->getVar("view");


        switch ($sports_type_name)
		{
		case 'COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION':
        
        break;
        default:
        $query->where('( (m.team1_result IS NOT NULL AND m.team2_result IS NOT NULL) OR (m.alt_decision=1) ) ');
        $query->where('m.projectteam1_id > 0 AND m.projectteam2_id > 0');
        break;
        }



		$query->select('m.id');
		$query->select('m.projectteam1_id');
		$query->select('m.projectteam2_id');
		$query->select('m.team1_result AS home_score');
		$query->select('m.team2_result AS away_score');
		$query->select('m.team1_bonus AS home_bonus');
		$query->select('m.team2_bonus AS away_bonus');
		$query->select('m.team1_legs AS l1');
		$query->select('m.team2_legs AS l2');
		$query->select('m.team1_result_split AS ls1');
		$query->select('m.team2_result_split AS ls2');

		$query->select('m.team1_single_matchpoint AS mp1');
		$query->select('m.team2_single_matchpoint AS mp2');

		$query->select('m.team1_single_sets AS se1');
		$query->select('m.team2_single_sets AS se2');

		$query->select('m.team1_single_games AS ga1');
		$query->select('m.team2_single_games AS ga2');

		$query->select('m.match_result_type AS match_result_type');
		$query->select('m.alt_decision as decision');
		$query->select('m.team1_result_decision AS home_score_decision');
		$query->select('m.team2_result_decision AS away_score_decision');
		$query->select('m.team1_result_ot AS home_score_ot');
		$query->select('m.team2_result_ot AS away_score_ot');
		$query->select('m.team1_result_so AS home_score_so');
		$query->select('m.team2_result_so AS away_score_so');
		$query->select('r.id as roundid, m.team_won, r.roundcode ');

		$query->from('#__sportsmanagement_match as m');
		$query->join('INNER', '#__sportsmanagement_project_team AS pt1 ON m.projectteam1_id = pt1.id ');
		$query->join('INNER', '#__sportsmanagement_round AS r ON m.round_id = r.id');

//		$query->where('( (m.team1_result IS NOT NULL AND m.team2_result IS NOT NULL) OR (m.alt_decision=1) ) ');
		$query->where('m.published = 1');
		$query->where('r.published = 1');
		$query->where('pt1.project_id = ' . $db->Quote($pid));

		if ($division)
		{
			$query->where('m.division_id = ' . $division);
		}

		$query->where('(m.cancel IS NULL OR m.cancel = 0)');
//		$query->where('m.projectteam1_id > 0 AND m.projectteam2_id > 0');






		switch ($viewName)
		{
		case 'rankingalltime':
		break;
		default:
		$query->where('m.count_result');
		break;
		}

		$db->setQuery($query);

		$res     = $db->loadObjectList();
//echo __METHOD__.' '.__LINE__ .' res <pre>'.print_r($res,true).'</pre>';        
		$matches = array();

		foreach ((array) $res as $r)
		{
			$matches[$r->id] = $r;
		}

//echo __METHOD__ .' '.__LINE__. ' matches <pre>'.print_r($matches,true).'</pre>';

		return $matches;
	}

	/**
	 * returns true if the game matches the criteria
	 *
	 * @param   object match
	 * @param   int from: first round id
	 * @param   int to: last round id
	 * @param   int division id
	 * @param   array int project team ids, game must be between team in that array
	 */
	function _countGame($game, $from = null, $to = null, $ptids = null, $cfg_which_database = 0)
	{
		$res = true;

		if ($from)
		{
			if ($this->_getRoundcode($game->roundid, $cfg_which_database) < $this->_getRoundcode($from, $cfg_which_database))
			{
				return false;
			}
		}

		if ($to)
		{
			if ($this->_getRoundcode($game->roundid, $cfg_which_database) > $this->_getRoundcode($to, $cfg_which_database))
			{
				return false;
			}
		}

		if ($ptids)
		{
			if (!in_array($game->projectteam1_id, $ptids) || !in_array($game->projectteam2_id, $ptids))
			{
				return false;
			}
		}

		return $res;
	}

	/**
	 * return round roundcode
	 *
	 * @param   int  $round_id
	 *
	 * @return integer roundcode
	 */
	function _getRoundcode($round_id, $cfg_which_database = 0)
	{
		$app       = Factory::getApplication();
		$option    = $app->input->getCmd('option');
		$view = $app->input->getVar("view");
		$db        = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query     = $db->getQuery(true);
		$starttime = microtime();

		if (empty($this->_roundcodes))
		{
			$query->select('r.roundcode, r.id ');
			$query->from('#__sportsmanagement_round AS r ');
			$query->where('r.project_id = ' . $this->_projectid);
			$query->order('r.roundcode');
			$db->setQuery($query);
			$this->_roundcodes = $db->loadAssocList('id');
		}

		if (!isset($this->_roundcodes[(int) $round_id]))
		{
			switch ($view)
{
				case 'leaguechampionoverview':
					return false;
					break;
				default:
			Log::add(Text::_('COM_SPORTSMANAGEMENT_RANKING_ERROR_UNKOWN_ROUND_ID'), Log::ERROR, 'jsmerror');
			Log::add(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MASTER_TEMPLATE_MISSING_PID'), Log::ERROR, 'jsmerror');

			return false;					
					break;
			}

		}

		return $this->_roundcodes[(int) $round_id];
	}

	/**
	 * returns the ranking for selected teams
	 *
	 * @param   array teams objects
	 */
	function _buildRanking($teams, $cfg_which_database = 0)
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		// Division filtering
		$teams = array_filter($teams, array($this, "_filterdivision"));

		// Initial group contains all teams, unordered, indexed by starting rank 1
		$groups = array(1 => $teams);

		// Each criteria will sort teams. All teams that are 'equal' for a given criteria are put in a 'group'
		// the next criteria will sort inside each of the previously obtained groups
		// in the end, we obtain a certain number of groups, indexed by rank. this can contain one to several teams of same rank
		foreach ($this->_getRankingCriteria() as $c)
		{
			$newgroups = array(); // Groups that will be used for next loop

			foreach ($groups as $rank => $teams)
			{
				// For head to head, we have to init h2h values that are used to collect results just for this group
				$this->_h2h_group = $teams; // teans of the group
				$this->_h2h       = null;         // wipe h2h data cache for group

				$newrank = $rank;
				$current = $rank;

				uasort($teams, array($this, $c)); // Sort

				$prev = null;

				foreach ($teams as $k => $team)
				{
					if (!$prev || $this->$c($team, $prev) != 0)
					{
						// Teams are not 'equal', create new group
						$newgroups[$newrank] = array($k => $team);
						$current             = $newrank;
					}
					else
					{
						// Teams still have the same rank, add to current group
						$newgroups[$current][$k] = $team;
					}

					$prev = $team;
					$newrank++;
				}
			}

			$groups = $newgroups;
		}

		// Now, let's just sort by name for the team still tied, and output to single array
		$res = array();

		foreach ($groups as $rank => $teams)
		{
			uasort($teams, array($this, '_cmpAlpha'));

			foreach ($teams as $ptid => $t)
			{
				$t->rank    = $rank;
				$res[$ptid] = $t;
			}
		}

		return $res;
	}

	/**
	 * Returns ranking criteria as an array of methods
	 * This method will look for method matching the specified criteria: e.g, if the criteria is 'points',
	 * it will look for _cmpPoints. If the method exists, the criteria is accepted.
	 *
	 * @return array
	 */
	function _getRankingCriteria()
	{
		$crit   = array();
		//_cmpFinaltablerank
		if ( self::$_use_finaltablerank )
		{
			$crit[] = '_cmpFinaltablerank';
			$this->_criteria = $crit;
		}
		else
		{
		if (empty($this->_criteria))
		{
			/**
			 *
			 * get the values from ranking template setting
			 */
			$values = explode(',', $this->_params['ranking_order']);
			//$crit   = array();

			foreach ($values as $v)
			{
				$v = ucfirst(strtolower(trim($v)));

				if (method_exists($this, '_cmp' . $v))
				{
					$crit[] = '_cmp' . $v;
				}
				else
				{
					Log::add(Text::_('COM_SPORTSMANAGEMENT_RANKING_NOT_VALID_CRITERIA') . ': ' . $v, Log::WARNING, 'jsmerror');
				}
			}

			/**
			 *
			 * set a default criteria if empty
			 */
			if (!count($crit))
			{
				$crit[] = '_cmpPoints';
			}

			$this->_criteria = $crit;
		}
	}

		return $this->_criteria;
	}

	
	/**
	 * JSMRanking::getRankingHome()
	 * 
	 * @param mixed $from
	 * @param mixed $to
	 * @param mixed $division
	 * @param integer $cfg_which_database
	 * @param string $sports_type_name
	 * @return
	 */
	function getRankingHome($from = null, $to = null, $division = null, $cfg_which_database = 0,$sports_type_name='')
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		$this->_from = $from;
		$this->_to   = $to;
		$this->_mode = 1;

		if ($division == '')
		{
			$division = 0;
		}

		self::setDivisionId($division, $cfg_which_database);

		$teams    = self::_collect(null, $cfg_which_database);
		$rankings = self::_buildRanking($teams, $cfg_which_database);

		return $rankings;
	}


	/**
	 * JSMRanking::getRankingAway()
	 * 
	 * @param mixed $from
	 * @param mixed $to
	 * @param mixed $division
	 * @param integer $cfg_which_database
	 * @param string $sports_type_name
	 * @return
	 */
	function getRankingAway($from = null, $to = null, $division = null, $cfg_which_database = 0,$sports_type_name='')
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');

		$this->_from = $from;
		$this->_to   = $to;
		$this->_mode = 2;

		if ($division == '')
		{
			$division = 0;
		}

		self::setDivisionId($division, $cfg_which_database);

		$teams    = self::_collect(null, $cfg_which_database);
		$rankings = self::_buildRanking($teams, $cfg_which_database);

		return $rankings;
	}

	/**
	 * callback function to filter teams not in divisions
	 *
	 * @param   array  $team
	 */
	function _filterdivision($team)
	{
		$divs = $this->_getSubDivisions();

		if (!$divs)
		{
			return true;
		}

		return (in_array($team->_divisionid, $divs));
	}

	/**
	 * return an array of divisions matching current division id (this division, and children divisions)
	 *
	 * @return array
	 */
	function _getSubDivisions($cfg_which_database = 0)
	{
		$app    = Factory::getApplication();
		$option = $app->input->getCmd('option');
		$db     = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query  = $db->getQuery(true);

		if (!$this->_division)
		{
			return false;
		}
		elseif (empty($this->_divisions))
		{
			$query->select('id');
			$query->from('#__sportsmanagement_division ');
			$query->where('project_id = ' . $db->Quote($this->_projectid));
			$query->where('parent_id = ' . $db->Quote($this->_division));

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

			$res[]            = $this->_division;
			$this->_divisions = $res;
		}

		return $this->_divisions;
	}

	/**
	 * alphanumerical comparison
	 *
	 * @param   JSMRankingTeam a
	 * @param   JSMRankingTeam b
	 *
	 * @return integer
	 */
	function _cmpAlpha($a, $b)
	{
		$res = strcasecmp($a->getName(), $b->getName());

		return $res;
	}
	
	function _cmpFinaltablerank($a, $b)
	{
		$res = $a->_finaltablerank < $b->_finaltablerank;

		return $res;
	}

	/**
	 * **************************************************************************
	 *
	 * Compare functions (callbacks for uasort)
	 *
	 * You can add more criteria by just adding more _cmpXxxx functions, with Xxxx
	 * being the name of your criteria to be set in ranking template setting
	 *
	 *****************************************************************************/

	/**
	 * Point comparison
	 *
	 * @param   JSMRankingTeam a
	 * @param   JSMRankingTeam b
	 *
	 * @return integer
	 */
	function _cmpPoints($a, $b)
	{
		$res = -($a->getPoints() - $b->getPoints());

		return (int) $res;
	}
    
    
    /**
     * JSMRanking::_cmpShooterrings()
     * 
     * @param mixed $a
     * @param mixed $b
     * @return
     */
    function _cmpShooterrings($a, $b)
	{
		$res = -($a->shooterrings - $b->shooterrings);

		return (int) $res;
	}
    
    

	/**
	 * Point comparison
	 *
	 * @param   JSMRankingTeam a
	 * @param   JSMRankingTeam b
	 *
	 * @return integer
	 */
	function _cmpPenaltypoints($a, $b)
	{
		// $res =- ($a->penalty_points - $b->penalty_points );
		$res = -($b->penalty_points - $a->penalty_points);

		return (int) $res;
	}

	/**
	 * Bonus points comparison
	 *
	 * @param   JSMRankingTeam a
	 * @param   JSMRankingTeam b
	 *
	 * @return integer
	 */
	function _cmpBonus($a, $b)
	{
		$res = -($a->bonus_points - $b->bonus_points);

		return $res;
	}

	/**
	 * Score against comparison
	 *
	 * @param   JSMRankingTeam a
	 * @param   JSMRankingTeam b
	 *
	 * @return integer
	 */
	function _cmpAgainst($a, $b)
	{
		$res = ($a->sum_team2_result - $b->sum_team2_result);

		return $res;
	}

	/**
	 * Scoring average comparison
	 *
	 * @param   JSMRankingTeam a
	 * @param   JSMRankingTeam b
	 *
	 * @return integer
	 */
	function _cmpScoreAvg($a, $b)
	{
		$res = -($a->scoreAvg() - $b->scoreAvg());

		return $res;
	}

	/**
	 * Scoring percentage comparison
	 *
	 * @param   JSMRankingTeam a
	 * @param   JSMRankingTeam b
	 *
	 * @return integer
	 */
	function _cmpScorePct($a, $b)
	{
		$res = -($a->scorePct() - $b->scorePct());

		return $res;
	}

	/**
	 * Winning percentage comparison
	 *
	 * @param   JSMRankingTeam a
	 * @param   JSMRankingTeam b
	 *
	 * @return integer
	 */
	function _cmpWinpct($a, $b)
	{
		$res = -($a->winPct() - $b->winPct());

		if ($res != 0)
		{
			$res = ($res >= 0 ? 1 : -1);
		}

		return $res;
	}

	/**
	 * Gameback comparison (US sports)
	 *
	 * @param   JSMRankingTeam a
	 * @param   JSMRankingTeam b
	 *
	 * @return integer
	 */
	function _cmpGb($a, $b)
	{
		$res = -(($a->cnt_won - $b->cnt_won) + ($b->cnt_lost - $a->cnt_lost));

		return $res;
	}

	/**
	 * Head to Head points comparison
	 *
	 * @param   JSMRankingTeam a
	 * @param   JSMRankingTeam b
	 *
	 * @return integer
	 */
	function _cmpH2h($a, $b)
	{
		$teams = $this->_geth2h();

		// We do not include start points in h2h comparison
		$res = -($teams[$a->_ptid]->getPoints(false) - $teams[$b->_ptid]->getPoints(false));

		return $res;
	}

	/**
	 * for head to head ranking, we collect game data for current ranking group
	 */
	function _geth2h()
	{
		if (empty($this->_h2h))
		{
			$teams = $this->_h2h_group;

			if (empty($teams))
			{
				return false;
			}

			$ptids = array();

			foreach ($teams as $t)
			{
				$ptids[] = $t->_ptid;
			}

			$this->_h2h = $this->_collect($ptids);
		}

		return $this->_h2h;
	}

	/**
	 * Head to Head score difference comparison
	 *
	 * @param   JSMRankingTeam a
	 * @param   JSMRankingTeam b
	 *
	 * @return integer
	 */
	function _cmpH2h_diff($a, $b)
	{
		$teams = $this->_geth2h();

		return $this->_cmpDiff($teams[$a->_ptid], $teams[$b->_ptid]);
	}

	/**
	 * Score difference comparison
	 *
	 * @param   JSMRankingTeam a
	 * @param   JSMRankingTeam b
	 *
	 * @return integer
	 */
	function _cmpDiff($a, $b)
	{
		$res = -($a->diff_team_results - $b->diff_team_results);

		return $res;
	}

	/**
	 * Head to Head score for comparison
	 *
	 * @param   JSMRankingTeam a
	 * @param   JSMRankingTeam b
	 *
	 * @return integer
	 */
	function _cmpH2h_for($a, $b)
	{
		$teams = $this->_geth2h();

		return $this->_cmpFor($teams[$a->_ptid], $teams[$b->_ptid]);
	}

	/**
	 * Score for comparison
	 *
	 * @param   JSMRankingTeam a
	 * @param   JSMRankingTeam b
	 *
	 * @return integer
	 */
	function _cmpFor($a, $b)
	{
		$res = -($a->sum_team1_result - $b->sum_team1_result);

		return $res;
	}

	/**
	 * Head to Head scored away comparison
	 *
	 * @param   JSMRankingTeam a
	 * @param   JSMRankingTeam b
	 *
	 * @return integer
	 */
	function _cmpH2h_away($a, $b)
	{
		$teams = $this->_geth2h();

		return $this->_cmpAwayfor($teams[$a->_ptid], $teams[$b->_ptid]);
	}

	/**
	 * Score away comparison
	 *
	 * @param   JSMRankingTeam a
	 * @param   JSMRankingTeam b
	 *
	 * @return integer
	 */
	function _cmpAwayfor($a, $b)
	{
		$res = -($a->sum_away_for - $b->sum_away_for);

		return $res;
	}

	/**
	 * Legs diff comparison
	 *
	 * @param   JSMRankingTeam a
	 * @param   JSMRankingTeam b
	 *
	 * @return integer
	 */
	function _cmpLegs_diff($a, $b)
	{
		$res = -($a->diff_team_legs - $b->diff_team_legs);

		return $res;
	}

	/**
	 * Legs ratio comparison
	 *
	 * @param   JSMRankingTeam a
	 * @param   JSMRankingTeam b
	 *
	 * @return integer
	 */
	function _cmpLegs_ratio($a, $b)
	{
		$res = -($a->legsRatio() - $b->legsRatio());

		if ($res != 0)
		{
			$res = ($res >= 0 ? 1 : -1);
		}

		return $res;
	}

	/**
	 * Legs wins comparison
	 *
	 * @param   JSMRankingTeam a
	 * @param   JSMRankingTeam b
	 *
	 * @return integer
	 */
	function _cmpLegs_win($a, $b)
	{
		$res = -($a->sum_team1_legs - $b->sum_team1_legs);

		return $res;
	}

	/**
	 * Total wins comparison
	 *
	 * @param   JSMRankingTeam a
	 * @param   JSMRankingTeam b
	 *
	 * @return integer
	 */
	function _cmpWins($a, $b)
	{
		$res = -($a->cnt_won - $b->cnt_won);

		return $res;
	}

	/**
	 * Games played ASC comparison, less games played, higher in rank
	 *
	 * @param   JSMRankingTeam a
	 * @param   JSMRankingTeam b
	 *
	 * @return integer
	 */
	function _cmpPlayedasc($a, $b)
	{
		$res = -($this->_cmpPlayed($a, $b));

		return $res;
	}

	/**
	 * Games played comparison, more games played, higher in rank
	 *
	 * @param   JSMRankingTeam a
	 * @param   JSMRankingTeam b
	 *
	 * @return integer
	 */
	function _cmpPlayed($a, $b)
	{
		$res = -($a->cnt_matches - $b->cnt_matches);

		return $res;
	}

	/**
	 * Points ratio comparison
	 *
	 * @param   JSMRankingTeam a
	 * @param   JSMRankingTeam b
	 *
	 * @return integer
	 */
	function _cmpPoints_ratio($a, $b)
	{
		$res = -($a->pointsRatio() - $b->pointsRatio());

		if ($res != 0)
		{
			$res = ($res >= 0 ? 1 : -1);
		}

		return $res;
	}

	/**
	 * OT_wins comparison
	 *
	 * @param   JSMRankingTeam a
	 * @param   JSMRankingTeam b
	 *
	 * @return integer
	 */
	function _cmpWOT($a, $b)
	{
		$res = -($a->cnt_wot - $b->cnt_wot);

		return $res;
	}

	/**
	 * SO_wins comparison
	 *
	 * @param   JSMRankingTeam a
	 * @param   JSMRankingTeam b
	 *
	 * @return integer
	 */
	function _cmpWSO($a, $b)
	{
		$res = -($a->cnt_wso - $b->cnt_wso);

		return $res;
	}
}

/**
 * Ranking team class
 * Support class for ranking helper
 */

/**
 * JSMRankingTeam
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JSMRankingTeamClass
{
	var $_use_finally = 0;
	var $_finaltablerank = 0;
	var $_points_finally = 0;
	var $_neg_points_finally = 0;
	var $_matches_finally = 0;
	var $_won_finally = 0;
	var $_draws_finally = 0;
	var $_lost_finally = 0;
	var $_homegoals_finally = 0;
	var $_guestgoals_finally = 0;
	var $_diffgoals_finally = 0;
	var $_is_in_score = 0;
	var $_ptid = 0;
	var $_teamid = 0;
	var $_divisionid = 0;
	var $_startpoints = 0;
	var $_name = null;
	var $cnt_matches = 0;
	var $cnt_won = 0;
	var $cnt_draw = 0;
	var $cnt_lost = 0;
	var $cnt_won_home = 0;
	var $cnt_draw_home = 0;
	var $cnt_lost_home = 0;
	var $cnt_won_away = 0;
	var $cnt_draw_away = 0;
	var $cnt_lost_away = 0;
	var $cnt_wot = 0;
	var $cnt_wso = 0;
	var $cnt_lot = 0;
	var $cnt_lso = 0;
	var $cnt_wot_home = 0;
	var $cnt_wso_home = 0;
	var $cnt_lot_home = 0;
	var $cnt_lso_home = 0;
	var $cnt_wot_away = 0;
	var $cnt_wso_away = 0;
	var $cnt_lot_away = 0;
	var $cnt_lso_away = 0;
	var $sum_points = 0;
	var $neg_points = 0;
	var $bonus_points = 0;
	var $sum_team1_result = 0;
	var $sum_team2_result = 0;
	var $sum_away_for = 0;
	var $sum_team1_legs = 0;
	var $sum_team2_legs = 0;
	var $sum_team1_matchpoint = 0;
	var $sum_team2_matchpoint = 0;
	var $sum_team1_sets = 0;
	var $sum_team2_sets = 0;
	var $sum_team1_games = 0;
	var $sum_team2_games = 0;
	var $diff_team_results = 0;
	var $diff_team_legs = 0;
	var $round = 0;
	var $rank = 0;
    var $shooterrings = 0;
    var $shooterringsperround = array();
	var $sum_team1_balls = 0;
	var $sum_team2_balls = 0;
	var $diff_team_balls = 0;
    

	/**
	 * JSMRankingTeamClass::JSMRankingTeam()
	 *
	 * @param   mixed  $ptid
	 *
	 * @return void
	 */
	function JSMRankingTeam($ptid)
	{
		$this->setPtid($ptid);
	}

	/**
	 * JSMRankingTeamClass::setis_in_score()
	 *
	 * @param   mixed  $val
	 *
	 * @return void
	 */
	function setis_in_score($val)
	{
		$this->_is_in_score = (int) $val;
	}

	/**
	 * JSMRankingTeamClass::setuse_finally()
	 *
	 * @param   mixed  $val
	 *
	 * @return void
	 */
	function setuse_finally($val)
	{
		$this->_use_finally = (int) $val;
	}
	
	function setuse_finaltablerank($val)
	{
		$this->_finaltablerank = (int) $val;
	}

	/**
	 * JSMRankingTeamClass::setpoints_finally()
	 *
	 * @param   mixed  $val
	 *
	 * @return void
	 */
	function setpoints_finally($val)
	{
		$this->_points_finally = (int) $val;
	}

	/**
	 * JSMRankingTeamClass::setneg_points_finally()
	 *
	 * @param   mixed  $val
	 *
	 * @return void
	 */
	function setneg_points_finally($val)
	{
		$this->_neg_points_finally = (int) $val;
	}

	/**
	 * JSMRankingTeamClass::setmatches_finally()
	 *
	 * @param   mixed  $val
	 *
	 * @return void
	 */
	function setmatches_finally($val)
	{
		$this->_matches_finally = (int) $val;
	}

	/**
	 * JSMRankingTeamClass::setwon_finally()
	 *
	 * @param   mixed  $val
	 *
	 * @return void
	 */
	function setwon_finally($val)
	{
		$this->_won_finally = (int) $val;
	}

	/**
	 * JSMRankingTeamClass::setdraws_finally()
	 *
	 * @param   mixed  $val
	 *
	 * @return void
	 */
	function setdraws_finally($val)
	{
		$this->_draws_finally = (int) $val;
	}

	/**
	 * JSMRankingTeamClass::setlost_finally()
	 *
	 * @param   mixed  $val
	 *
	 * @return void
	 */
	function setlost_finally($val)
	{
		$this->_lost_finally = (int) $val;
	}

	/**
	 * JSMRankingTeamClass::sethomegoals_finally()
	 *
	 * @param   mixed  $val
	 *
	 * @return void
	 */
	function sethomegoals_finally($val)
	{
		$this->_homegoals_finally = (int) $val;
	}

	/**
	 * JSMRankingTeamClass::setguestgoals_finally()
	 *
	 * @param   mixed  $val
	 *
	 * @return void
	 */
	function setguestgoals_finally($val)
	{
		$this->_guestgoals_finally = (int) $val;
	}

	/**
	 * JSMRankingTeamClass::setdiffgoals_finally()
	 *
	 * @param   mixed  $val
	 *
	 * @return void
	 */
	function setdiffgoals_finally($val)
	{
		$this->_diffgoals_finally = (int) $val;
	}

	/**
	 * JSMRankingTeamClass::getPtid()
	 *
	 * @return
	 */
	function getPtid()
	{
		return $this->_ptid;
	}

	/**
	 * JSMRankingTeamClass::setPtid()
	 *
	 * @param   mixed  $ptid
	 *
	 * @return void
	 */
	function setPtid($ptid)
	{
		$this->_ptid = (int) $ptid;
	}

	/**
	 * JSMRankingTeamClass::getTeamid()
	 *
	 * @return
	 */
	function getTeamid()
	{
		return $this->_teamid;
	}

	/**
	 * JSMRankingTeamClass::setTeamid()
	 *
	 * @param   mixed  $id
	 *
	 * @return void
	 */
	function setTeamid($id)
	{
		$this->_teamid = (int) $id;
	}

	/**
	 * JSMRankingTeamClass::getDivisionid()
	 *
	 * @return
	 */
	function getDivisionid()
	{
		return $this->_divisionid;
	}

	/**
	 * JSMRankingTeamClass::setDivisionid()
	 *
	 * @param   mixed  $val
	 *
	 * @return void
	 */
	function setDivisionid($val)
	{
		$this->_divisionid = (int) $val;
	}

	/**
	 * JSMRankingTeamClass::setStartpoints()
	 *
	 * @param   mixed  $val
	 *
	 * @return void
	 */
	function setStartpoints($val)
	{
		$this->_startpoints = $val;
	}

	/**
	 * JSMRankingTeamClass::setNegpoints()
	 *
	 * @param   mixed  $val
	 *
	 * @return void
	 */
	function setNegpoints($val)
	{
		$this->neg_points = $val;
	}

	/**
	 * JSMRankingTeamClass::winPct()
	 *
	 * @return
	 */
	function winPct()
	{
		if ($this->cnt_won + $this->cnt_lost + $this->cnt_draw == 0)
		{
			return 0;
		}
		else
		{
			return ($this->cnt_won / ($this->cnt_won + $this->cnt_lost + $this->cnt_draw)) * 100;
		}
	}

	/**
	 * JSMRankingTeamClass::scorePct()
	 *
	 * @return
	 */
	function scorePct()
	{
		$result = $this->scoreAvg() * 100;

		return $result;
	}

	/**
	 * JSMRankingTeamClass::scoreAvg()
	 *
	 * @return
	 */
	function scoreAvg()
	{
		if ($this->sum_team2_result == 0)
		{
			return $this->sum_team1_result / 1;
		}
		else
		{
			return $this->sum_team1_result / $this->sum_team2_result;
		}
	}

	/**
	 * JSMRankingTeamClass::legsRatio()
	 *
	 * @return
	 */
	function legsRatio()
	{
		if ($this->sum_team2_legs == 0)
		{
			return $this->sum_team1_legs / 1;
		}
		else
		{
			return $this->sum_team1_legs / $this->sum_team2_legs;
		}
	}

	/**
	 * JSMRankingTeamClass::pointsRatio()
	 *
	 * @return
	 */
	function pointsRatio()
	{
		if ($this->neg_points == 0)
		{
			// We do not include start points
			return $this->getPoints(false) / 1;
		}
		else
		{
			// We do not include start points
			return $this->getPoints(false) / $this->neg_points;
		}
	}

	/**
	 * JSMRankingTeamClass::getPoints()
	 *
	 * @param   bool  $include_start
	 *
	 * @return
	 */
	function getPoints($include_start = true)
	{
		if ($include_start)
		{
			return $this->sum_points + $this->_startpoints;
		}
		else
		{
			return $this->sum_points;
		}
	}

	/**
	 * JSMRankingTeamClass::pointsQuot()
	 *
	 * @return
	 */
	function pointsQuot()
	{
		if ($this->cnt_matches == 0)
		{
			// We do not include start points
			return $this->getPoints(false) / 1;
		}
		else
		{
			// We do not include start points
			return $this->getPoints(false) / $this->cnt_matches;
		}
	}


	/**
	 * JSMRankingTeam::getName()
	 *
	 * @return
	 */
	function getName()
	{
		return $this->_name;
	}

	/**
	 * JSMRankingTeamClass::setName()
	 *
	 * @param   mixed  $val
	 *
	 * @return void
	 */
	function setName($val)
	{
		$this->_name = $val;
	}

	/**
	 * GFA:Goal For Average per match = Goal for / played matches
	 *
	 * @return float
	 */
	function getGFA()
	{
		if ($this->cnt_matches == 0)
		{
			return $this->sum_team1_result / 1;
		}
		else
		{
			return $this->sum_team1_result / $this->cnt_matches;
		}
	}

	/**
	 * JSMRankingTeamClass::getGAA()
	 * GAA:Goal Against Average per match = Goal against / played matches
	 *
	 * @return float
	 */
	function getGAA()
	{
		if ($this->cnt_matches == 0)
		{
			return $this->sum_team2_result / 1;
		}
		else
		{
			return $this->sum_team2_result / $this->cnt_matches;
		}
	}

	/**
	 * JSMRankingTeamClass::getPPG()
	 * PpG:Points per Game = points / played matches
	 *
	 * @return float
	 */
	function getPPG()
	{
		if ($this->cnt_matches == 0)
		{
			return $this->getPoints(false) / 1;
		}
		else
		{
			return $this->getPoints(false) / $this->cnt_matches;
		}
	}

	/**
	 * JSMRankingTeamClass::getPPP()
	 * %PP:Team points in relation into max points = (points / (played matches*win points))*100
	 *
	 * @return float
	 */
	function getPPP()
	{
		if (($this->cnt_matches * $this->winpoints) == 0)
		{
			return $this->getPoints(false) / 1;
		}
		else
		{
			return ($this->getPoints(false) / ($this->cnt_matches * $this->winpoints)) * 100;
		}
	}
}
