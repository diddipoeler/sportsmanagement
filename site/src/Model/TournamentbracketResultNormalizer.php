<?php
/**
 * Native Joomla 5/6 tournament bracket result normalizer.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

/**
 * Normalises match result fields for tournament-bracket rendering while
 * preserving the historic SportsManagement OT/SO/decision formatting.
 */
final class TournamentbracketResultNormalizer
{
    /**
     * Build the home and away scores displayed in a tournament bracket.
     *
     * Preserves historic extra-time, shootout and match-decision notation,
     * including the special handling of draws and numeric shootout scores.
     * Discards nested or nonfinite legacy score fields without PHP 8 casting
     * warnings and avoids float overflow when combining shootout scores.
     *
     * @param object $match Match row containing regular and optional OT/SO results.
     * @return array{home:mixed,away:mixed} Normalised display results.
     */
    public function normalise(object $match): array
    {
        $home = $this->normaliseScoreValue($match->team1_result ?? null);
        $away = $this->normaliseScoreValue($match->team2_result ?? null);
        $homeOt = $this->normaliseScoreValue($match->team1_result_ot ?? null);
        $awayOt = $this->normaliseScoreValue($match->team2_result_ot ?? null);
        $homeSo = $this->normaliseScoreValue($match->team1_result_so ?? null);
        $awaySo = $this->normaliseScoreValue($match->team2_result_so ?? null);
        $homeDecision = $this->normaliseScoreValue($match->team1_result_decision ?? null);
        $awayDecision = $this->normaliseScoreValue($match->team2_result_decision ?? null);

        $isDraw = $this->hasValue($home)
            && $this->hasValue($away)
            && (string) $home === (string) $away;

        if ($isDraw) {
            if (!$this->hasValue($homeOt) && $this->hasValue($homeSo)) {
                $homeOt = $home;
                $awayOt = $away;
            }

            $displayHome = $this->hasValue($homeOt) ? $homeOt : $home;
            $displayAway = $this->hasValue($awayOt) ? $awayOt : $away;

            if ($this->hasValue($homeSo) && $this->isNumericScore($home) && $this->isNumericScore($homeSo)) {
                $addedHome = (float) $home + (float) $homeSo;

                if (is_finite($addedHome)) {
                    $displayHome = $addedHome;
                }
            }

            if ($this->hasValue($awaySo) && $this->isNumericScore($away) && $this->isNumericScore($awaySo)) {
                $addedAway = (float) $away + (float) $awaySo;

                if (is_finite($addedAway)) {
                    $displayAway = $addedAway;
                }
            }
        } else {
            $displayHome = $this->hasValue($home) ? $home : null;
            $displayAway = $this->hasValue($away) ? $away : null;
        }

        $displayHome = $this->appendResultPart($displayHome, 'OT', $homeOt);
        $displayAway = $this->appendResultPart($displayAway, 'OT', $awayOt);
        $displayHome = $this->appendResultPart($displayHome, 'SO', $homeSo);
        $displayAway = $this->appendResultPart($displayAway, 'SO', $awaySo);
        $displayHome = $this->appendResultPart($displayHome, 'DE', $homeDecision);
        $displayAway = $this->appendResultPart($displayAway, 'DE', $awayDecision);

        return ['home' => $displayHome, 'away' => $displayAway];
    }

    /**
     * Restrict legacy match score fields to safe scalar display values.
     *
     * Preserves numeric zero, accepted numeric values and regular score
     * strings while refusing arrays, objects, booleans, oversized text and
     * nonfinite floats. Invalid legacy data is treated as absent so no
     * array-to-string PHP warnings reach a Joomla tournament view.
     *
     * @param mixed $value Raw score or optional OT/SO/decision field.
     * @return string|int|float|null Safe score value or null.
     */
    private function normaliseScoreValue(mixed $value): string|int|float|null
    {
        if (is_int($value) || (is_float($value) && is_finite($value))) {
            return $value;
        }

        if (!is_string($value) || strlen($value) > 128
            || preg_match('/[\x00-\x1F\x7F]/', $value) === 1) {
            return null;
        }

        return $value;
    }

    /**
     * Append a labelled extra result part when its value is present.
     *
     * @param mixed $base Base score or previously formatted result.
     * @param string $label Result part label such as OT, SO or DE.
     * @param mixed $value Additional result value.
     * @return mixed Base result with the appended label or unchanged base.
     */
    private function appendResultPart(mixed $base, string $label, mixed $value): mixed
    {
        if (!$this->hasValue($value)) {
            return $base;
        }

        return (string) ($base ?? '') . ' ' . $label . ':' . (string) $value;
    }

    /**
     * Determine whether a match result has a value, including numeric zero.
     *
     * @param mixed $value Result field to inspect.
     * @return bool Whether the field is a supported nonempty score value.
     */
    private function hasValue(mixed $value): bool
    {
        return $value !== null && $value !== ''
            && (is_string($value) || is_int($value)
                || (is_float($value) && is_finite($value)));
    }

    /**
     * Check whether a present match result can be used in score arithmetic.
     *
     * @param mixed $value Score from a regular or shootout result field.
     * @return bool True for present numeric scores.
     */
    private function isNumericScore(mixed $value): bool
    {
        return $this->hasValue($value) && is_numeric($value)
            && is_finite((float) $value);
    }
}
