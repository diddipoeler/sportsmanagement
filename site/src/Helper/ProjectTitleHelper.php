<?php
/**
 * Joomla 5/6 SportsManagement site helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

/**
 * Build project page titles without loading the legacy sportsmanagementHelper.
 *
 * The formatting intentionally preserves the historical page_title_format
 * semantics used by existing frontend template configuration.
 */
final class ProjectTitleHelper
{
    /**
     * Create the data object required for project page title formatting.
     *
     * Provides the requested prefix and empty optional properties for
     * clubs, teams, rounds, players, venues and competition metadata so
     * native views can populate a consistent structure.
     *
     * @param string $prefix Introductory text for the page title.
     * @return object Mutable object of optional title components.
     */
    public static function createInfo(string $prefix): object
    {
        return (object) [
            'prefix' => $prefix,
            'clubName' => null,
            'team1Name' => null,
            'team2Name' => null,
            'roundName' => null,
            'personName' => null,
            'playgroundName' => null,
            'projectName' => null,
            'divisionName' => null,
            'leagueName' => null,
            'seasonName' => null,
        ];
    }

    /**
     * Render a SportsManagement page title using legacy formatting rules.
     *
     * Collects available person, venue, team, club and round labels,
     * combines the project and division, and appends league and season
     * details according to the existing page_title_format option.
     * Invalid nested legacy values are ignored rather than being cast to
     * strings, avoiding warnings on PHP 8 and malformed page titles.
     *
     * @param object $titleInfo Title components created by createInfo().
     * @param int|string $format Legacy numeric page title format selector.
     * @return string Formatted title including the configured prefix.
     */
    public static function format(object $titleInfo, int|string $format): string
    {
        $parts = [];

        foreach (['personName', 'playgroundName'] as $property) {
            $raw = $titleInfo->{$property} ?? '';
            $value = is_string($raw) || is_int($raw) ? trim((string) $raw) : '';
            if ($value !== '') {
                $parts[] = $value;
            }
        }

        $team1 = self::titleText($titleInfo->team1Name ?? null);
        $team2 = self::titleText($titleInfo->team2Name ?? null);
        if ($team1 !== '') {
            $parts[] = $team2 !== '' ? $team1 . ' - ' . $team2 : $team1;
        }

        foreach (['clubName', 'roundName'] as $property) {
            $value = trim((string) ($titleInfo->{$property} ?? ''));
            if ($value !== '') {
                $parts[] = $value;
            }
        }

        $projectDivision = self::titleText($titleInfo->projectName ?? null);
        $division = self::titleText($titleInfo->divisionName ?? null);
        if ($division !== '') {
            $projectDivision .= ($projectDivision !== '' ? ' - ' : '') . $division;
        }

        $league = self::titleText($titleInfo->leagueName ?? null);
        $season = self::titleText($titleInfo->seasonName ?? null);

        switch ((int) $format) {
            case 0:
                self::append($parts, $projectDivision);
                break;

            case 1:
                self::append($parts, $projectDivision);
                self::append($parts, $league);
                break;

            case 2:
                self::append($parts, $projectDivision);
                self::append($parts, $league);
                self::append($parts, $season);
                break;

            case 3:
                self::append($parts, $projectDivision);
                self::append($parts, $season);
                break;

            case 4:
                self::append($parts, $league);
                break;

            case 5:
                self::append($parts, $league);
                self::append($parts, $season);
                break;

            case 6:
                self::append($parts, $season);
                break;

            case 7:
                break;
        }

        return self::titleText($titleInfo->prefix ?? null) . ': ' . implode(' | ', $parts);
    }

    /**
     * Coerce only safe printable scalar page-title fragments.
     *
     * Import data may provide unexpected nested values for optional title
     * fields. Ignoring arrays, objects and non-finite floats avoids PHP 8
     * conversion diagnostics and invalid frontend metadata.
     *
     * @param mixed $value Raw project, club, team or title fragment.
     * @return string Trimmed fragment, or empty text for invalid input.
     */
    private static function titleText(mixed $value): string
    {
        if (is_float($value) && !is_finite($value)) {
            return '';
        }

        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            return '';
        }

        return trim((string) $value);
    }

    /**
     * Append a nonempty title segment without inserting blank separators.
     *
     * Keeps the segment ordering controlled by format() and filters out
     * empty optional values from the final joined title.
     *
     * @param array $parts Output title segments modified in place.
     * @param string $value Optional nonempty title segment.
     * @return void
     */
    private static function append(array &$parts, string $value): void
    {
        if ($value !== '') {
            $parts[] = $value;
        }
    }
}
