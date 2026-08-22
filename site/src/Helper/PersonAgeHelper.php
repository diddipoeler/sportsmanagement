<?php
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

/**
 * Calculate a person's age while preserving the historical SportsManagement semantics.
 */
final class PersonAgeHelper
{
    public static function calculate(string $date, string $secondDate = '0000-00-00'): int|string
    {
        $birth = self::parseDate($date);

        if ($birth === null) {
            return '-';
        }

        if ($secondDate === '0000-00-00') {
            $target = [
                (int) date('Y'),
                (int) date('m'),
                (int) date('d'),
            ];
        } else {
            $target = self::parseDate($secondDate);

            if ($target === null) {
                return '-';
            }
        }

        $age = $target[0] - $birth[0];

        if ($birth[1] > $target[1] || ($birth[1] === $target[1] && $birth[2] > $target[2])) {
            --$age;
        }

        return $age;
    }

    /**
     * @return array{0:int,1:int,2:int}|null
     */
    private static function parseDate(string $date): ?array
    {
        if ($date === '0000-00-00'
            || preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $date, $matches) !== 1
        ) {
            return null;
        }

        return [(int) $matches[1], (int) $matches[2], (int) $matches[3]];
    }
}
