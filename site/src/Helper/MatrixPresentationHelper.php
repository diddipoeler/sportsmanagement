<?php
/**
 * Native Joomla 5/6 match matrix team and score presentation.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

/**
 * Normalize matrix teams and scores without executing legacy HTML markup.
 *
 * Produces a compact indexed representation of matches for the Joomla
 * presentation template. All stored names and score values remain plain data.
 */
final class MatrixPresentationHelper
{
    /**
     * Preserve at most 128 unique positive project-team IDs and safe labels.
     *
     * Rejects malformed records, objects in name properties, oversized
     * slugs and duplicates before rendering a quadratic matrix table.
     *
     * @param iterable<mixed> $teams Teams returned by the native matrix model.
     * @return array<int,array{id:int,name:string,teamSlug:string}> Indexed team metadata.
     */
    public static function teams(iterable $teams): array
    {
        $rows = [];

        foreach ($teams as $team) {
            if (!is_object($team)) {
                continue;
            }

            $id = self::id($team->projectteamid ?? null);
            if ($id === 0 || isset($rows[$id])) {
                continue;
            }

            $name = $team->name ?? '';
            $slug = $team->team_slug ?? '';

            $rows[$id] = [
                'id' => $id,
                'name' => is_string($name) ? $name : '',
                'teamSlug' => is_string($slug) && strlen($slug) <= 128 ? $slug : '',
            ];

            if (count($rows) === 128) {
                break;
            }
        }

        return $rows;
    }

    /**
     * Group all valid results by home and away team in linear match time.
     *
     * Supports multiple matches per pair, decision scores and cancelled
     * games. Never copies untrusted result strings into HTML, and excludes
     * fixtures whose teams are not part of the current matrix selection.
     *
     * @param iterable<mixed> $matches Project-scoped match records.
     * @param array<int,array{id:int,name:string,teamSlug:string}> $teams Visible teams.
     * @return array<int,array<int,array<int,string>>> Text-only fixture labels.
     */
    public static function results(iterable $matches, array $teams): array
    {
        $cells = [];

        foreach ($matches as $match) {
            if (!is_object($match)) {
                continue;
            }

            $homeId = self::id($match->projectteam1_id ?? null);
            $awayId = self::id($match->projectteam2_id ?? null);
            if ($homeId === 0 || $awayId === 0 || $homeId === $awayId
                || !isset($teams[$homeId], $teams[$awayId])) {
                continue;
            }

            $decision = self::id($match->decision ?? 0) > 0;
            $home = self::score($decision ? ($match->v1 ?? null) : ($match->e1 ?? null));
            $away = self::score($decision ? ($match->v2 ?? null) : ($match->e2 ?? null));

            $label = !empty($match->cancel) || ($home === null && $away === null)
                ? '–'
                : ($home ?? '–') . ':' . ($away ?? '–');

            if (count($cells[$homeId][$awayId] ?? []) < 20) {
                $cells[$homeId][$awayId][] = $label;
            }
        }

        return $cells;
    }

    /**
     * Accept only positive decimal database IDs without PHP type conversion.
     *
     * @param mixed $value Raw numeric team identifier.
     * @return int Valid positive ID or zero.
     */
    private static function id(mixed $value): int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : 0;
        }

        if (!is_string($value) || preg_match('/^[1-9][0-9]*$/D', $value) !== 1) {
            return 0;
        }

        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? 0 : $id;
    }

    /**
     * Normalize a match score to a safe decimal display value.
     *
     * Scores from zero through 9999 are supported. Rejects markup, floats,
     * negative values, nested PHP types and overflowing database strings.
     *
     * @param mixed $value Stored normal or decision score.
     * @return string|null Decimal text or null when invalid/missing.
     */
    private static function score(mixed $value): ?string
    {
        if (is_int($value)) {
            return $value >= 0 && $value <= 9999 ? (string) $value : null;
        }

        if (!is_string($value) || preg_match('/^[0-9]{1,4}$/D', $value) !== 1) {
            return null;
        }

        return (string) (int) $value;
    }
}
