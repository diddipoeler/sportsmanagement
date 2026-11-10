<?php
/**
 * Joomla 5/6 tournament tree JSON data preparation for the site frontend.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

/**
 * Convert legacy tournament round and match records to safe browser data.
 *
 * The old tournament view concatenated database values into executable
 * JavaScript. The native Joomla template instead serialises the returned
 * arrays with JSON encoding and displays team labels using textContent.
 */
final class TournamentTreePayload
{
    private const MAX_ROUNDS = 64;
    private const MAX_MATCHES_PER_ROUND = 1024;

    /**
     * Build the initial bracket team names, ordered rounds and result pairs.
     *
     * Keeps the legacy numeric round order, excludes malformed records and
     * bounds each collection before it reaches the browser. The first round
     * supplies the visible team names; each round supplies its current
     * match scores. Missing scores and invalid nested values become null.
     *
     * @param array<int,mixed> $rounds Tournament round records from the native model.
     * @param array<int,mixed> $bracket Match objects indexed by numeric round code.
     * @return array{teams:array,results:array,rounds:array} JSON-ready bracket data.
     */
    public static function build(array $rounds, array $bracket): array
    {
        $roundNames = [];

        foreach (array_slice($rounds, 0, self::MAX_ROUNDS) as $round) {
            if (!is_object($round)) {
                continue;
            }

            $code = self::positiveId($round->roundcode ?? null);

            if ($code === null) {
                continue;
            }

            $name = $round->name ?? '';
            $roundNames[$code] = [
                'roundname' => is_string($name) ? self::shortText($name) : '',
            ];
        }

        ksort($roundNames, SORT_NUMERIC);
        $firstCode = array_key_first($roundNames);
        $teams = [];
        $results = [];

        foreach ($roundNames as $code => $roundName) {
            $scoreRows = [];
            $matches = $bracket[$code] ?? [];
            if (!is_array($matches)) {
                $matches = [];
            }

            foreach (array_slice(array_values($matches), 0, self::MAX_MATCHES_PER_ROUND) as $item) {
                if (!is_object($item)) {
                    continue;
                }

                $scoreRows[] = [
                    self::score($item->team1_result ?? null),
                    self::score($item->team2_result ?? null),
                ];

                if ($code === $firstCode) {
                    $teams[] = [
                        self::teamName($item->firstname ?? null),
                        self::teamName($item->secondname ?? null),
                    ];
                }
            }

            $results[] = $scoreRows;
        }

        return [
            'teams' => $teams,
            'results' => $results,
            'rounds' => array_values($roundNames),
        ];
    }

    /**
     * Convert a database score into a bounded, safe numeric result.
     *
     * Tournament scores are nonnegative integers. Reject HTML fragments,
     * negative values, fractional scores, arrays, objects and overflow
     * rather than using the historical raw JavaScript concatenation.
     *
     * @param mixed $raw Database match score.
     * @return int|null Accepted score or null when absent or malformed.
     */
    private static function score(mixed $raw): ?int
    {
        if (is_int($raw)) {
            return $raw >= 0 && $raw <= 9999 ? $raw : null;
        }

        if (!is_string($raw) || preg_match('/^[0-9]{1,4}$/D', $raw) !== 1) {
            return null;
        }

        return (int) $raw;
    }

    /**
     * Validate a positive tournament round code without lossy conversions.
     *
     * @param mixed $raw Round number stored by the database driver.
     * @return int|null Positive bounded code, or null if invalid.
     */
    private static function positiveId(mixed $raw): ?int
    {
        if (is_int($raw)) {
            return $raw > 0 ? $raw : null;
        }

        if (!is_string($raw) || preg_match('/^[1-9][0-9]*$/D', $raw) !== 1) {
            return null;
        }

        $id = filter_var($raw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? null : $id;
    }

    /**
     * Clean the browser-facing team name without interpreting database markup.
     *
     * The JavaScript renderer inserts this as text, not HTML. Do not decode
     * entities or add image markup: a saved team name must never become a
     * script or HTML attribute in a public tournament bracket.
     *
     * @param mixed $value Untrusted stored team name.
     * @return string Short plain-text team name, or empty for invalid values.
     */
    private static function teamName(mixed $value): string
    {
        return is_string($value) ? self::shortText($value) : '';
    }

    /**
     * Bound a Unicode display label while keeping valid multibyte characters.
     *
     * Uses mbstring when installed, otherwise limits byte length and lets
     * JSON_INVALID_UTF8_SUBSTITUTE handle any truncated multibyte sequence.
     *
     * @param string $text Stored name or translated round label.
     * @return string Text reduced to a safe 256-character display maximum.
     */
    private static function shortText(string $text): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, 256, 'UTF-8');
        }

        return substr($text, 0, 1024);
    }
}
