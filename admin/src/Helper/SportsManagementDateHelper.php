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
     * Normalise a date value for a SQL DATE column.
     *
     * Joomla calendar fields can submit the configured display format while
     * MySQL/MariaDB strict mode only accepts ISO dates for DATE columns.
     * Empty and historical zero-date values become NULL.
     */
    public static function toSqlDate(?string $date): ?string
    {
        $date = trim((string) $date);

        if ($date === '' || $date === '0000-00-00' || $date === '00-00-0000') {
            return null;
        }

        foreach (['Y-m-d', 'd-m-Y', 'd.m.Y', 'd/m/Y'] as $format) {
            $parsed = \DateTimeImmutable::createFromFormat('!' . $format, $date);

            if ($parsed instanceof \DateTimeImmutable && $parsed->format($format) === $date) {
                return $parsed->format('Y-m-d');
            }
        }

        // Preserve an unknown value so Joomla/DB validation can report it
        // instead of silently turning an invalid date into another value.
        return $date;
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
