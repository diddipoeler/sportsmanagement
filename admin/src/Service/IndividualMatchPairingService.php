<?php
/**
 * Builds the individual-match pairing cards used by the administrator generator view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

final class IndividualMatchPairingService
{
    /**
     * Generate individual match cards for the selected competition format.
     *
     * Resolves the historical pairing pattern, skips positions absent from either
     * roster and returns objects with player IDs and their original position codes.
     *
     * @param int $mode Pairing format from the administrator match generator.
     * @param array<string, int> $homePositions Home players indexed by position code.
     * @param array<string, int> $awayPositions Away players indexed by position code.
     * @param int $homeCount Number of available home players for pattern selection.
     * @param int $awayCount Number of available away players for pattern selection.
     * @return array<int, object> Generated pairing cards.
     */
    public function build(
        int $mode,
        array $homePositions,
        array $awayPositions,
        int $homeCount,
        int $awayCount
    ): array {
        $key = $mode === 3 ? '33' : (string) $homeCount . (string) $awayCount;
        $pattern = $this->patterns()[$mode][$key] ?? [];
        $matches = [];

        foreach ($pattern as [$homePosition, $awayPosition]) {
            if (!array_key_exists($homePosition, $homePositions) || !array_key_exists($awayPosition, $awayPositions)) {
                continue;
            }

            $matches[] = (object) [
                'teamplayer1_id' => (int) $homePositions[$homePosition],
                'teamplayer2_id' => (int) $awayPositions[$awayPosition],
                'teamplayer1_position' => $homePosition,
                'teamplayer2_position' => $awayPosition,
            ];
        }

        return $matches;
    }

    /**
     * Historical match-card definitions, represented as data instead of executable view code.
     *
     * @return array<int, array<string, array<int, array{0:string,1:string}>>>
     */
    public function patterns(): array
    {
        return [
            1 => [
                '33' => $this->pairs('C:Y,B:X,A:Y,C:W,A:X,B:W,C:X,B:Y,A:W,Double:Double'),
                '43' => $this->pairs('C:Y,B:X,A:Y,D:W,A:X,B:W,C:X,D:Y,A:W,Double:Double'),
                '34' => $this->pairs('C:Y,B:X,A:Z,C:W,A:X,B:W,C:Z,B:Y,A:W,Double:Double'),
                '44' => $this->pairs('C:Y,B:X,A:Z,D:W,A:X,B:W,C:Z,D:Y,A:W,Double:Double'),
            ],
            2 => [
                '22' => $this->pairs('A:W,B:X,Double:Double,A:X,B:W'),
                '23' => $this->pairs('A:W,B:X,Double:Double,A:Y,B:Y'),
                '32' => $this->pairs('C:W,C:X,Double:Double,A:X,B:W'),
                '33' => $this->pairs('C:W,C:X,Double:Double,A:Y,B:Y'),
            ],
            3 => [
                '33' => $this->pairs('A:W,B:X,Double:Double,A:X,B:W'),
            ],
        ];
    }

    /**
     * Convert a compact match-card definition to ordered home/away positions.
     *
     * Each comma-delimited element contains a home and away code separated by
     * a colon; the resulting pairs are used to generate stable match cards.
     *
     * @param string $definition Comma-delimited home:away position pairs.
     * @return array<int, array{0:string,1:string}> Ordered position pairs.
     */
    private function pairs(string $definition): array
    {
        $pairs = [];

        foreach (explode(',', $definition) as $item) {
            [$home, $away] = explode(':', $item, 2);
            $pairs[] = [$home, $away];
        }

        return $pairs;
    }
}
