<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Joomla 5/6 date conversion helpers extracted from sportsmanagementHelper.
 */
final class SportsManagementDateHelper
{
    /**
     * Preserve the historical SportsManagement date conversion contract.
     *
     * Direction 1 converts YYYY-MM-DD to DD-MM-YYYY.
     * Any other direction converts DD-MM-YYYY to YYYY-MM-DD.
     * Compact DDMMYYYY and DDMMYY inputs are always converted to YYYY-MM-DD.
     */
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

    /**
     * Preserve getTimestamp() for the no-offset calls used by native code.
     */
    public static function getTimestamp(?string $date = null): int
    {
        $date = trim((string) $date);

        if ($date === '' || $date === '0000-00-00 00:00:00' || $date === '0000-00-00 15:30:00') {
            $date = 'now';
        }

        $timestamp = strtotime($date);

        if ($timestamp === false) {
            return 0;
        }

        return Factory::getDate($timestamp)->getTimestamp();
    }
}
