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

    public static function register(): void
    {
        if (!class_exists('sportsmanagementModelProject', false)) {
            class_alias(self::class, 'sportsmanagementModelProject');
        }
    }

    /**
     * @param array<int, array<int, object>> $eventsByMatch
     * @param array<int, array<int, object>> $substitutionsByMatch
     * @param array<int, int> $favteams
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

    public static function getMatchEvents($matchId = 0, $showName = 0, $sortDesc = 0, $databaseSelector = 0): array
    {
        $events = self::$eventsByMatch[(int) $matchId] ?? [];

        if ((int) $sortDesc === 1) {
            $events = array_reverse($events);
        }

        return $events;
    }

    public static function getMatchSubstitutions($matchId = 0, $databaseSelector = 0): array
    {
        return self::$substitutionsByMatch[(int) $matchId] ?? [];
    }
}
