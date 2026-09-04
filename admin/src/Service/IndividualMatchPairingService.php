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
     * @return array<int, object>
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

    /** @return array<int, array{0:string,1:string}> */
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
