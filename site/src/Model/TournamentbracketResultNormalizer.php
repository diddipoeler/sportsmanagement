<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

/**
 * Normalises match result fields for tournament-bracket rendering.
 *
 * The legacy bracket builder repeated this logic in several nested loops.
 */
final class TournamentbracketResultNormalizer
{
    /**
     * @return array{home:mixed,away:mixed}
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

        // Preserve the historic SIS/JSM behaviour: when a drawn regular result
        // has a shoot-out but no explicit overtime value, regular time becomes
        // the overtime base before shoot-out values are appended.
        if ($this->hasValue($home)
            && $this->hasValue($away)
            && (string) $home === (string) $away
            && !$this->hasValue($homeOt)
            && $this->hasValue($homeSo)
        ) {
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

        $displayHome = $this->appendResultPart($displayHome, 'OT', $homeOt);
        $displayAway = $this->appendResultPart($displayAway, 'OT', $awayOt);
        $displayHome = $this->appendResultPart($displayHome, 'SO', $homeSo);
        $displayAway = $this->appendResultPart($displayAway, 'SO', $awaySo);
        $displayHome = $this->appendResultPart($displayHome, 'DE', $homeDecision);
        $displayAway = $this->appendResultPart($displayAway, 'DE', $awayDecision);

        return [
            'home' => $displayHome,
            'away' => $displayAway,
        ];
    }

    private function appendResultPart(mixed $base, string $label, mixed $value): mixed
    {
        if (!$this->hasValue($value)) {
            return $base;
        }

        $prefix = $this->hasValue($base) ? (string) $base : '';

        return trim($prefix . ' ' . $label . ':' . (string) $value);
    }

    private function hasValue(mixed $value): bool
    {
        return $value !== null && $value !== '';
    }

    private function isNumericScore(mixed $value): bool
    {
        return $this->hasValue($value) && is_numeric($value);
    }
}
