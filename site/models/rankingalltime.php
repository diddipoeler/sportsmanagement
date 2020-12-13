<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage rankingalltime
 * @file       rankingalltime.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Registry\Registry;
use Joomla\CMS\Log\Log;

jimport('joomla.utilities.array');
jimport('joomla.utilities.arrayhelper');

if (!defined('JSM_PATH'))
{
	DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

$maxImportTime = 480;

if ((int) ini_get('max_execution_time') < $maxImportTime)
{
	@set_time_limit($maxImportTime);
}

/**
 * sportsmanagementModelRankingAllTime
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelRankingAllTime extends BaseDatabaseModel
{
	static $classname = 'sportsmanagementModelRanking';
	var $teams = array();
	var $_teams = array();
	var $_matches = array();
	var $alltimepoints = '';
	var $debug_info = false;
	var $projectid = 0;
	var $leagueid = 0;
	/**
	 * ranking parameters
	 * @var array
	 */
	var $_params = null;

	/**
	 * criteria for ranking order
	 * @var array
	 */
	var $_criteria = null;


	/**
	 * sportsmanagementModelRankingAllTime::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		$app                 = Factory::getApplication();
		$jinput              = $app->input;
		$this->alltimepoints = $jinput->request->get('points', '3,1,0', 'STR');

		$file = JPATH_SITE . DIRECTORY_SEPARATOR . JSM_PATH . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'ranking.php';
		require_once $file;

		$menu = JMenu::getInstance('site');
		$item = $menu->getActive();
        if ( !property_exists($item, 'query') )
		{
		$item->query['view'] = '';  
		}
        if ( !property_exists($item, 'id') )
		{
		$item->id = 0;  
		}

		$params = $menu->getParams($item->id);

		if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO)
		{
			$this->debug_info = true;
		}
		else
		{
			$this->debug_info = false;
		}

		if ($item->query['view'] == 'rankingalltime')
		{
			// Diddipoeler
			// menueeintrag vorhanden

			$registry = new Registry;
			$registry->loadArray($params);
			$newparams = $registry->toArray();

			foreach ($newparams['data'] as $key => $value)
			{
				$this->_params[$key] = $value;
			}
		}
		else
		{
			$strXmlFile = JPATH_SITE . DIRECTORY_SEPARATOR . JSM_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'rankingalltime' . DIRECTORY_SEPARATOR . 'tmpl' . DIRECTORY_SEPARATOR . 'default.xml';
			$xml      = Factory::getXML($strXmlFile);
			$children = $xml->document->children();
			/** We can now step through each element of the file */
			foreach ($xml->children() as $field)
			{
				foreach ($field->fieldset as $fieldset)
				{
					foreach ($fieldset->field as $param)
					{
						$attributes = $param->attributes();

						$this->_params[(string) $param->attributes()->name[0]] = (string) $param->attributes()->default[0];
					}
				}
			}
		}

		parent::__construct();

	}


	/**
	 * sportsmanagementModelRankingAllTime::getAllTeamsIndexedByPtid()
	 *
	 * @param   mixed  $project_ids
	 *
	 * @return
	 */
	function getAllTeamsIndexedByPtid($project_ids)
	{
		$app    = Factory::getApplication();
		$result = self::getAllTeams($project_ids);

		if (count($result))
		{
			foreach ($result as $r)
			{
				if (!array_key_exists($r->team_id, $this->teams))
				{
					$this->teams[$r->team_id] = $r;
                    $this->teams[$r->team_id]->cnt_matches       = 0;
					$this->teams[$r->team_id]->sum_points        = 0;
					$this->teams[$r->team_id]->neg_points        = 0;
					$this->teams[$r->team_id]->cnt_won_home      = 0;
					$this->teams[$r->team_id]->cnt_draw_home     = 0;
					$this->teams[$r->team_id]->cnt_lost_home     = 0;
					$this->teams[$r->team_id]->cnt_won           = 0;
					$this->teams[$r->team_id]->cnt_draw          = 0;
					$this->teams[$r->team_id]->cnt_lost          = 0;
					$this->teams[$r->team_id]->sum_team1_result  = 0;
					$this->teams[$r->team_id]->sum_team2_result  = 0;
					$this->teams[$r->team_id]->sum_away_for      = 0;
					$this->teams[$r->team_id]->diff_team_results = 0;
                    
                    $this->teams[$r->team_id]->sum_team1_legs = 0;
                    $this->teams[$r->team_id]->sum_team2_legs = 0;
                    
                    $this->teams[$r->team_id]->cnt_lost_away = 0;
                    $this->teams[$r->team_id]->bonus_points = 0;
                    $this->teams[$r->team_id]->cnt_won_away = 0;
                    $this->teams[$r->team_id]->cnt_draw_away = 0;
                    
				}

				if ($r->use_finally)
				{
					$this->teams[$r->team_id]->sum_points        += $r->points_finally;
					$this->teams[$r->team_id]->neg_points        += $r->neg_points_finally;
					$this->teams[$r->team_id]->cnt_matches       += $r->matches_finally;
					$this->teams[$r->team_id]->cnt_won           += $r->won_finally;
					$this->teams[$r->team_id]->cnt_draw          += $r->draws_finally;
					$this->teams[$r->team_id]->cnt_lost          += $r->lost_finally;
					$this->teams[$r->team_id]->sum_team1_result  += $r->homegoals_finally;
					$this->teams[$r->team_id]->sum_team2_result  += $r->guestgoals_finally;
					$this->teams[$r->team_id]->diff_team_results += $r->diffgoals_finally;
				}
				else
				{
					$this->teams[$r->team_id]->cnt_matches       = 0;
					$this->teams[$r->team_id]->sum_points        = 0;
					$this->teams[$r->team_id]->neg_points        = 0;
					$this->teams[$r->team_id]->cnt_won_home      = 0;
					$this->teams[$r->team_id]->cnt_draw_home     = 0;
					$this->teams[$r->team_id]->cnt_lost_home     = 0;
					$this->teams[$r->team_id]->cnt_won           = 0;
					$this->teams[$r->team_id]->cnt_draw          = 0;
					$this->teams[$r->team_id]->cnt_lost          = 0;
					$this->teams[$r->team_id]->sum_team1_result  = 0;
					$this->teams[$r->team_id]->sum_team2_result  = 0;
					$this->teams[$r->team_id]->sum_away_for      = 0;
					$this->teams[$r->team_id]->diff_team_results = 0;
				}

				$this->teams[$r->team_id]->round = 0;
				$this->teams[$r->team_id]->rank  = 0;
			}
		}

		$count_teams = count($this->teams);
		Log::add(Text::_('Wir verarbeiten ' . $count_teams . ' Vereine !'), Log::INFO, 'jsmerror');

		// Echo 'result <pre>'.print_r($result,true).'</pre>';
		// echo 'teams <pre>'.print_r($this->teams,true).'</pre>';

		return $this->teams;
	}

	/**
	 * sportsmanagementModelRankingAllTime::getAllTeams()
	 *
	 * @param   mixed  $project_ids
	 *
	 * @return
	 */
	function getAllTeams($project_ids)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();

		// Get a db connection.
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->clear();

		if ($project_ids)
		{
			$query->select('tl.id AS projectteamid,tl.division_id');
			$query->select('tl.standard_playground,tl.admin,tl.start_points');
			$query->select('tl.use_finally,tl.points_finally,tl.neg_points_finally,tl.matches_finally,tl.won_finally,tl.draws_finally,tl.lost_finally,tl.homegoals_finally,tl.guestgoals_finally,tl.diffgoals_finally');
			$query->select('tl.is_in_score,tl.info,st.team_id,tl.checked_out,tl.checked_out_time');
			$query->select('tl.picture,tl.project_id');
			$query->select('t.id,t.name,t.short_name,t.middle_name,t.notes,t.club_id');
			$query->select('c.unique_id,c.email as club_email,c.logo_small,c.logo_middle,c.logo_big,c.country, c.website');
			$query->select('d.name AS division_name,d.shortname AS division_shortname,d.parent_id AS parent_division_id');
			$query->select('plg.name AS playground_name,plg.short_name AS playground_short_name');

			$query->select('CONCAT_WS( \':\', p.id, p.alias ) AS project_slug');
			$query->select('CONCAT_WS( \':\', t.id, t.alias ) AS team_slug');
			$query->select('CONCAT_WS( \':\', d.id, d.alias ) AS division_slug');
			$query->select('CONCAT_WS( \':\', c.id, c.alias ) AS club_slug');

			$query->from('#__sportsmanagement_project_team as tl');
			$query->join('LEFT', '#__sportsmanagement_season_team_id AS st ON st.id = tl.team_id');
			$query->join('LEFT', '#__sportsmanagement_team as t ON st.team_id = t.id');
			$query->join('LEFT', '#__sportsmanagement_club as c ON t.club_id = c.id');
			$query->join('LEFT', '#__sportsmanagement_division as d ON d.id = tl.division_id');
			$query->join('LEFT', '#__sportsmanagement_playground as plg ON plg.id = tl.standard_playground');
			$query->join('LEFT', '#__sportsmanagement_project AS p ON p.id = tl.project_id');

			$query->where('tl.project_id IN (' . $project_ids . ')');
try
{
			$db->setQuery($query);
			$this->_teams = $db->loadObjectList();
            }
catch (Exception $e)
{
    Log::add(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), Log::INFO, 'jsmerror');
    Log::add(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), Log::INFO, 'jsmerror');
}

			if (!$this->_teams)
			{
			}

			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			return $this->_teams;
		}
		else
		{
			Log::add(Text::_('COM_SPORTSMANAGEMENT_NO_RANKING_PROJECTINFO'), Log::WARNING, 'jsmerror');

			return false;
		}

	}

	/**
	 * sportsmanagementModelRankingAllTime::getAllMatches()
	 *
	 * @param   mixed  $projects
	 *
	 * @return
	 */
	function getAllMatches($projects)
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();
		$res    = '';

		// Get a db connection.
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->clear();

		if ($projects)
		{
			$query->select('m.id,m.projectteam1_id,m.projectteam2_id,m.team1_result AS home_score,m.team2_result AS away_score,m.team1_bonus AS home_bonus,m.team2_bonus AS away_bonus');
			$query->select('m.team1_legs AS l1,m.team2_legs AS l2,m.match_result_type AS match_result_type,m.alt_decision as decision,m.team1_result_decision AS home_score_decision');
			$query->select('m.team2_result_decision AS away_score_decision,m.team1_result_ot AS home_score_ot,m.team2_result_ot AS away_score_ot,m.team1_result_so AS home_score_so,m.team2_result_so AS away_score_so');
			$query->select('t1.id AS team1_id,t2.id AS team2_id');
			$query->select('r.id as roundid, m.team_won, r.roundcode');

			$query->from('#__sportsmanagement_match m');
			$query->join('INNER', '#__sportsmanagement_project_team AS pt1 ON m.projectteam1_id = pt1.id ');
			$query->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id');
			$query->join('INNER', '#__sportsmanagement_team t1 ON st1.team_id = t1.id ');
			$query->join('INNER', '#__sportsmanagement_project_team AS pt2 ON m.projectteam2_id = pt2.id ');
			$query->join('INNER', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id');
			$query->join('INNER', '#__sportsmanagement_team t2 ON st2.team_id = t2.id ');
			$query->join('INNER', '#__sportsmanagement_round AS r ON m.round_id = r.id ');

			$query->where('((m.team1_result IS NOT NULL AND m.team2_result IS NOT NULL) OR (m.alt_decision=1))');
			$query->where('m.published = 1');
			$query->where('r.published = 1');
			$query->where('pt1.project_id IN (' . $projects . ')');
			$query->where('(m.cancel IS NULL OR m.cancel = 0)');
			$query->where('m.projectteam1_id > 0 AND m.projectteam2_id > 0');

			$db->setQuery($query);
			$res = $db->loadObjectList();

			if (!$res)
			{
				Log::add(Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_NO_MATCHES'), Log::ERROR, 'jsmerror');
			}

			$this->_matches = $res;

			$count_matches = count($res);
			Log::add(Text::_('Wir verarbeiten ' . $count_matches . ' Spiele !'), Log::INFO, 'jsmerror');
		}
		else
		{
			Log::add(Text::_('COM_SPORTSMANAGEMENT_NO_RANKING_PROJECTINFO'), Log::WARNING, 'jsmerror');

			return false;
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $res;

	}

	/**
	 * sportsmanagementModelRankingAllTime::getAllTimeRanking()
	 *
	 * @return
	 */
	function getAllTimeRanking()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();

		$arr = explode(",", $this->alltimepoints);

		foreach ((array) $this->_matches as $match)
		{
			$resultType = $match->match_result_type;
			$decision   = $match->decision;

			if ($decision == 0)
			{
				$home_score = $match->home_score;
				$away_score = $match->away_score;
				$leg1       = $match->l1;
				$leg2       = $match->l2;
			}
			else
			{
				$home_score = $match->home_score_decision;
				$away_score = $match->away_score_decision;
				$leg1       = 0;
				$leg2       = 0;
			}

			$homeId = $match->team1_id;
			$awayId = $match->team2_id;
			$home   = &$this->teams[$homeId];
			$away   = &$this->teams[$awayId];

			$home->cnt_matches++;
			$away->cnt_matches++;

			$win_points  = (isset($arr[0])) ? $arr[0] : 3;
			$draw_points = (isset($arr[1])) ? $arr[1] : 1;
			$loss_points = (isset($arr[2])) ? $arr[2] : 0;

			if ($loss_points)
			{
				$shownegpoints = 1;
			}
			else
			{
				$shownegpoints = 0;
			}

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
							$home->cnt_won++;
							$home->cnt_won_home++;

							$away->cnt_lot++;
							$away->cnt_lot_away++;

							// When LOT, LOT=1 but No LOSS Count(Hockey)
							// $away->cnt_lost++;
							// $away->cnt_lost_home++;
							break;
						case 2:
							$home->cnt_wso++;
							$home->cnt_wso_home++;
							$home->cnt_won++;
							$home->cnt_won_home++;

							$away->cnt_lso++;
							$away->cnt_lso_away++;
							$away->cnt_lot++;
							$away->cnt_lot_away++;

							// When LSO ,LSO=1 and LOT=1 but No LOSS Count (Hockey)
							// $away->cnt_lost++;
							// $away->cnt_lost_home++;
							break;
					}

					$home->sum_points += $win_points; // Home_score can't be null...
					$away->sum_points += ($decision == 0 || isset($away_score) ? $loss_points : 0);

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
								$home->cnt_won++;
								$home->cnt_won_home++;
								$home->cnt_wot++;
								$home->cnt_wot_home++;

								$away->cnt_lost++;
								$away->cnt_lost_away++;
								$away->cnt_lot++;
								$away->cnt_lot_away++;
							}

							if ($home_ot < $away_ot)
							{
								$away->cnt_won++;
								$away->cnt_won_home++;
								$away->cnt_wot++;
								$away->cnt_wot_home++;

								$home->cnt_lost++;
								$home->cnt_lost_away++;
								$home->cnt_lot++;
								$home->cnt_lot_away++;
							}
							break;
						case 2:
							if ($home_so > $away_so)
							{
								$home->cnt_won++;
								$home->cnt_won_home++;
								$home->cnt_wso++;
								$home->cnt_wso_home++;

								$away->cnt_lost++;
								$away->cnt_lost_away++;
								$away->cnt_lso++;
								$away->cnt_lso_away++;
							}

							if ($home_so < $away_so)
							{
								$away->cnt_won++;
								$away->cnt_won_home++;
								$away->cnt_wso++;
								$away->cnt_wso_home++;

								$home->cnt_lost++;
								$home->cnt_lost_away++;
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

							// When LOT, LOT=1 but No LOSS Count(Hockey)
							// $home->cnt_lost++;
							// $home->cnt_lost_home++;

							$away->cnt_wot++;
							$away->cnt_wot_away++;
							$away->cnt_won++;
							$away->cnt_won_away++;
							break;
						case 2:
							$home->cnt_lso++;
							$home->cnt_lso_home++;
							$home->cnt_lot++;
							$home->cnt_lot_home++;

							// When LSO ,LSO=1 and LOT=1 but No LOSS Count (Hockey)
							// $home->cnt_lost++;
							// $home->cnt_lost_home++;

							$away->cnt_wso++;
							$away->cnt_wso_away++;
							$away->cnt_won++;
							$away->cnt_won_away++;
							break;
					}

					$home->sum_points += ($decision == 0 || isset($home_score) ? $loss_points : 0);
					$away->sum_points += $win_points;

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

			$home->sum_points   += $match->home_bonus;
			$home->bonus_points += $match->home_bonus;

			$away->sum_points   += $match->away_bonus;
			$away->bonus_points += $match->away_bonus;

			// Goals for/against/diff

			$home->sum_team1_result  += $home_score;
			$home->sum_team2_result  += $away_score;
			$home->diff_team_results = $home->sum_team1_result - $home->sum_team2_result;
			$home->sum_team1_legs    += $leg1;
			$home->sum_team2_legs    += $leg2;
			$home->diff_team_legs    = $home->sum_team1_legs - $home->sum_team2_legs;

			$away->sum_team1_result  += $away_score;
			$away->sum_team2_result  += $home_score;
			$away->diff_team_results = $away->sum_team1_result - $away->sum_team2_result;
			$away->sum_team1_legs    += $leg2;
			$away->sum_team2_legs    += $leg1;
			$away->diff_team_legs    = $away->sum_team1_legs - $away->sum_team2_legs;

			$away->sum_away_for += $away_score;
		}

		/**
		 * hier werden die werte aus dem projekt dem team dazu addiert
		 * wenn die werte des projektteams auch genutzt werden sollen
		 * dazu müssen die endpunkte neu berechnet werden, da die punkteverteilung
		 * über den request anders übergeben werden. z.b. 2 punkte oder 3 punkte für
		 * einen sieg
		 * $win_points $draw_points $loss_points
		 *
		 *
		 */
		/*
		foreach ( $this->_teams as $team )
		{
		if ( $team->use_finally )
		{
		if ( $win_points )
        {
		if ( $team->won_finally && $team->draws_finally )
        {
		$team->points_finally = ( $win_points * $team->won_finally ) + ( $draw_points * $team->draws_finally );
		}
		if ( $team->lost_finally && $team->draws_finally )
		{
		$team->neg_points_finally = ( $loss_points * $team->lost_finally ) + ( $draw_points * $team->draws_finally );
		}
		}
		$this->teams[$team->team_id]->sum_points += $team->points_finally;
		$this->teams[$team->team_id]->neg_points += $team->neg_points_finally;
		$this->teams[$team->team_id]->cnt_matches += $team->matches_finally;
		$this->teams[$team->team_id]->cnt_won += $team->won_finally;
		$this->teams[$team->team_id]->cnt_draw += $team->draws_finally;
		$this->teams[$team->team_id]->cnt_lost += $team->lost_finally;
		$this->teams[$team->team_id]->sum_team1_result += $team->homegoals_finally;
		$this->teams[$team->team_id]->sum_team2_result += $team->guestgoals_finally;
		$this->teams[$team->team_id]->diff_team_results += $team->diffgoals_finally;
        }
		}
		*/

		return $this->teams;

	}

	/**
	 * sportsmanagementModelRankingAllTime::getColors()
	 *
	 * @param   string  $configcolors
	 *
	 * @return
	 */
	function getColors($configcolors = '')
	{
		$s = substr($configcolors, 0, -1);

		$arr1 = array();

		if (trim($s) != "")
		{
			$arr1 = explode(";", $s);
		}

		$colors = array();

		$colors[0]["from"]        = "";
		$colors[0]["to"]          = "";
		$colors[0]["color"]       = "";
		$colors[0]["description"] = "";

		for ($i = 0; $i < count($arr1); $i++)
		{
			$arr2 = explode(",", $arr1[$i]);

			if (count($arr2) != 4)
			{
				break;
			}

			$colors[$i]["from"]        = $arr2[0];
			$colors[$i]["to"]          = $arr2[1];
			$colors[$i]["color"]       = $arr2[2];
			$colors[$i]["description"] = $arr2[3];
		}

		return $colors;
	}


	/**
	 * sportsmanagementModelRankingAllTime::getAllProjectNames()
	 * 
	 * @return
	 */
	function getAllProjectNames()
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		$jinput = $app->input;
		$league = $jinput->request->get('l', 0, 'INT');

		$db    = Factory::getDBO();
		$query = Factory::getDbo()->getQuery(true);

		if (!$league)
		{
			$projekt = $jinput->request->get('p', 0, 'INT');
			$query->clear();
			$query->select('league_id');
			$query->from('#__sportsmanagement_project');
			$query->where('id = ' . $projekt);
			$query->order('name ');
			$db->setQuery($query);
            try
            {
			$league = $db->loadResult();
            }
catch (Exception $e)
{
    Log::add(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), Log::INFO, 'jsmerror');
    Log::add(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), Log::INFO, 'jsmerror');
}
		}

		$query->clear();
		$query->select('p.id,p.name,s.name as seasonname');
		$query->select('CONCAT_WS(\':\',p.id,p.alias) AS project_slug');
		$query->from('#__sportsmanagement_project as p');
		$query->join('INNER', '#__sportsmanagement_season AS s ON p.season_id = s.id ');
		$query->where('p.league_id = ' . $league);
		$query->order('s.name DESC ');
		$db->setQuery($query);
        try
        {
		$result = $db->loadObjectList();
        }
catch (Exception $e)
{
    Log::add(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), Log::INFO, 'jsmerror');
    Log::add(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), Log::INFO, 'jsmerror');
}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;

	}

	/**
	 * sportsmanagementModelRankingAllTime::getAllProject()
	 *
	 * @return
	 */
	function getAllProject()
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');
		$jinput = $app->input;
		$league = $jinput->request->get('l', 0, 'INT');

		$db    = Factory::getDBO();
		$query = Factory::getDbo()->getQuery(true);

		if (!$league)
		{
			$projekt = $jinput->request->get('p', 0, 'INT');
			$query->clear();
			$query->select('league_id');
			$query->from('#__sportsmanagement_project');
			$query->where('id = ' . $projekt);
			$query->order('name ');
			$db->setQuery($query);
			$league = $db->loadResult();
		}

		$query->clear();
		$query->select('id');
		$query->from('#__sportsmanagement_project');
		$query->where('league_id = ' . $league);
		$query->order('name ');
		$db->setQuery($query);
try{
		$result = $db->loadColumn(0);

		$this->project_ids       = implode(",", $result);
		$this->project_ids_array = $result;

		$count_project = count($result);
		Log::add(Text::_('Wir verarbeiten ' . $count_project . ' Projekte/Saisons !'), Log::INFO, 'jsmerror');
        
        }
catch (Exception $e)
{
    Log::add(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), Log::INFO, 'jsmerror');
    Log::add(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), Log::INFO, 'jsmerror');
}
        
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		return $result;
	}

	/**
	 * sportsmanagementModelRankingAllTime::getAllTimeParams()
	 *
	 * @return
	 */
	function getAllTimeParams()
	{
		return $this->_params;
	}

	/**
	 * sportsmanagementModelRankingAllTime::getCurrentRanking()
	 *
	 * @return
	 */
	function getCurrentRanking()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();

		$newranking = array();

		foreach ($this->teams as $key)
		{
			//            $new = new stdclass(0);
			$new                    = new JSMRankingTeamClass(0);
			$new->cnt_matches       = $key->cnt_matches;
			$new->sum_points        = $key->sum_points;
			$new->neg_points        = $key->neg_points;
			$new->cnt_won_home      = $key->cnt_won_home;
			$new->cnt_draw_home     = $key->cnt_draw_home;
			$new->cnt_lost_home     = $key->cnt_lost_home;
			$new->cnt_won           = $key->cnt_won;
			$new->cnt_draw          = $key->cnt_draw;
			$new->cnt_lost          = $key->cnt_lost;
			$new->sum_team1_result  = $key->sum_team1_result;
			$new->sum_team2_result  = $key->sum_team2_result;
			$new->sum_away_for      = $key->sum_away_for;
			$new->diff_team_results = $key->diff_team_results;
			$new->_is_in_score      = $key->is_in_score;
			$new->_teamid           = $key->team_id;
			$new->_name             = $key->name;
			$new->_ptid             = $key->projectteamid;
			$new->_pid              = $key->project_id;

			$newranking[0][$key->team_id] = $new;
		}

		$newranking[0] = self::_sortRanking($newranking[0]);

		$oldpoints = 0;
		$rank      = 0;

		foreach ($newranking[0] as $teamid => $row)
		{
			if ($oldpoints == $row->sum_points)
			{
				$row->rank = $rank;
				$oldpoints = $row->sum_points;
			}
			else
			{
				$rank++;
				$row->rank = $rank;
				$oldpoints = $row->sum_points;
			}
		}

		return $newranking;

	}


	/**
	 * sportsmanagementModelRankingAllTime::_sortRanking()
	 *
	 * @param   mixed  $ranking
	 *
	 * @return
	 */
	function _sortRanking(&$ranking)
	{
		// Reference global application object
		$app       = Factory::getApplication();
		$jinput    = $app->input;
		$order     = $jinput->request->get('order', '', 'STR');
		$order_dir = $jinput->request->get('dir', 'DESC', 'STR');

		$arr2 = array();

		foreach ($ranking as $row)
		{
			$arr2[$row->_teamid] = ArrayHelper::fromObject($row);
		}

		if (!$order)
		{
			$order_dir = 'DESC';
			$sortarray = array();

			foreach ($this->_getRankingCriteria() as $c)
			{
				switch ($c)
				{
					case '_cmpPoints':
						$sortarray['sum_points'] = SORT_DESC;
						break;
					case '_cmpPLAYED':
						$sortarray['cnt_matches'] = SORT_DESC;
						break;
					case '_cmpDiff':
						$sortarray['diff_team_results'] = SORT_DESC;
						break;
					case '_cmpFor':
						$sortarray['sum_team1_result'] = SORT_DESC;
						break;
					case '_cmpPlayedasc':
						$sortarray['cnt_matches'] = SORT_ASC;
						break;
				}
			}

			$arr2 = $this->array_msort($arr2, $sortarray);

			unset($ranking);

			foreach ($arr2 as $key => $row)
			{
				$ranking[$key] = ArrayHelper::toObject($row, 'JSMRankingTeamClass');
			}
		}
		else //     if ( !$order_dir)
		{
			switch ($order)
			{
				case 'played':
					// Uasort($ranking, array(self::$classname, "playedCmp"));
					$sortarray['cnt_matches'] = ($order_dir == 'DESC') ? SORT_DESC : SORT_ASC;
					break;
				case 'name':
					// Uasort($ranking, array(self::$classname, "teamNameCmp"));
					$sortarray['_name'] = ($order_dir == 'DESC') ? SORT_DESC : SORT_ASC;
					break;
				case 'rank':
					break;
				case 'won':
					// Uasort($ranking, array(self::$classname, "wonCmp"));
					$sortarray['cnt_won'] = ($order_dir == 'DESC') ? SORT_DESC : SORT_ASC;
					break;
				case 'draw':
					// Uasort($ranking, array(self::$classname, "drawCmp"));
					$sortarray['cnt_draw'] = ($order_dir == 'DESC') ? SORT_DESC : SORT_ASC;
					break;
				case 'loss':
					// Uasort($ranking, array(self::$classname, "lossCmp"));
					$sortarray['cnt_lost'] = ($order_dir == 'DESC') ? SORT_DESC : SORT_ASC;
					break;
				case 'winpct':
					uasort($ranking, array(self::$classname, "_winpctCmp"));

					// $sortarray['_name'] = ( $order_dir == 'DESC' ) ? SORT_DESC : SORT_ASC;
					break;
				case 'quot':
					uasort($ranking, array(self::$classname, "_quotCmp"));

					// $sortarray['_name'] = ( $order_dir == 'DESC' ) ? SORT_DESC : SORT_ASC;
					break;
				case 'goalsp':
					// Uasort($ranking, array(self::$classname, "goalspCmp"));
					$sortarray['sum_team1_result'] = ($order_dir == 'DESC') ? SORT_DESC : SORT_ASC;
					break;
				case 'goalsfor':
					// Uasort($ranking, array(self::$classname, "goalsforCmp"));
					$sortarray['sum_team1_result'] = ($order_dir == 'DESC') ? SORT_DESC : SORT_ASC;
					break;
				case 'goalsagainst':
					// Uasort($ranking, array(self::$classname, "goalsagainstCmp"));
					$sortarray['sum_team2_result'] = ($order_dir == 'DESC') ? SORT_DESC : SORT_ASC;
					break;
				case 'legsdiff':
					// Uasort($ranking, array(self::$classname, "legsdiffCmp"));
					$sortarray['diff_team_legs'] = ($order_dir == 'DESC') ? SORT_DESC : SORT_ASC;
					break;
				case 'legsratio':
					// Uasort($ranking, array(self::$classname, "legsratioCmp"));
					$sortarray['legsRatio'] = ($order_dir == 'DESC') ? SORT_DESC : SORT_ASC;
					break;
				case 'diff':
					// Uasort($ranking, array(self::$classname, "diffCmp"));
					$sortarray['diff_team_legs'] = ($order_dir == 'DESC') ? SORT_DESC : SORT_ASC;
					break;
				case 'points':
					// Uasort($ranking, array(self::$classname, "_pointsCmp"));
					$sortarray['sum_points'] = ($order_dir == 'DESC') ? SORT_DESC : SORT_ASC;

					// $sortarray['_name'] = ( $order_dir == 'DESC' ) ? SORT_DESC : SORT_ASC;
					break;
				case 'start':
					// Uasort($ranking, array(self::$classname, "startCmp"));
					$sortarray['start_points'] = ($order_dir == 'DESC') ? SORT_DESC : SORT_ASC;
					break;
				case 'bonus':
					// Uasort($ranking, array(self::$classname, "bonusCmp"));
					$sortarray['bonus_points'] = ($order_dir == 'DESC') ? SORT_DESC : SORT_ASC;
					break;
				case 'negpoints':
					// Uasort($ranking, array(self::$classname, "negpointsCmp"));
					$sortarray['neg_points'] = ($order_dir == 'DESC') ? SORT_DESC : SORT_ASC;
					break;
				case 'pointsratio':
					// Uasort($ranking, array(self::$classname, "pointsratioCmp"));
					$sortarray['pointsRatio'] = ($order_dir == 'DESC') ? SORT_DESC : SORT_ASC;
					break;

				default:
					if (method_exists($this, $order . 'Cmp'))
					{
						uasort($ranking, array($this, $order . 'Cmp'));
					}
					break;
			}

			$arr2 = $this->array_msort($arr2, $sortarray);

			unset($ranking);

			foreach ($arr2 as $key => $row)
			{
				$ranking[$key] = ArrayHelper::toObject($row, 'JSMRankingTeamClass');
			}
		}

		return $ranking;

	}

	/**
	 * sportsmanagementModelRankingAllTime::_getRankingCriteria()
	 *
	 * @return
	 */
	function _getRankingCriteria()
	{
		$app = Factory::getApplication();

		if (empty($this->_criteria))
		{
			// Get the values from ranking template setting
			$values = explode(',', $this->_params['ranking_order']);
			$crit   = array();

			foreach ($values as $v)
			{
				$v = ucfirst(str_replace("jl_", "", strtolower(trim($v))));

				if (method_exists($this, '_cmp' . $v))
				{
					$crit[] = '_cmp' . $v;
				}
				else
				{
					Log::add(Text::_('COM_SPORTSMANAGEMENT_RANKING_NOT_VALID_CRITERIA') . ': ' . $v, Log::WARNING, 'jsmerror');
				}
			}

			// Set a default criteria if empty
			if (!count($crit))
			{
				$crit[] = '_cmpPoints';
			}

			$this->_criteria = $crit;
		}

		if ($this->debug_info)
		{
			$this->dump_header("models function _getRankingCriteria");
			$this->dump_variable("this->_criteria", $this->_criteria);
		}

		return $this->_criteria;
	}

	/**
	 * sportsmanagementModelRankingAllTime::array_msort()
	 *
	 * @param   mixed  $array
	 * @param   mixed  $cols
	 *
	 * @return
	 */
	function array_msort($array, $cols)
	{
		$colarr = array();

		foreach ($cols as $col => $order)
		{
			$colarr[$col] = array();

			foreach ($array as $k => $row)
			{
				$colarr[$col]['_' . $k] = strtolower($row[$col]);
			}
		}

		$params = array();

		foreach ($cols as $col => $order)
		{
			$params[] = &$colarr[$col];
			$params   = array_merge($params, (array) $order);
		}

		call_user_func_array('array_multisort', $params);
		$ret   = array();
		$keys  = array();
		$first = true;

		foreach ($colarr as $col => $arr)
		{
			foreach ($arr as $k => $v)
			{
				if ($first)
				{
					$keys[$k] = substr($k, 1);
				}

				$k = $keys[$k];

				if (!isset($ret[$k]))
				{
					$ret[$k] = $array[$k];
				}

				$ret[$k][$col] = $array[$k][$col];
			}

			$first = false;
		}

		return $ret;

	}


}

