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
     *
     * @param object $match Match row containing regular and optional OT/SO results.
     * @return array{home:mixed,away:mixed} Normalised display results.
     */
    public function normalise(object $match): array
    {
        $home = $match->team1_result ?? null;
        $away = $match->team2_result ?? null;
        $homeOt = $match->team1_result_ot ?? null;
        $awayOt = $match->team2_result_ot ?? null;
        $homeSo = $match->team1_result_so ?? null;
        $awaySo = $match->team2_result_so ?? null;
        $homeDecision = $match->team1_result_decision ?? null;
        $awayDecision = $match->team2_result_decision ?? null;

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
                $displayHome = (float) $home + (float) $homeSo;
            }

            if ($this->hasValue($awaySo) && $this->isNumericScore($away) && $this->isNumericScore($awaySo)) {
                $displayAway = (float) $away + (float) $awaySo;
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
     * @return bool False only for null or empty-string values.
     */
    private function hasValue(mixed $value): bool
    {
        return $value !== null && $value !== '';
    }

    /**
     * Check whether a present match result can be used in score arithmetic.
     *
     * @param mixed $value Score from a regular or shootout result field.
     * @return bool True for present numeric scores.
     */
    private function isNumericScore(mixed $value): bool
    {
        return $this->hasValue($value) && is_numeric($value);
    }
}
