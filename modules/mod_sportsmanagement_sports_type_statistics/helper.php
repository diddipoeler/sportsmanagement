<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_sports_type_statistics
 * @file       mod_sportsmanagement_sports_type_statistics.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * modJSMSportsHelper
 *
 * @package
 * @author    abcde
 * @copyright 2015
 * @version   $Id$
 * @access    public
 */
class modJSMSportsHelper
{

	/**
	 * modJSMSportsHelper::getData()
	 *
	 * @param   mixed  $params
	 *
	 * @return array
	 */
	public static function getData(&$params)
	{
		if (!class_exists('sportsmanagementModelSportsTypes'))
		{
			JLoader::import('components.com_sportsmanagement.models.sportstypes', JPATH_ADMINISTRATOR);
		}

		$model = BaseDatabaseModel::getInstance('SportsTypes', 'sportsmanagementModel');

		return array('sportstype'                => $model->getSportsTypes(),
		             'projectscount'             => $model->getProjectsCount($params->get('sportstypes')),
		             'playgroundscount'          => $model->getPlaygroundsOnlyCount($params->get('sportstypes')),
		             'leaguescount'              => $model->getLeaguesCount($params->get('sportstypes')),
		             'seasonscount'              => $model->getSeasonsCount($params->get('sportstypes')),
		             'clubscount'                => $model->getClubsOnlyCount($params->get('sportstypes')),
		             'personscount'              => $model->getPersonsOnlyCount($params->get('sportstypes')),
		             'projectteamscount'         => $model->getProjectTeamsCount($params->get('sportstypes')),
		             'projectteamsplayerscount'  => $model->getProjectTeamsPlayersCount($params->get('sportstypes')),
		             'projectdivisionscount'     => $model->getProjectDivisionsCount($params->get('sportstypes')),
		             'projectroundscount'        => $model->getProjectRoundsCount($params->get('sportstypes')),
		             'projectmatchescount'       => $model->getProjectMatchesCount($params->get('sportstypes')),
		             'projectmatcheseventscount' => $model->getProjectMatchesEventsCount($params->get('sportstypes')),
		             'projectmatchesstatscount'  => $model->getProjectMatchesStatsCount($params->get('sportstypes')),
		);
	}
}
