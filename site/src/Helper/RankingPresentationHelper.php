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

use Joomla\CMS\Language\Text;

/** Presentation-only helpers for native ranking tables. */
final class RankingPresentationHelper
{
    /** @return array<int,array{code:string,label:string,sort:string}> */
    public static function columns(array $config): array
    {
        $codes = array_values(array_filter(array_map(
            static fn (string $value): string => strtoupper(trim($value)),
            explode(',', (string) ($config['ordered_columns'] ?? 'PLAYED,WINS,TIES,LOSSES,POINTS,SCOREFOR,SCOREAGAINST,DIFF'))
        )));
        $names = array_map('trim', explode(',', (string) ($config['ordered_columns_names'] ?? '')));
        $columns = [];

        foreach ($codes as $index => $code) {
            $label = trim((string) ($names[$index] ?? ''));
            if ($label === '') {
                $translated = Text::_('COM_SPORTSMANAGEMENT_' . $code);
                $label = $translated !== 'COM_SPORTSMANAGEMENT_' . $code ? $translated : $code;
            }

            $columns[] = [
                'code' => $code,
                'label' => $label,
                'sort' => self::sortKey($code),
            ];
        }

        return $columns;
    }

    /** @param array<int|string,object> $ranking @param array<int|string,object> $teams */
    public static function sort(array $ranking, string $order, string $direction, array $teams = []): array
    {
        $order = strtolower(trim($order));
        if ($order === '') {
            return $ranking;
        }

        $direction = strtoupper($direction) === 'DESC' ? -1 : 1;

        uasort($ranking, static function (object $a, object $b) use ($order, $direction, $teams): int {
            $left = self::sortValue($a, $order, $teams);
            $right = self::sortValue($b, $order, $teams);

            if (is_numeric($left) && is_numeric($right)) {
                $result = ((float) $left <=> (float) $right);
            } else {
                $result = strnatcasecmp((string) $left, (string) $right);
            }

            return $result * $direction;
        });

        return $ranking;
    }

    /**
     * Render a ranked team's configured statistic as display text.
     * Handles supported column codes, score pairs and optional model values.
     * @param object $team Ranking calculation row.
     * @param string $code Selected ranking column code.
     * @return string Escaped or formatted statistic.
     */
    public static function value(object $team, string $code): string
    {
        $code = strtoupper(trim($code));

        return match ($code) {
            'PLAYED' => self::number($team->cnt_matches ?? 0),
            'WINS' => self::number($team->cnt_won ?? 0),
            'TIES', 'DRAWS' => self::number($team->cnt_draw ?? 0),
            'LOSSES' => self::number($team->cnt_lost ?? 0),
            'WOT' => self::number($team->cnt_wot ?? 0),
            'WSO' => self::number($team->cnt_wso ?? 0),
            'LOT' => self::number($team->cnt_lot ?? 0),
            'LSO' => self::number($team->cnt_lso ?? 0),
            'WINPCT' => self::decimal(self::call($team, 'winPct')) . '%',
            'GB' => self::decimal($team->gb ?? 0),
            'LEGS' => self::pair($team->sum_team1_legs ?? 0, $team->sum_team2_legs ?? 0),
            'LEGS_DIFF' => self::number($team->diff_team_legs ?? 0),
            'LEGS_RATIO' => self::decimal(self::call($team, 'legsRatio')),
            'BALLS' => self::pair($team->sum_team1_balls ?? 0, $team->sum_team2_balls ?? 0),
            'BALLS_DIFF' => self::number($team->diff_team_balls ?? 0),
            'SCOREFOR', 'GOALSFOR' => self::number($team->sum_team1_result ?? $team->goalsfor ?? 0),
            'SCOREAGAINST', 'GOALSAGAINST' => self::number($team->sum_team2_result ?? $team->goalsagainst ?? 0),
            'SCOREPCT' => self::decimal(self::call($team, 'scorePct')) . '%',
            'RESULTS' => self::pair($team->sum_team1_result ?? 0, $team->sum_team2_result ?? 0),
            'DIFF' => self::number($team->diff_team_results ?? 0),
            'POINTS' => self::number(self::call($team, 'getPoints', $team->sum_points ?? 0)),
            'PENALTYPOINTS' => self::number($team->penalty_points ?? 0),
            'NEGPOINTS', 'OLDNEGPOINTS' => self::number($team->neg_points ?? 0),
            'POINTS_RATIO' => self::decimal(self::call($team, 'pointsRatio')),
            'BONUS' => self::number($team->bonus_points ?? 0),
            'START' => self::number($team->_startpoints ?? 0),
            'MATCHPOINTS' => self::pair($team->sum_team1_matchpoint ?? 0, $team->sum_team2_matchpoint ?? 0),
            'SETS' => self::pair($team->sum_team1_sets ?? 0, $team->sum_team2_sets ?? 0),
            'GAMES' => self::pair($team->sum_team1_games ?? 0, $team->sum_team2_games ?? 0),
            'SHOOTERRINGS' => self::number($team->shooterrings ?? 0),
            'GFA' => self::decimal(self::call($team, 'getGFA')),
            'GAA' => self::decimal(self::call($team, 'getGAA')),
            'PPG' => self::decimal(self::call($team, 'getPPG')),
            'PPP' => self::decimal(self::call($team, 'getPPP')) . '%',
            default => self::number($team->{strtolower($code)} ?? ''),
        };
    }

    /**
     * Render a movement indicator for a team relative to prior ranking.
     * @param object $team Current ranking row.
     * @param object|null $previous Previous ranking row, if known.
     * @return string Escaped HTML trend indicator or empty text.
     */
    public static function trend(object $team, ?object $previous): string
    {
        if (!$previous || !isset($previous->rank) || !isset($team->rank)) {
            return '';
        }

        $current = (int) $team->rank;
        $old = (int) $previous->rank;

        if ($current < $old) {
            return '<span class="text-success" title="' . self::escape(Text::_('COM_SPORTSMANAGEMENT_RANKING_UP')) . '">↑</span>';
        }
        if ($current > $old) {
            return '<span class="text-danger" title="' . self::escape(Text::_('COM_SPORTSMANAGEMENT_RANKING_DOWN')) . '">↓</span>';
        }

        return '<span class="text-muted" aria-hidden="true">–</span>';
    }

    /** @param array<int,array{from:mixed,to:mixed,color:mixed,description:mixed}> $colors */
    public static function colorForRank(int $rank, array $colors): string
    {
        foreach ($colors as $color) {
            $from = (int) ($color['from'] ?? 0);
            $to = (int) ($color['to'] ?? 0);
            $value = trim((string) ($color['color'] ?? ''));

            if ($value === '' || $from <= 0) {
                continue;
            }

            if ($rank >= $from && ($to <= 0 || $rank <= $to)) {
                return self::safeColor($value);
            }
        }

        return '';
    }

    /**
     * Map a configured ranking column code to a supported sort key.
     * @param string $code Ranking presentation column identifier.
     * @return string Recognised ranking sort key.
     */
    public static function sortKey(string $code): string
    {
        return match (strtoupper(trim($code))) {
            'PLAYED' => 'played',
            'WINS' => 'won',
            'TIES', 'DRAWS' => 'draw',
            'LOSSES' => 'loss',
            'WOT' => 'wot',
            'WSO' => 'wso',
            'LOT' => 'lot',
            'LSO' => 'lso',
            'WINPCT' => 'winpct',
            'LEGS_DIFF' => 'legsdiff',
            'LEGS_RATIO' => 'legsratio',
            'SCOREFOR', 'GOALSFOR' => 'goalsfor',
            'SCOREAGAINST', 'GOALSAGAINST' => 'goalsagainst',
            'RESULTS' => 'goalsp',
            'DIFF' => 'diff',
            'POINTS' => 'points',
            'PENALTYPOINTS' => 'penaltypoints',
            'NEGPOINTS', 'OLDNEGPOINTS' => 'negpoints',
            'POINTS_RATIO' => 'pointsratio',
            'BONUS' => 'bonus',
            'START' => 'start',
            default => strtolower(trim($code)),
        };
    }

    /**
     * Resolve the numeric or textual statistic used to order teams.
     * @param object $team Ranked team metrics.
     * @param string $order Resolved ranking sort key.
     * @param array $teams Additional project-team metadata.
     * @return mixed Sort value for the selected column.
     */
    private static function sortValue(object $team, string $order, array $teams): mixed
    {
        $metadata = $teams[(int) ($team->_ptid ?? 0)] ?? null;

        return match ($order) {
            'rank' => $team->rank ?? 0,
            'name' => $metadata->name ?? $team->_name ?? '',
            'played' => $team->cnt_matches ?? 0,
            'won' => $team->cnt_won ?? 0,
            'draw' => $team->cnt_draw ?? 0,
            'loss' => $team->cnt_lost ?? 0,
            'wot' => $team->cnt_wot ?? 0,
            'wso' => $team->cnt_wso ?? 0,
            'lot' => $team->cnt_lot ?? 0,
            'lso' => $team->cnt_lso ?? 0,
            'winpct' => self::call($team, 'winPct'),
            'goalsfor', 'goalsp' => $team->sum_team1_result ?? 0,
            'goalsagainst' => $team->sum_team2_result ?? 0,
            'legsdiff' => $team->diff_team_legs ?? 0,
            'legsratio' => self::call($team, 'legsRatio'),
            'diff' => $team->diff_team_results ?? 0,
            'points' => self::call($team, 'getPoints', $team->sum_points ?? 0),
            'penaltypoints' => $team->penalty_points ?? 0,
            'start' => $team->_startpoints ?? 0,
            'bonus' => $team->bonus_points ?? 0,
            'negpoints' => $team->neg_points ?? 0,
            'pointsratio' => self::call($team, 'pointsRatio'),
            default => $team->{$order} ?? 0,
        };
    }

    /**
     * Call an optional ranking calculator method with a safe fallback.
     * @param object $object Ranking data object.
     * @param string $method Optional calculated-statistic method.
     * @param mixed $fallback Value returned if method is unavailable.
     * @return mixed Computed value or fallback.
     */
    private static function call(object $object, string $method, mixed $fallback = 0): mixed
    {
        return method_exists($object, $method) ? $object->{$method}() : $fallback;
    }

    /**
     * Format two result counts separated by a colon.
     * @param mixed $left First result count.
     * @param mixed $right Second result count.
     * @return string Formatted result pair.
     */
    private static function pair(mixed $left, mixed $right): string
    {
        return self::number($left) . ':' . self::number($right);
    }

    /**
     * Format a numeric ranking value or escape nonnumeric text.
     * @param mixed $value Calculated statistic.
     * @return string Display-ready numeric or escaped value.
     */
    private static function number(mixed $value): string
    {
        if ($value === '' || $value === null) {
            return '';
        }

        return is_numeric($value) ? (string) ($value + 0) : self::escape((string) $value);
    }

    /**
     * Format a calculated fraction to at most two decimal places.
     * @param mixed $value Decimal ranking value.
     * @return string Normalised decimal display value.
     */
    private static function decimal(mixed $value): string
    {
        return is_numeric($value) ? rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.') : '0';
    }

    /**
     * Validate a CSS colour for use in ranking tables.
     * @param string $value Configured colour name or functional colour.
     * @return string Valid CSS colour or empty text.
     */
    private static function safeColor(string $value): string
    {
        return preg_match('/^(#[0-9a-f]{3,8}|[a-z]+|rgba?\([0-9.,% ]+\)|hsla?\([0-9.,% ]+\))$/i', $value)
            ? $value
            : '';
    }

    /**
     * Escape an arbitrary ranking label for safe HTML output.
     * @param string $value Raw label text.
     * @return string Escaped label.
     */
    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
