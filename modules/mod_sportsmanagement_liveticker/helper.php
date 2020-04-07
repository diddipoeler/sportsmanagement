<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       helper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage mod_sportsmanagement_liveticker
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

/**
 * modTurtushoutHelper
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class modTurtushoutHelper
{

	/**
	 * modTurtushoutHelper::getListCommentary()
	 *
	 * @param   mixed $list
	 * @return
	 */
	public static function getListCommentary($list)
	{
		$db    = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
		$mainframe = Factory::getApplication();
		$matches = array();

		foreach ($list as $row)
		{
			// $matches[] = $row->match_id;

				  // $selmatchcomm = implode(',',$matches);
			$query->clear();
			$query->select('*');
			$query->from('#__sportsmanagement_match_commentary');
			$query->where('match_id = ' . $row->match_id);
			$query->order('event_time DESC');

			$db->setQuery($query);
			$rows = $db->loadObjectList();

			if ($rows)
			{
				$matches[$row->match_id] = $rows;
			}
		}

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $matches;
	}

		  /**
		   * modTurtushoutHelper::getList()
		   *
		   * @param   mixed $params
		   * @param   mixed $limit
		   * @return
		   */
	public static function getList(&$params, $limit)
	{

		$date = new DateTime;
		$config = Factory::getConfig();
		$date->setTimezone(new DateTimeZone($config->get('offset')));

		// $timestamp = strtotime($this->match->match_date);

			  // Aktuelles datum
		$akt_datum = date("Y-m-d", time());

		// $timestamp = strtotime($akt_datum);
		// echo 'timestamp '.$timestamp.'<br>';

			  // $date->format('Y-m-d H:i:s');
		$timestamp = strtotime($date->format('Y-m-d H:i:s'));
		$timestampvon = $timestamp - ( $params->get('playtime') * 60 );
		$timestampbis = $timestamp + ( $params->get('playtime') * 60 );

		//        echo 'timestamp '.$timestamp.'<br>';
		//        echo 'timestampvon '.$timestampvon.'<br>';
		//        echo 'timestampbis '.$timestampbis.'<br>';
		// $von = $akt_datum.' 00:00:00';
		// $bis = $akt_datum.' 23:59:59';
		$rows = array();

			  $db        = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
		$mainframe = Factory::getApplication();

		$query->clear();
		$query->select('jl.id,jl.name,jl.game_regular_time,jl.halftime,jl.fav_team');
		$query->select('jco.alpha2');
		$query->select('jco.picture as country_picture');
		$query->select('jco.alpha3 as countries_iso_code_3');
		$query->select('jm.id as match_id,jm.match_date,jm.projectteam1_id,jm.projectteam2_id,jm.team1_result,jm.team2_result');
		$query->select('jt1.name as heim,jt1.short_name as heim_short_name,jt1.middle_name as heim_middle_name');
		$query->select('jt2.name as gast,jt2.short_name as gast_short_name,jt2.middle_name as gast_middle_name');
		$query->select('jc1.logo_big as wappenheim');
		$query->select('jc2.logo_big as wappengast');
		$query->from('#__sportsmanagement_project as jl');
		$query->join('INNER', '#__sportsmanagement_round as jr ON jr.project_id = jl.id');
		$query->join('INNER', '#__sportsmanagement_match as jm ON jm.round_id = jr.id');
		$query->join('INNER', '#__sportsmanagement_project_team as jpt1 ON jpt1.id = jm.projectteam1_id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.id = jpt1.team_id');
		$query->join('INNER', '#__sportsmanagement_team as jt1 ON jt1.id = st1.team_id');
		$query->join('INNER', '#__sportsmanagement_club as jc1 ON jc1.id = jt1.club_id');
		$query->join('INNER', '#__sportsmanagement_project_team as jpt2 ON jpt2.id = jm.projectteam2_id ');
		$query->join('INNER', '#__sportsmanagement_season_team_id AS st2 ON st2.id = jpt2.team_id');
		$query->join('INNER', '#__sportsmanagement_team as jt2 ON jt2.id = st2.team_id');
		$query->join('INNER', '#__sportsmanagement_club as jc2 ON jc2.id = jt2.club_id');
		$query->join('INNER', '#__sportsmanagement_league as jle ON jle.id = jl.league_id');
		$query->join('LEFT', '#__sportsmanagement_countries as jco ON jco.alpha3 = jle.country');

		// $query->where('jm.round_id IN ('.$round_ids.')');

			  $query->where('( jm.match_timestamp >= ' . $timestampvon . ' AND jm.match_timestamp <= ' . $timestampbis . ' )');

			  $db->setQuery($query, 0, $limit);
		$rows = $db->loadObjectList();

		if ($db->getErrorMsg())
		{
			//			 modTurtushoutHelper::install();
		}

			  $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			  return $rows;
	}

	/**
	 * modTurtushoutHelper::shout()
	 *
	 * @param   mixed $display_username
	 * @param   mixed $display_title
	 * @param   mixed $add_timeout
	 * @return
	 */
	function shout($display_username, $display_title, $add_timeout)
	{

	}


	/**
	 * modTurtushoutHelper::delete()
	 *
	 * @return
	 */
	function delete()
	{

	}

	/**
	 * modTurtushoutHelper::install()
	 *
	 * @return
	 */
	function install()
	{

	}
}
