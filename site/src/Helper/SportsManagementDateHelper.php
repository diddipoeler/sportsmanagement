<?php
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

/** Date conversion used by native site templates. */
final class SportsManagementDateHelper
{
    public static function convertDate(string $date, int $direction = 1): string
    {
        if (!str_contains($date, '-')) {
            if (strlen($date) === 8) {
                return substr($date, 4, 4)
                    . '-' . substr($date, 2, 2)
                    . '-' . substr($date, 0, 2);
            }

            if (strlen($date) === 6) {
                return substr(date('Y'), 0, 2)
                    . substr($date, 4, 2)
                    . '-' . substr($date, 2, 2)
                    . '-' . substr($date, 0, 2);
            }

            return '';
        }

        if ($direction === 1) {
            return substr($date, 8)
                . '-' . substr($date, 5, 2)
                . '-' . substr($date, 0, 4);
        }

        return substr($date, 6, 4)
            . '-' . substr($date, 3, 2)
            . '-' . substr($date, 0, 2);
    }
}
