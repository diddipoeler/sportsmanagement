<?php
/**
 * Joomla 5/6 compatibility facade for project data used by results overrides.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

/**
 * Exposes the small historical sportsmanagementModelProject surface still used
 * by results templates while serving data prepared by the native Results view.
 */
final class ResultsLegacyProjectCompatibility
{
    public static int $projectid = 0;
    public static int $seasonid = 0;
    public static int $cfg_which_database = 0;
    public static array $favteams = [];
    public static string $projectslug = '';
    public static string $roundslug = '';
    public static string $divisionslug = '';

    /** @var array<int, array<int, object>> */
    private static array $eventsByMatch = [];

    /** @var array<int, array<int, object>> */
    private static array $substitutionsByMatch = [];

    /**
     * Register the historical project model alias for legacy results layouts.
     *
     * Uses a non-autoloading class lookup so an already-registered legacy
     * model is preserved and Joomla can continue resolving results overrides
     * that depend on the old class name.
     *
     * @return void
     */
    public static function register(): void
    {
        if (!class_exists('sportsmanagementModelProject', false)) {
            class_alias(self::class, 'sportsmanagementModelProject');
        }
    }

    /**
     * Synchronise the legacy project facade with the native results view.
     *
     * Copies project identity, routing slugs, favourite teams, match events
     * and substitutions so existing template overrides can read the same
     * data as the native Joomla 5/6 results view without database requests.
     *
     * @param object $project Project row containing ID, season and slugs.
     * @param int $roundId Selected match round identifier.
     * @param int $databaseSelector Selected SportsManagement database.
     * @param array<int, array<int, object>> $eventsByMatch Events indexed by match ID.
     * @param array<int, array<int, object>> $substitutionsByMatch Substitutions indexed by match ID.
     * @param array<int, int> $favteams Favourite team identifiers.
     * @return void
     */
    public static function sync(
        object $project,
        int $roundId,
        int $databaseSelector,
        array $eventsByMatch,
        array $substitutionsByMatch,
        array $favteams
    ): void {
        self::$projectid = (int) ($project->id ?? 0);
        self::$seasonid = (int) ($project->season_id ?? 0);
        self::$cfg_which_database = $databaseSelector === 1 ? 1 : 0;
        self::$favteams = array_values(array_map('intval', $favteams));
        self::$projectslug = (string) ($project->slug ?? self::$projectid);
        self::$roundslug = (string) ($project->round_slug ?? $roundId);
        self::$divisionslug = (string) ($project->division_slug ?? '');
        self::$eventsByMatch = $eventsByMatch;
        self::$substitutionsByMatch = $substitutionsByMatch;
    }

    /**
     * Return prepared match events for a legacy results template.
     *
     * Reads the in-memory events supplied by sync(), preserving historical
     * arguments for template compatibility. Reverses event order when the
     * legacy descending-order flag is set, without executing a query.
     *
     * @param mixed $matchId Match identifier requested by the template.
     * @param mixed $showName Legacy display flag, retained for compatibility.
     * @param mixed $sortDesc Reverse event order when equal to one.
     * @param mixed $databaseSelector Legacy database selector argument.
     * @return array<int, object> Events for the match, or an empty list.
     */
    public static function getMatchEvents($matchId = 0, $showName = 0, $sortDesc = 0, $databaseSelector = 0): array
    {
        $events = self::$eventsByMatch[(int) $matchId] ?? [];

        if ((int) $sortDesc === 1) {
            $events = array_reverse($events);
        }

        return $events;
    }

    /**
     * Return previously prepared substitutions for a specific match.
     *
     * Uses the match-indexed data received from the native results view;
     * the optional database selector remains part of the old API signature.
     *
     * @param mixed $matchId Match identifier requested by the layout.
     * @param mixed $databaseSelector Historical selector, not used for queries.
     * @return array<int, object> Substitutions for the match, or an empty list.
     */
    public static function getMatchSubstitutions($matchId = 0, $databaseSelector = 0): array
    {
        return self::$substitutionsByMatch[(int) $matchId] ?? [];
    }
}
