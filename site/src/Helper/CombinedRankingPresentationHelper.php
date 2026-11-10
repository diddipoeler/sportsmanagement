<?php
/**
 * Joomla 5/6 bounded display rows for the combined results and ranking page.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

/**
 * Reduce sports-specific ranking objects to safe, text-only frontend data.
 *
 * The calculation remains in RankingCalculationAdapter; this helper never
 * changes league points or sorts matches. It validates display metadata,
 * preserves the calculated order and rejects unexpected nested input.
 */
final class CombinedRankingPresentationHelper
{
    /**
     * Convert calculated division standings into bounded HTML-safe data fields.
     *
     * Only division rows and team entries with positive database identifiers
     * are included. Ranking labels are sourced from native project-team data,
     * never from an untrusted script fragment. The numeric fields reuse the
     * native RankingPresentationHelper mapping and are validated again as
     * decimal strings. Each group is capped to prevent excessive HTML output.
     *
     * @param array<mixed> $ranking Division-keyed native ranking calculation.
     * @param array<mixed> $teams Native project-team metadata keyed by ID.
     * @return array<int,array{division:int,rows:array<int,array{rank:int,name:string,played:string,wins:string,draws:string,losses:string,points:string}>}> Display groups.
     */
    public static function standings(array $ranking, array $teams): array
    {
        $groups = [];

        foreach ($ranking as $division => $members) {
            if (!is_array($members)) {
                continue;
            }

            $divisionId = self::id($division, true);
            if ($divisionId === null) {
                continue;
            }

            $rows = [];

            foreach ($members as $teamId => $teamRow) {
                if (!is_object($teamRow)) {
                    continue;
                }

                $id = self::id($teamId);
                if ($id === null) {
                    continue;
                }

                $team = $teams[$id] ?? null;
                $name = is_object($team) ? ($team->name ?? null) : null;

                if (!is_string($name) || $name === '') {
                    continue;
                }

                $rank = self::id($teamRow->rank ?? null);
                $rows[] = [
                    'rank' => $rank ?? (count($rows) + 1),
                    'name' => self::shortText($name),
                    'played' => self::stat($teamRow, 'PLAYED'),
                    'wins' => self::stat($teamRow, 'WINS'),
                    'draws' => self::stat($teamRow, 'TIES'),
                    'losses' => self::stat($teamRow, 'LOSSES'),
                    'points' => self::stat($teamRow, 'POINTS'),
                ];

                if (count($rows) >= 128) {
                    break;
                }
            }

            if ($rows !== []) {
                $groups[] = ['division' => $divisionId, 'rows' => $rows];
            }

            if (count($groups) >= 32) {
                break;
            }
        }

        return $groups;
    }

    /**
     * Render only the numeric result of a native ranking statistics lookup.
     *
     * RankingPresentationHelper provides the sport-specific points mapping
     * (including optional getPoints() methods). The combined view strips any
     * unexpected HTML or nonnumeric strings rather than reusing raw markup.
     *
     * @param object $row One calculated ranking entry.
     * @param string $column Approved native ranking column code.
     * @return string Bounded decimal statistic or an en dash if unavailable.
     */
    private static function stat(object $row, string $column): string
    {
        $value = RankingPresentationHelper::value($row, $column);

        return is_string($value) && preg_match('/^-?[0-9]{1,9}(?:\.[0-9]{1,4})?$/D', $value) === 1
            ? $value
            : '–';
    }

    /**
     * Validate a positive project-team identifier or optional zero division.
     *
     * Rejects booleans, floats, negative values, malformed decimal strings
     * and integers outside the platform range before indexing project data.
     *
     * @param mixed $raw Candidate ID returned by native ranking calculations.
     * @param bool $allowZero Whether division key zero represents a whole league.
     * @return int|null Accepted integer identifier or null for invalid values.
     */
    private static function id(mixed $raw, bool $allowZero = false): ?int
    {
        if (is_int($raw)) {
            return $raw > 0 || ($allowZero && $raw === 0) ? $raw : null;
        }

        if (!is_string($raw)
            || preg_match($allowZero ? '/^(?:0|[1-9][0-9]*)$/D' : '/^[1-9][0-9]*$/D', $raw) !== 1) {
            return null;
        }

        $value = filter_var($raw, FILTER_VALIDATE_INT);

        return $value !== false && ($value > 0 || ($allowZero && $value === 0))
            ? $value
            : null;
    }

    /**
     * Cap a stored team name while preserving common UTF-8 lettering.
     *
     * The Joomla HTML layout independently escapes the returned text, so
     * script and entity-looking names cannot become executable markup.
     *
     * @param string $name Project team display name from Joomla's database.
     * @return string Team label limited to 256 Unicode characters when possible.
     */
    private static function shortText(string $name): string
    {
        return function_exists('mb_substr')
            ? mb_substr($name, 0, 256, 'UTF-8')
            : substr($name, 0, 1024);
    }
}
