<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage leaguechampionoverview
 * @file       leaguechampionoverview.php
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
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementModelleaguechampionoverview
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2020
 * @version $Id$
 * @access public
 */
class sportsmanagementModelleaguechampionoverview extends BaseDatabaseModel
{
static $rankingalltimenotes = array();
	static $rankingalltimewarnings = array();
	static $rankingalltimetips = array();
    
    /**
     * sportsmanagementModelleaguechampionoverview::getProjectWinner()
     * 
     * @param integer $project_id
     * @return void
     */
    function getProjectWinner($project_id = 0)
    {
    $app = Factory::getApplication();  
    $jinput = $app->input;  
        $db        = sportsmanagementHelper::getDBConnection(true, Factory::getApplication()->input->get('cfg_which_database', 0, 'INT'));
		$query     = $db->getQuery(true);
        
        $query->select('pt.id AS _ptid, pt.is_in_score, pt.division_id, pt.finaltablerank as rank');
        $query->select('CONCAT_WS(\':\',pt.id,t.alias) AS ptid_slug');
        $query->select('t.name as _name, t.id as _teamid, t.club_id, c.logo_big');
        $query->from('#__sportsmanagement_project_team AS pt ');
        $query->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt.team_id');
		$query->join('INNER', '#__sportsmanagement_team AS t ON st1.team_id = t.id ');
        $query->join('INNER', '#__sportsmanagement_club AS c ON c.id = t.club_id ');
        $query->where('pt.project_id = ' . $project_id);
		$query->where('pt.is_in_score = 1');
        $query->where('pt.finaltablerank = 1');
        $db->setQuery($query);
		$res = $db->loadObjectList();
        
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        return $res;
    }
    
/**
 * sportsmanagementModelleaguechampionoverview::_getRankingCriteria()
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
 * sportsmanagementModelleaguechampionoverview::_sortRanking()
 * 
 * @param mixed $ranking
 * @param string $order
 * @param string $order_dir
 * @return
 */
function _sortRanking(&$ranking,$order='points',$order_dir='DESC')
	{
		// Reference global application object
		$app       = Factory::getApplication();
		$jinput    = $app->input;

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
			$rank = 1;

			foreach ($arr2 as $key => $row)
			{
				$ranking[$key] = ArrayHelper::toObject($row, 'JSMRankingTeamClass');
				$ranking[$key]->rank = $rank;
				$rank++;
			}
		}

		return $ranking;

	}

/**
 * sportsmanagementModelleaguechampionoverview::array_msort()
 * 
 * @param mixed $array
 * @param mixed $cols
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

?>
