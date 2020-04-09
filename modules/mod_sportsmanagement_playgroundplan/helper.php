<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_playgroundplan
 * @file       helper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

/**
 * modSportsmanagementPlaygroundplanHelper
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class modSportsmanagementPlaygroundplanHelper
{

	/**
	 * Method to get the list
	 *
	 * @access public
	 * @return array
	 */
	public static function getData(&$params)
	{
		$app              = Factory::getApplication();
		$usedp            = $params->get('projects', '0');
		$usedpid          = $params->get('playground', '0');
		$projectstring    = (is_array($usedp)) ? implode(",", array_map('intval', $usedp)) : (int) $usedp;
		$playgroundstring = (is_array($usedpid)) ? implode(",", array_map('intval', $usedpid)) : (int) $usedpid;

		$numberofmatches = $params->get('maxmatches', '5');

		$db    = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		$result = array();

		$query->select('m.match_date, DATE_FORMAT(m.time_present, "%H:%i") time_present');
		$query->select('p.name AS project_name, p.id AS project_id, st1.team_id as team1, st2.team_id as team2, lg.name AS league_name');
		$query->select('plcd.id AS club_playground_id');
		$query->select('plcd.name AS club_playground_name');
		$query->select('pltd.id AS team_playground_id');
		$query->select('pltd.name AS team_playground_name');
		$query->select('pl.id AS playground_id');

		$query->select('pl.picture AS playground_picture');
		$query->select('plcd.picture AS playground_club_picture');
		$query->select('pltd.picture AS playground_team_picture');

		$query->select('pl.name AS playground_name');
		$query->select('t1.name AS team1_name');
		$query->select('t2.name AS team2_name');

		$query->select('CONCAT_WS( \':\', p.id, p.alias ) AS project_slug');
		$query->select('CONCAT_WS( \':\', pl.id, pl.alias ) AS playground_slug');
		$query->select('CONCAT_WS( \':\', plcd.id, plcd.alias ) AS playground_club_slug');
		$query->select('CONCAT_WS( \':\', pltd.id, pltd.alias ) AS playground_team_slug');

		$query->from('#__sportsmanagement_match AS m ');
		$query->join('INNER', ' #__sportsmanagement_project_team as tj1 ON tj1.id = m.projectteam1_id  ');
		$query->join('INNER', ' #__sportsmanagement_project_team as tj2 ON tj2.id = m.projectteam2_id  ');

		$query->join('INNER', ' #__sportsmanagement_project AS p ON p.id = tj1.project_id  ');

		$query->join('INNER', ' #__sportsmanagement_season_team_id as st1 ON st1.id = tj1.team_id ');
		$query->join('INNER', ' #__sportsmanagement_season_team_id as st2 ON st2.id = tj2.team_id ');

		$query->join('INNER', ' #__sportsmanagement_team as t1 ON t1.id = st1.team_id ');
		$query->join('INNER', ' #__sportsmanagement_team as t2 ON t2.id = st2.team_id ');
		$query->join('INNER', ' #__sportsmanagement_club c ON c.id = t1.club_id');
		$query->join('INNER', ' #__sportsmanagement_league as lg ON lg.id = p.league_id');

		$query->join('LEFT', ' #__sportsmanagement_playground AS plcd ON c.standard_playground = plcd.id');
		$query->join('LEFT', ' #__sportsmanagement_playground AS pltd ON tj1.standard_playground = pltd.id ');
		$query->join('LEFT', ' #__sportsmanagement_playground AS pl ON m.playground_id = pl.id');

		if ($playgroundstring)
		{
			$query->where('( m.playground_id IN (' . $playgroundstring . ')
        OR (tj1.standard_playground IN (' . $playgroundstring . ') AND m.playground_id IS NULL)
                          OR (c.standard_playground IN (' . $playgroundstring . ') AND (m.playground_id IS NULL AND tj1.standard_playground IS NULL )))
        '
			);
		}

		$query->where('m.match_date > NOW()');
		$query->where('m.published = 1');
		$query->where('p.published = 1');

		if ($projectstring != 0)
		{
			$query->where('p.id IN (' . $projectstring . ')');
		}

		$query->order('m.match_date ASC');

		$db->setQuery($query, 0, $numberofmatches);

		try
		{
			$info = $db->loadObjectList();
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		}
		catch (Exception $e)
		{
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
			$msg  = $e->getMessage(); // Returns "Normally you would have other code...
			$code = $e->getCode(); // Returns '500';
			$info = false;
		}

		return $info;
	}

	/**
	 * modSportsmanagementPlaygroundplanHelper::getTeams()
	 *
	 * @param   mixed  $team1_id
	 * @param   mixed  $teamformat
	 *
	 * @return
	 */
	public static function getTeams($team1_id, $teamformat)
	{
		$app   = Factory::getApplication();
		$db    = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		$query->select($teamformat);
		$query->from('#__sportsmanagement_team AS t ');
		$query->where('t.id =' . (int) $team1_id);

		$db->setQuery($query);

		try
		{
			$team_name = $db->loadResult();
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		}
		catch (Exception $e)
		{
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
			$msg       = $e->getMessage(); // Returns "Normally you would have other code...
			$code      = $e->getCode(); // Returns '500';
			$team_name = false;
		}

		return $team_name;
	}

	/**
	 * modSportsmanagementPlaygroundplanHelper::getTeamLogo()
	 *
	 * @param   mixed  $team_id
	 *
	 * @return
	 */
	public static function getTeamLogo($team_id, $logo = 'logo_big')
	{
		$app   = Factory::getApplication();
		$db    = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		$query->select('c.' . $logo);
		$query->from('#__sportsmanagement_team AS t ');
		$query->join('LEFT', ' #__sportsmanagement_club as c ON c.id = t.club_id ');
		$query->where('t.id =' . $team_id);

		$db->setQuery($query);

		try
		{
			$club_logo = $db->loadResult();

			if ($club_logo == '')
			{
				switch ($logo)
				{
					case 'logo_small':
						$club_logo = sportsmanagementHelper::getDefaultPlaceholder('clublogosmall');
						break;
					case 'logo_middle':
						$club_logo = sportsmanagementHelper::getDefaultPlaceholder('clublogomiddle');
						break;
					case 'logo_big':
						$club_logo = sportsmanagementHelper::getDefaultPlaceholder('clublogobig');
						break;
				}
			}

			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		}
		catch (Exception $e)
		{
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
			$msg       = $e->getMessage(); // Returns "Normally you would have other code...
			$code      = $e->getCode(); // Returns '500';
			$club_logo = false;
		}

		return $club_logo;
	}
}
