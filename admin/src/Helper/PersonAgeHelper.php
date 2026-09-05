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
    public function calculate(string $birthday, ?string $referenceDate = null): ?int
    {
        $birth = $this->parseDate($birthday);

        if (!$birth) {
            return null;
        }

        $reference = $this->parseDate((string) $referenceDate) ?? new \DateTimeImmutable('today');

        if ($reference < $birth) {
            return null;
        }

        return $birth->diff($reference)->y;
    }

    private function parseDate(string $value): ?\DateTimeImmutable
    {
        $value = trim($value);

        if ($value === '' || str_starts_with($value, '0000-00-00')) {
            return null;
        }

        $date = substr($value, 0, 10);
        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $errors = \DateTimeImmutable::getLastErrors();

        if (!$parsed || (is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            return null;
        }

        return $parsed;
    }
}
