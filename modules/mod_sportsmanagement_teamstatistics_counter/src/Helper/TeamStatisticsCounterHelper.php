<?php
/**
 * Native Joomla 5/6 data helper for the SportsManagement Team Statistics Counter module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementTeamStatisticsCounter\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\TeamstatsModel;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class TeamStatisticsCounterHelper
{
    public function getData(Registry $params, DatabaseInterface $fallbackDatabase): array
    {
        $projectId = self::positiveId($params->get('p', 0));
        $teamId = self::positiveId($params->get('teams', 0));

        if ($projectId <= 0 || $teamId <= 0) {
            return self::emptyData();
        }

        $databaseSelector = (int) $params->get('cfg_which_database', 0) === 1 ? 1 : 0;

        try {
            $db = $this->database($databaseSelector, $fallbackDatabase);

        $projectTeamId = $this->getProjectTeamId($db, $projectId, $teamId);
        $team = $this->getTeam($db, $teamId);
        $project = $this->getProject($db, $projectId);

        // The legacy model expects a team assigned to the selected project.
        // Do not run cross-project/statistics queries for missing associations.
        if ($projectTeamId <= 0 || $team === null || $project === null) {
            return self::emptyData();
        }

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
        } catch (\Throwable $e) {
            // Keep missing optional/external databases and statistics failures
            // private. An unavailable data source must not crash a site page.
            Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'com_sportsmanagement');

            return self::emptyData();
        }
    }

    /** Return the same empty shape for missing, invalid or unavailable data. */
    private static function emptyData(): array
    {
        return ['project' => null, 'team' => null, 'stats' => []];
    }

    /** Accept only positive Joomla ID or ID:alias selectors, never casts. */
    private static function positiveId(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        $raw = trim((string) $value);

        if (!preg_match('/^([0-9]+)(?::[\pL\pN_-]+)?$/Du', $raw, $parts)) {
            return 0;
        }

        $digits = ltrim($parts[1], '0');
        $digits = $digits === '' ? '0' : $digits;
        $max = (string) PHP_INT_MAX;

        if ($digits === '0'
            || strlen($digits) > strlen($max)
            || (strlen($digits) === strlen($max) && strcmp($digits, $max) > 0)) {
            return 0;
        }

        return (int) $digits;
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

        $query = $db->createQuery()
            ->select($db->quoteName('pt.id'))
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
            )
            ->where($db->quoteName('pt.project_id') . ' = :projectId')
            ->where($db->quoteName('st.team_id') . ' = :teamId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->bind(':teamId', $teamId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return (int) ($db->loadResult() ?: 0);
    }

    private function getTeam(DatabaseInterface $db, int $teamId): ?object
    {
        if ($teamId <= 0) {
            return null;
        }

        $query = $db->createQuery()
            ->select($db->quoteName('t') . '.*')
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->where($db->quoteName('t.id') . ' = :teamId')
            ->bind(':teamId', $teamId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        $team = $db->loadObject() ?: null;

        if ($team !== null) {
            // Avoid the MySQL-only CONCAT_WS expression in Joomla database queries.
            $team->slug = (int) ($team->id ?? 0) . ':' . (string) ($team->alias ?? '');
        }

        return $team;
    }

    private function getProject(DatabaseInterface $db, int $projectId): ?object
    {
        if ($projectId <= 0) {
            return null;
        }

        $query = $db->createQuery()
            ->select($db->quoteName('p') . '.*')
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->where($db->quoteName('p.id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        $project = $db->loadObject() ?: null;

        if ($project !== null) {
            $project->slug = (int) ($project->id ?? 0) . ':' . (string) ($project->alias ?? '');
        }

        return $project;
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
