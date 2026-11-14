<?php
/**
 * Joomla 5/6 iCalendar site model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

final class IcalModel extends SportsManagementProjectModel
{
    public static int $projectid = 0;
    public static int $divisionid = 0;
    public static int $cfg_which_database = 0;
    public static int $teamid = 0;
    public static int $projectteamid = 0;

    public ?object $team = null;
    public ?object $club = null;

    /**
     * Retrieve Joomla's primary database connection from its container.
     * The calendar feed uses this service when a caller explicitly requests
     * a database other than the one already bound to the current model.
     *
     * @return DatabaseInterface Active Joomla database service.
     * @throws \RuntimeException When Joomla's service is unavailable.
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement iCalendar database service is unavailable.', 500);
        }

        return $database;
    }

    /**
     * Initialise iCalendar compatibility selectors for the current request.
     * Copies project, division, team and database settings from the
     * native model and Joomla input to legacy static fields expected
     * by older calendar entry points.
     * @param array $config MVC model configuration.
     * @param MVCFactoryInterface|null $factory Optional Joomla MVC factory.
     * @return void
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        self::$projectid = $this->projectId;
        self::$divisionid = $this->divisionId;
        self::$cfg_which_database = $input->getInt('cfg_which_database', 0) === 1 ? 1 : 0;
        self::$teamid = $input->getInt('tid', 0);
        self::$projectteamid = $input->getInt('ptid', 0);

        if (class_exists('sportsmanagementModelProject')) {
            \sportsmanagementModelProject::$projectid = self::$projectid;
        }
    }

    /**
     * Parse a bounded nonnegative calendar selector without partial casts.
     *
     * Accepts decimal identifiers and optional Joomla ID:alias slugs,
     * including historical leading-zero IDs. Rejects nested input, floats,
     * invalid aliases, oversized values and integers beyond PHP_INT_MAX.
     *
     * @param mixed $value ID supplied by the caller or Joomla input.
     * @param bool $allowAlias Whether ID:alias syntax is allowed.
     * @return int|null Nonnegative ID, or null for malformed input.
     */
    private static function nonNegativeSelector(mixed $value, bool $allowAlias = false): ?int
    {
        if (is_int($value)) {
            return $value >= 0 ? $value : null;
        }

        if (!is_string($value) || strlen($value) > 128) {
            return null;
        }

        $pattern = $allowAlias
            ? '/^([0-9]+)(?::[\pL\pN_-]+)?$/Du'
            : '/^([0-9]+)$/D';

        if (!preg_match($pattern, $value, $matches)) {
            return null;
        }

        $digits = ltrim($matches[1], '0');

        if ($digits === '') {
            return 0;
        }

        $max = (string) PHP_INT_MAX;

        if (strlen($digits) > strlen($max)
            || (strlen($digits) === strlen($max) && strcmp($digits, $max) > 0)) {
            return null;
        }

        return (int) $digits;
    }

    /**
     * Load project-owned published fixtures for the iCalendar exporter.
     *
     * Validates each selected project, team, division, venue and database
     * identifier before creating a query. Constrains joined project teams to
     * the requested project and season, and binds all optional filters.
     * Creates Joomla route slugs and display times in PHP instead of using
     * MySQL-specific SQL formatting functions. Database exceptions are
     * reduced to their class name in logs and generic frontend notices.
     *
     * @param mixed $projectid Positive project ID or Joomla ID:alias slug.
     * @param mixed $teamid Optional team ID or slug (zero means all teams).
     * @param mixed $divisionid Optional division ID or slug (zero means all divisions).
     * @param mixed $playgroundid Optional playground ID or slug (zero means all venues).
     * @param mixed $ordering ASC or DESC match order; other inputs default to ASC.
     * @param mixed $cfg_which_database Database selector, only 0 or 1.
     * @return array|false Published matches keyed by ID, [] for invalid selectors, or false on database failure.
     */
    public function getResultsPlan($projectid = 0, $teamid = 0, $divisionid = 0, $playgroundid = 0, $ordering = 'ASC', $cfg_which_database = 0)
    {
        $projectId = self::nonNegativeSelector($projectid, true);
        $teamId = self::nonNegativeSelector($teamid, true);
        $divisionId = self::nonNegativeSelector($divisionid, true);
        $playgroundId = self::nonNegativeSelector($playgroundid, true);
        $databaseSelector = self::nonNegativeSelector($cfg_which_database);
        $direction = is_string($ordering) && strtoupper($ordering) === 'DESC' ? 'DESC' : 'ASC';

        // Malformed selectors must not silently expose a different project.
        if ($projectId === null || $projectId <= 0
            || $teamId === null || $divisionId === null
            || $playgroundId === null
            || $databaseSelector === null || $databaseSelector > 1) {
            return [];
        }

        $app = $this->siteApplication();

        try {
            if ($databaseSelector === self::$cfg_which_database) {
                $db = $this->getDatabase();
            } else {
                $db = SportsManagementDatabaseResolver::resolve(
                    self::joomlaDatabase(),
                    $databaseSelector
                );
            }

            $query = $db->createQuery()
                ->select([
                    $db->quoteName('m.id'),
                    $db->quoteName('m.projectteam1_id'),
                    $db->quoteName('m.projectteam2_id'),
                    $db->quoteName('m.match_date'),
                    $db->quoteName('m.time_present'),
                    $db->quoteName('playground.id', 'playground_id'),
                    $db->quoteName('playground.name', 'playground_name'),
                    $db->quoteName('playground.short_name', 'playground_short_name'),
                    $db->quoteName('playground.address', 'playground_address'),
                    $db->quoteName('playground.zipcode', 'playground_zipcode'),
                    $db->quoteName('playground.city', 'playground_city'),
                    $db->quoteName('pt1.project_id'),
                    $db->quoteName('d1.name', 'divhome'),
                    $db->quoteName('d2.name', 'divaway'),
                    $db->quoteName('p.alias', 'project_alias'),
                    $db->quoteName('r.id', 'round_id'),
                    $db->quoteName('r.alias', 'round_alias'),
                    $db->quoteName('playground.alias', 'playground_alias'),
                    $db->quoteName('t1.alias', 'home_team_alias'),
                    $db->quoteName('t2.alias', 'away_team_alias'),
                    $db->quoteName('t1.id', 'team1'),
                    $db->quoteName('t2.id', 'team2'),
                    $db->quoteName('p.name', 'project_name'),
                ])
                ->from($db->quoteName('#__sportsmanagement_match', 'm'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('m.round_id') . ' = ' . $db->quoteName('r.id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('m.projectteam1_id') . ' = ' . $db->quoteName('pt1.id') . ' AND ' . $db->quoteName('pt1.project_id') . ' = ' . $db->quoteName('p.id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('m.projectteam2_id') . ' = ' . $db->quoteName('pt2.id') . ' AND ' . $db->quoteName('pt2.project_id') . ' = ' . $db->quoteName('p.id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id') . ' AND ' . $db->quoteName('st1.season_id') . ' = ' . $db->quoteName('p.season_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id') . ' AND ' . $db->quoteName('st2.season_id') . ' = ' . $db->quoteName('p.season_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_division', 'd1') . ' ON ' . $db->quoteName('m.division_id') . ' = ' . $db->quoteName('d1.id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_division', 'd2') . ' ON ' . $db->quoteName('m.division_id') . ' = ' . $db->quoteName('d2.id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_playground', 'playground') . ' ON ' . $db->quoteName('playground.id') . ' = ' . $db->quoteName('m.playground_id'))
                ->where($db->quoteName('m.published') . ' = 1')
                ->where($db->quoteName('r.project_id') . ' = :projectId')
                ->order($db->quoteName('m.match_date') . ' ' . $direction)
                ->order($db->quoteName('m.match_number') . ' ' . $direction)
                ->bind(':projectId', $projectId, ParameterType::INTEGER);

            if ($teamId > 0) {
                $query
                    ->where('(' . $db->quoteName('t1.id') . ' = :homeTeamId OR ' . $db->quoteName('t2.id') . ' = :awayTeamId)')
                    ->bind([':homeTeamId', ':awayTeamId'], $teamId, ParameterType::INTEGER);
            }

            if ($divisionId > 0) {
                $query->where($db->quoteName('m.division_id') . ' = :calendarDivisionId')
                    ->bind(':calendarDivisionId', $divisionId, ParameterType::INTEGER);
            }

            if ($playgroundId > 0) {
                $query->where($db->quoteName('m.playground_id') . ' = :calendarPlaygroundId')
                    ->bind(':calendarPlaygroundId', $playgroundId, ParameterType::INTEGER);
            }

            $db->setQuery($query);
            $matches = $db->loadObjectList('id') ?: [];

            foreach ($matches as $match) {
                if (!is_object($match)) {
                    continue;
                }

                $matchId = (int) ($match->id ?? 0);
                $homeAlias = (string) ($match->home_team_alias ?? '');
                $awayAlias = (string) ($match->away_team_alias ?? '');
                $match->slug = (string) $matchId . ($homeAlias !== '' && $awayAlias !== ''
                    ? ':' . $homeAlias . '_' . $awayAlias
                    : '');
                $match->project_slug = (int) ($match->project_id ?? $projectId)
                    . ':' . (string) ($match->project_alias ?? '');
                $match->round_slug = (int) ($match->round_id ?? 0)
                    . ':' . (string) ($match->round_alias ?? '');
                $match->playground_slug = (int) ($match->playground_id ?? 0) > 0
                    ? (int) $match->playground_id . ':' . (string) ($match->playground_alias ?? '')
                    : '';

                $rawTime = (string) ($match->time_present ?? '');
                $match->time_present = preg_match('/(?:^|\\s)(\\d{2}:\\d{2})(?::\\d{2})?$/D', $rawTime, $time)
                    ? $time[1]
                    : $rawTime;
            }

            return $matches;
        } catch (\Throwable $exception) {
            // Do not record driver exception messages: they may contain
            // passwords, private table names or raw SQL query values.
            Log::add(__METHOD__ . ': iCalendar query failed (' . get_class($exception) . ').', Log::ERROR, 'jsmerror');
            $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

            return false;
        }
    }
}
