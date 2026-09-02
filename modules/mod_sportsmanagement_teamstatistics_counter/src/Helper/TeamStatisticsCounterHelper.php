<?php
/**
 * Native Joomla 5/6 data helper for the SportsManagement Team Statistics Counter module.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementTeamStatisticsCounter\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\TeamstatsModel;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class TeamStatisticsCounterHelper
{
    public function getData(Registry $params, DatabaseInterface $fallbackDatabase): array
    {
        $projectId = (int) $params->get('p', 0);
        $teamId = (int) $params->get('teams', 0);
        $databaseSelector = (int) $params->get('cfg_which_database', 0) === 1 ? 1 : 0;
        $db = $this->database($databaseSelector, $fallbackDatabase);

        $projectTeamId = $this->getProjectTeamId($db, $projectId, $teamId);
        $team = $this->getTeam($db, $teamId);
        $project = $this->getProject($db, $projectId);

        $this->prepareModelContext($projectId, $teamId, $projectTeamId, $databaseSelector, $team);

        return [
            'project' => $project,
            'team' => $team,
            'stats' => [
                'highest_home' => TeamstatsModel::getHighest('HOME', 'WIN'),
                'highest_away' => TeamstatsModel::getHighest('AWAY', 'WIN'),
                'highestdef_home' => TeamstatsModel::getHighest('HOME', 'DEF'),
                'highestdef_away' => TeamstatsModel::getHighest('AWAY', 'DEF'),
                'highestdraw_home' => TeamstatsModel::getHighest('HOME', 'DRAW'),
                'highestdraw_away' => TeamstatsModel::getHighest('AWAY', 'DRAW'),
                'totalshome' => TeamstatsModel::getSeasonTotals('HOME'),
                'totalsaway' => TeamstatsModel::getSeasonTotals('AWAY'),
                'matchdaytotals' => TeamstatsModel::getMatchDayTotals(),
                'totalrounds' => TeamstatsModel::getTotalRounds(),
                'totalattendance' => TeamstatsModel::getTotalAttendance(),
                'bestattendance' => TeamstatsModel::getBestAttendance(),
                'worstattendance' => TeamstatsModel::getWorstAttendance(),
                'averageattendance' => TeamstatsModel::getAverageAttendance(),
                'chart_url' => TeamstatsModel::getChartURL(),
                'nogoals_against' => TeamstatsModel::getNoGoalsAgainst(),
                'logo' => TeamstatsModel::getLogo(),
                'results' => TeamstatsModel::getResults(),
            ],
        ];
    }

    private function database(int $selector, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve($fallbackDatabase, $selector);
    }

    private function getProjectTeamId(DatabaseInterface $db, int $projectId, int $teamId): int
    {
        if ($projectId <= 0 || $teamId <= 0) {
            return 0;
        }

        $query = $db->getQuery(true)
            ->select($db->quoteName('pt.id'))
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
            )
            ->where($db->quoteName('pt.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('st.team_id') . ' = ' . $teamId);
        $db->setQuery($query, 0, 1);

        return (int) ($db->loadResult() ?: 0);
    }

    private function getTeam(DatabaseInterface $db, int $teamId): ?object
    {
        if ($teamId <= 0) {
            return null;
        }

        $query = $db->getQuery(true)
            ->select('t.*')
            ->select("CONCAT_WS(':', t.id, t.alias) AS slug")
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->where($db->quoteName('t.id') . ' = ' . $teamId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    private function getProject(DatabaseInterface $db, int $projectId): ?object
    {
        if ($projectId <= 0) {
            return null;
        }

        $query = $db->getQuery(true)
            ->select('p.*')
            ->select("CONCAT_WS(':', p.id, p.alias) AS slug")
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->where($db->quoteName('p.id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    private function prepareModelContext(
        int $projectId,
        int $teamId,
        int $projectTeamId,
        int $databaseSelector,
        ?object $team
    ): void {
        TeamstatsModel::$projectid = max(0, $projectId);
        TeamstatsModel::$teamid = max(0, $teamId);
        TeamstatsModel::$projectteamid = max(0, $projectTeamId);
        TeamstatsModel::$cfg_which_database = $databaseSelector;

        TeamstatsModel::$team = $team;
        TeamstatsModel::$highest_home = null;
        TeamstatsModel::$highest_away = null;
        TeamstatsModel::$highestdef_home = null;
        TeamstatsModel::$highestdef_away = null;
        TeamstatsModel::$highestdraw_home = null;
        TeamstatsModel::$highestdraw_away = null;
        TeamstatsModel::$totalshome = null;
        TeamstatsModel::$totalsaway = null;
        TeamstatsModel::$matchdaytotals = null;
        TeamstatsModel::$totalrounds = null;
        TeamstatsModel::$attendanceranking = null;
        TeamstatsModel::$nogoals_against = null;
    }
}
