<?php
/**
 * Joomla 5/6 helper for SportsManagement person age calculation.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

/** Calculate a person's age without loading the legacy component helper. */
final class PersonAgeHelper
{
    /**
     * Calculate completed years between a birthday and a reference date.
     *
     * Parses ISO dates without allowing PHP's automatic rollover of invalid
     * days. When no reference date is supplied, the current local day is
     * used. Explicitly invalid reference dates and future birthdays produce
     * no age rather than silently falling back to today's date.
     *
     * @param string $birthday Person's ISO birthday, optionally followed by time.
     * @param string|null $referenceDate Optional comparison date in ISO format.
     * @return int|null Completed years, or null for invalid/future dates.
     */
    public function calculate(string $birthday, ?string $referenceDate = null): ?int
    {
        $birth = $this->parseDate($birthday);

        if (!$birth) {
            return null;
        }

        $reference = $referenceDate === null
            ? new \DateTimeImmutable('today')
            : $this->parseDate($referenceDate);

        if ($reference === null) {
            return null;
        }

        if ($reference < $birth) {
            return null;
        }

        return $birth->diff($reference)->y;
    }

    /**
     * Parse a calendar date from a database ISO date or timestamp.
     *
     * Rejects zero dates, empty values, invalid leap days, malformed time
     * suffixes and dates which PHP would otherwise normalize by rollover.
     * The database ISO contract is strict: leading or trailing whitespace
     * must not silently turn malformed stored values into valid birthdays.
     *
     * @param string $value Date in YYYY-MM-DD or YYYY-MM-DD HH:MM:SS format.
     * @return \DateTimeImmutable|null Calendar day at midnight, or null on failure.
     */
    private function parseDate(string $value): ?\DateTimeImmutable
    {
        if ($value === '' || trim($value) !== $value || str_starts_with($value, '0000-00-00')) {
            return null;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}(?: \d{2}:\d{2}:\d{2})?$/D', $value)) {
            return null;
        }

        // A date-time suffix is accepted for database compatibility, but its
        // clock values must be valid rather than silently ignored.
        if (strlen($value) > 10) {
            $hours = (int) substr($value, 11, 2);
            $minutes = (int) substr($value, 14, 2);
            $seconds = (int) substr($value, 17, 2);

            if ($hours > 23 || $minutes > 59 || $seconds > 59) {
                return null;
            }
        }

        $date = substr($value, 0, 10);
        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $errors = \DateTimeImmutable::getLastErrors();

        if (!$parsed || (is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            return null;
        }

        return $parsed->format('Y-m-d') === $date ? $parsed : null;
    }
}
