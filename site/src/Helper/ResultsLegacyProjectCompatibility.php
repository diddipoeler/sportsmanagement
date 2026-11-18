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
     * Ignores malformed imported identifiers and nested legacy names,
     * preventing PHP 8 array-to-string conversions and forged slugs.
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
        self::$projectid = self::positiveId($project->id ?? null);
        self::$seasonid = self::positiveId($project->season_id ?? null);
        self::$cfg_which_database = $databaseSelector === 1 ? 1 : 0;
        self::$favteams = [];

        foreach ($favteams as $teamId) {
            $id = self::positiveId($teamId);

            if ($id > 0) {
                self::$favteams[] = $id;
            }
        }

        self::$projectslug = self::slug($project->slug ?? null, (string) self::$projectid);
        self::$roundslug = self::slug($project->round_slug ?? null, (string) max(0, $roundId));
        self::$divisionslug = self::slug($project->division_slug ?? null, '');
        self::$eventsByMatch = $eventsByMatch;
        self::$substitutionsByMatch = $substitutionsByMatch;
    }

    /**
     * Return prepared match events for a legacy results template.
     *
     * Reads the in-memory events supplied by sync(), preserving historical
     * arguments for template compatibility. Reverses event order when the
     * legacy descending-order flag is set, without executing a query.
     * Requires a complete positive match ID and an explicit descending
     * flag so corrupted PHP 8 input cannot return another match's events.
     *
     * @param mixed $matchId Match identifier requested by the template.
     * @param mixed $showName Legacy display flag, retained for compatibility.
     * @param mixed $sortDesc Reverse event order when equal to one.
     * @param mixed $databaseSelector Legacy database selector argument.
     * @return array<int, object> Events for the match, or an empty list.
     */
    public static function getMatchEvents($matchId = 0, $showName = 0, $sortDesc = 0, $databaseSelector = 0): array
    {
        $id = self::positiveId($matchId);

        if ($id <= 0) {
            return [];
        }

        $events = self::$eventsByMatch[$id] ?? [];

        if ($sortDesc === 1 || $sortDesc === '1') {
            $events = array_reverse($events);
        }

        return $events;
    }

    /**
     * Return previously prepared substitutions for a specific match.
     *
     * Uses the match-indexed data received from the native results view;
     * the optional database selector remains part of the old API signature.
     * Rejects malformed and overflowing match IDs instead of casting them
     * to a different match or raising PHP 8 conversion warnings.
     *
     * @param mixed $matchId Match identifier requested by the layout.
     * @param mixed $databaseSelector Historical selector, not used for queries.
     * @return array<int, object> Substitutions for the match, or an empty list.
     */
    public static function getMatchSubstitutions($matchId = 0, $databaseSelector = 0): array
    {
        $id = self::positiveId($matchId);

        return $id > 0 ? (self::$substitutionsByMatch[$id] ?? []) : [];
    }

    /**
     * Parse an exact positive identifier from a legacy result template.
     *
     * Preserves zero-padded decimal IDs while rejecting nested values,
     * booleans, negatives, partial numeric strings and PHP integer overflow.
     * This avoids selecting another match or project by a lossy PHP cast.
     *
     * @param mixed $value Imported ID or view argument.
     * @return int Valid positive record ID, or zero when invalid.
     */
    private static function positiveId(mixed $value): int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : 0;
        }

        if (!is_string($value) || $value === '' || strlen($value) > 64
            || preg_match('/^[0-9]+$/D', $value) !== 1) {
            return 0;
        }

        $digits = ltrim($value, '0');

        if ($digits === '') {
            return 0;
        }

        $id = filter_var($digits, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        return $id === false ? 0 : $id;
    }

    /**
     * Read one legacy route slug without converting nested stored values.
     *
     * Preserves normal Joomla UTF-8 slugs and bounded ID:alias formats.
     * Missing, malformed or control-bearing values use the supplied safe
     * fallback rather than producing the string "Array" in frontend links.
     *
     * @param mixed $value Raw imported project, division or round slug.
     * @param string $fallback Safe text to use when no valid slug exists.
     * @return string Valid route slug or fallback.
     */
    private static function slug(mixed $value, string $fallback): string
    {
        if (!is_string($value) || $value === '' || strlen($value) > 512
            || preg_match('/[\\x00-\\x1F\\x7F]/', $value) === 1
            || preg_match('//u', $value) !== 1) {
            return $fallback;
        }

        return $value;
    }
}
