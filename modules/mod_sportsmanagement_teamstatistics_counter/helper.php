<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       helper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage mod_sportsmanagement_teamstatistics_counter
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

/**
 * modJSMTeamStatisticsCounter
 *
 * @package
 * @author    abcde
 * @copyright 2015
 * @version   $Id$
 * @access    public
 */
class modJSMTeamStatisticsCounter
{

    /**
     * modJSMTeamStatisticsCounter::getData()
     *
     * @param mixed $params
     *
     * @return
     */
    public static function getData($params)
    {
        /**
 *         Get params from module
 */
        $seasonId  = (int) $params->get('s', '0');
        $projectId = (int) $params->get('p', '0');
        $teamId    = (int) $params->get('teams', '0');

        /**
 *         Load project team id
 */
        $db    = sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true);

        $query->select('tt.id');
        $query->from('#__sportsmanagement_project_team tt ');
        $query->where('tt.project_id = ' . $projectId);
        $query->join('INNER', ' #__sportsmanagement_season_team_id as st1 ON st1.id = tt.team_id ');
        $query->where('st1.team_id = ' . $teamId);
        $query->setLimit('1');

        $db->setQuery($query);
        $projectTeamId = $db->loadResult();
        $db->disconnect();

        /**
 *         Set data in model
 */
        sportsmanagementModelTeamStats::$projectid     = $projectId;
        sportsmanagementModelTeamStats::$teamid        = $teamId;
        sportsmanagementModelTeamStats::$projectteamid = $projectTeamId;
        Factory::getApplication()->input->setVar('p', $projectId);

        /**
 *         Get data
 */
        $team    = sportsmanagementModelTeamStats::getTeam();
        $project = sportsmanagementModelProject::getProject();
        $stats   = array(
         'highest_home'      => sportsmanagementModelTeamStats::getHighest('HOME', 'WIN'),
         'highest_away'      => sportsmanagementModelTeamStats::getHighest('AWAY', 'WIN'),
         'highestdef_home'   => sportsmanagementModelTeamStats::getHighest('HOME', 'DEF'),
         'highestdef_away'   => sportsmanagementModelTeamStats::getHighest('AWAY', 'DEF'),
         'highestdraw_home'  => sportsmanagementModelTeamStats::getHighest('HOME', 'DRAW'),
         'highestdraw_away'  => sportsmanagementModelTeamStats::getHighest('AWAY', 'DRAW'),
         'totalshome'        => sportsmanagementModelTeamStats::getSeasonTotals('HOME'),
         'totalsaway'        => sportsmanagementModelTeamStats::getSeasonTotals('AWAY'),
         'matchdaytotals'    => sportsmanagementModelTeamStats::getMatchDayTotals(),
         'totalrounds'       => sportsmanagementModelTeamStats::getTotalRounds(),
         'totalattendance'   => sportsmanagementModelTeamStats::getTotalAttendance(),
         'bestattendance'    => sportsmanagementModelTeamStats::getBestAttendance(),
         'worstattendance'   => sportsmanagementModelTeamStats::getWorstAttendance(),
         'averageattendance' => sportsmanagementModelTeamStats::getAverageAttendance(),
         'chart_url'         => sportsmanagementModelTeamStats::getChartURL(),
         'nogoals_against'   => sportsmanagementModelTeamStats::getNoGoalsAgainst(),
         'logo'              => sportsmanagementModelTeamStats::getLogo(),
         'results'           => sportsmanagementModelTeamStats::getResults(),
        );

        return array(
         'project' => $project,
         'team'    => $team,
         'stats'   => $stats
        );


    }

}
