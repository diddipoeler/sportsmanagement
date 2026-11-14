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
    /**
     * Load the configured team and its statistics for a frontend module.
     *
     * Validates project/team IDs and the database selector before querying.
     * Unrelated project/team records never reach the static statistics model.
     * Database failures are kept private and return a stable empty payload
     * instead of leaking SQL details into Joomla logs or site output.
     *
     * @param Registry $params Current module configuration.
     * @param DatabaseInterface $fallbackDatabase Joomla database fallback.
     * @return array{project:object|null,team:object|null,stats:array} Sanitised data.
     */
    public function getData(Registry $params, DatabaseInterface $fallbackDatabase): array
    {
        $projectId = self::positiveId($params->get('p', 0));
        $teamId = self::positiveId($params->get('teams', 0));

        if ($projectId <= 0 || $teamId <= 0) {
            return self::emptyData();
        }

        $databaseSelector = self::databaseSelector($params->get('cfg_which_database', 0));

        if ($databaseSelector === null) {
            return self::emptyData();
        }

        try {
            $db = $this->database($databaseSelector, $fallbackDatabase);

            $projectTeamId = $this->getProjectTeamId($db, $projectId, $teamId);

            // Never load unrelated project/team information when this pair has no
            // season-team association; this also avoids unnecessary SQL queries.
            if ($projectTeamId <= 0) {
                return self::emptyData();
            }

            $team = $this->getTeam($db, $teamId);
            $project = $this->getProject($db, $projectId);

            if ($team === null || $project === null) {
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
            Log::add(__METHOD__ . ': statistics query failed (' . get_class($e) . ').', Log::ERROR, 'com_sportsmanagement');

            return self::emptyData();
        }
    }

    /**
     * Return the safe template payload when statistics are unavailable.
     *
     * Null project/team records and an empty statistics map ensure that
     * another module instance's cached results are never rendered by mistake.
     *
     * @return array{project:null,team:null,stats:array{}} Empty module data.
     */
    private static function emptyData(): array
    {
        return ['project' => null, 'team' => null, 'stats' => []];
    }

    /**
     * Parse positive Joomla IDs and optional ID:alias route selectors.
     *
     * Rejects non-scalar, negative, fractional and overflowing identifiers.
     * Supports Unicode alias characters used by legacy team URLs, without
     * trusting PHP's permissive integer conversions.
     *
     * @param mixed $value Module parameter or route slug.
     * @return int Positive ID, or zero for malformed input.
     */
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

    /**
     * Validate the module's database selector before any database query.
     *
     * Accepts only zero/one and zero-padded decimal strings from historical
     * Joomla radio fields. Invalid choices must never fall back silently
     * to an unrelated database connection.
     *
     * @param mixed $value Raw module configuration value.
     * @return int|null Allowed database choice, or null if invalid.
     */
    private static function databaseSelector(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value === 0 || $value === 1 ? $value : null;
        }

        if (!is_string($value) || strlen($value) > 64
            || preg_match('/^0*([01])$/D', $value, $match) !== 1) {
            return null;
        }

        return (int) $match[1];
    }

    /**
     * Resolve the configured component database for module queries.
     *
     * Delegates to the namespaced Joomla 5/6 resolver, preserving the
     * component's external database entitlement and fallback checks.
     * @param int $selector Database selector.
     * @param DatabaseInterface $fallbackDatabase Default database.
     * @return DatabaseInterface Selected connection.
     */
    private function database(int $selector, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve($fallbackDatabase, $selector);
    }

    /**
     * Resolve project-team membership for a project and team.
     *
     * Joins project-team assignments to season-team IDs with bound SQL
     * parameters. Malformed database result IDs must not be cast into an
     * unrelated project-team association.
     *
     * @param DatabaseInterface $db Component database.
     * @param int $projectId Project identifier.
     * @param int $teamId Team identifier.
     * @return int Project-team ID or zero.
     */
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

        $result = $db->loadResult();

        if (!is_int($result) && (!is_string($result)
            || preg_match('/^[0-9]+$/D', $result) !== 1)) {
            return 0;
        }

        return self::positiveId($result);
    }

    /**
     * Load one team and build its portable Joomla ID:alias slug.
     *
     * Uses a parameterised query and assembles the slug in PHP rather than
     * relying on database-specific string concatenation.
     *
     * @param DatabaseInterface $db Component database.
     * @param int $teamId Team identifier.
     * @return object|null Team or null.
     */
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

    /**
     * Load one project and construct its portable Joomla ID:alias slug.
     *
     * Queries the selected record with a bound integer and returns null
     * for missing projects without relying on MySQL-only functions.
     *
     * @param DatabaseInterface $db Component database.
     * @param int $projectId Project identifier.
     * @return object|null Project or null.
     */
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

    /**
     * Prepare legacy TeamstatsModel static context and reset cached statistics.
     *
     * Stores the current project and team, then clears cached aggregates
     * so multiple module instances cannot display another team's results.
     *
     * @param int $projectId Project identifier.
     * @param int $teamId Team identifier.
     * @param int $projectTeamId Project-team identifier.
     * @param int $databaseSelector Database mode.
     * @param object|null $team Optional team record.
     * @return void
     */
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
