<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_teamstats_ranking
 * @file       helper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * modSportsmanagementTeamStatHelper
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class modSportsmanagementTeamStatHelper
{

	/**
	 * Method to get the list
	 *
	 * @access public
	 * @return array
	 */
	public static function getData(&$params)
	{
		  $mainframe = Factory::getApplication();
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

			  sportsmanagementModelProject::setProjectId((int) $params->get('p'));
		$stat_id = (int) $params->get('sid');

		if ($stat_id)
		{
			$project = sportsmanagementModelProject::getProject();
			$stat = current(current(sportsmanagementModelProject::getProjectStats($stat_id, 0, 0)));

			if (!$stat)
			{
					   echo Text::_('MOD_SPORTSMANAGEMENT_TEAMSTATS_RANKING_UNDEFINED_STAT') . '<br>';

								$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

				return false;
			}

			$ranking = $stat->getTeamsRanking($project->id, $params->get('limit'), 0, $params->get('ranking_order', 'DESC'));

			if (empty($ranking))
			{
					   return false;
			}

			$ids = array();

			foreach ($ranking as $r)
			{
					   $ids[] = $db->Quote($r->team_id);
			}

			$query->select('t.*, c.logo_big,c.country');
			$query->select('CASE WHEN CHAR_LENGTH( t.alias ) THEN CONCAT_WS( \':\', t.id, t.alias ) ELSE t.id END AS team_slug');
			$query->select('CASE WHEN CHAR_LENGTH( c.alias ) THEN CONCAT_WS( \':\', c.id, c.alias ) ELSE c.id END AS club_slug');
			$query->from('#__sportsmanagement_team as t ');
			$query->join('LEFT', ' #__sportsmanagement_club AS c ON c.id = t.club_id ');
			$query->where('t.id IN (' . implode(',', $ids) . ')');

			$db->setQuery($query);

			$teams = $db->loadObjectList('id');
			$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			return array('project' => $project, 'ranking' => $ranking, 'teams' => $teams, 'stat' => $stat);
		}
		else
		{
			return false;
		}
	}

		  /**
		   * get img for team
		   *
		   * @param  object ranking row
		   * @param  int type = 1 for club logo, 2 for country
		   * @return html string
		   */
	public static function getLogo($item, $type = 1)
	{
		if ($type == 1) // Club logo
		{
			if (!empty($item->logo_big))
			{
				return HTMLHelper::_('image', $item->logo_big, $item->short_name, array('width' => '50', 'class' => 'teamlogo'));
			}
		}
		elseif ($type == 2 && !empty($item->country))
		{
			return JSMCountries::getCountryFlag($item->country, 'class="teamcountry"');
		}

			  return '';
	}

	/**
	 * modSportsmanagementTeamStatHelper::getTeamLink()
	 *
	 * @param   mixed $item
	 * @param   mixed $params
	 * @param   mixed $project
	 * @return
	 */
	public static function getTeamLink($item, $params, $project)
	{
		$routeparameter = array();
		$routeparameter['cfg_which_database'] = $params->get('cfg_which_database');
		$routeparameter['s'] = $project->season_slug;
		$routeparameter['p'] = $project->slug;

		switch ($params->get('teamlink'))
		{
			case 'teaminfo':
				$routeparameter['tid'] = $item->team_slug;
				$routeparameter['ptid'] = 0;

return sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo', $routeparameter);
			case 'roster':
				$routeparameter['tid'] = $item->team_slug;
				$routeparameter['ptid'] = 0;
				$routeparameter['division'] = 0;

return sportsmanagementHelperRoute::getSportsmanagementRoute('roster', $routeparameter);
			case 'teamplan':
				$routeparameter['tid'] = $item->team_slug;
				$routeparameter['division'] = 0;
				$routeparameter['mode'] = 0;
				$routeparameter['ptid'] = 0;

return sportsmanagementHelperRoute::getSportsmanagementRoute('teamplan', $routeparameter);
			case 'clubinfo':
			return sportsmanagementHelperRoute::getClubInfoRoute($project->slug, $item->club_slug);
		}
	}

	/**
	 * modSportsmanagementTeamStatHelper::getStatIcon()
	 *
	 * @param   mixed $stat
	 * @return
	 */
	public static function getStatIcon($stat)
	{
		if ($stat->icon == 'media/com_sportsmanagement/event_icons/event.gif')
		{
			$txt = $stat->name;
		}
		else
		{
			$imgTitle = Text::_($stat->name);
			$imgTitle2 = array(' title' => $imgTitle, ' alt' => $imgTitle);
			$txt = HTMLHelper::image($stat->icon, $imgTitle, $imgTitle2);
		}

		return $txt;
	}
}
