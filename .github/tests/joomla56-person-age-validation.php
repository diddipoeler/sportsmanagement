<?php
/**
 * Regression coverage for Joomla 5/6 person-age validation.
 *
 * Exercises the native administrator helper without a Joomla bootstrap.
 *
 * Usage: php .github/tests/joomla56-person-age-validation.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

defined('_JEXEC') || define('_JEXEC', 1);

require_once dirname(__DIR__, 2) . '/admin/src/Helper/PersonAgeHelper.php';

use Diddipoeler\Component\SportsManagement\Administrator\Helper\PersonAgeHelper;

/**
 * Assert one date-to-age contract without relying on a test framework.
 *
 * @param PersonAgeHelper $helper Native administrator age helper.
 * @param string $birthday ISO birth date supplied for testing.
 * @param string $referenceDate Explicit ISO comparison date.
 * @param int|null $expected Expected completed age, or null for invalid data.
 * @return void
 * @throws RuntimeException When the age helper breaks the expected contract.
 */
function smCheckAge(PersonAgeHelper $helper, string $birthday, string $referenceDate, ?int $expected): void
{
    $actual = $helper->calculate($birthday, $referenceDate);

    if ($actual !== $expected) {
        throw new RuntimeException(
            sprintf('Age mismatch for %s / %s: expected %s, got %s',
                $birthday, $referenceDate, var_export($expected, true), var_export($actual, true))
        );
    }
}

$helper = new PersonAgeHelper();
smCheckAge($helper, '2000-11-09', '2026-11-08', 25);
smCheckAge($helper, '2000-11-08', '2026-11-08', 26);
smCheckAge($helper, '2000-02-29', '2024-02-29', 24);
smCheckAge($helper, '2027-01-01', '2026-11-08', null);
smCheckAge($helper, '2025-02-29', '2026-11-08', null);
smCheckAge($helper, '2000-01-01', '2026-02-30', null);
smCheckAge($helper, '2000-01-01', 'not-a-date', null);
smCheckAge($helper, '0000-00-00', '2026-11-08', null);
smCheckAge($helper, '2000-01-01 12:30:00', '2026-11-08', 26);
smCheckAge($helper, '2000-01-01bogus', '2026-11-08', null);
smCheckAge($helper, '2000-01-01 25:00:00', '2026-11-08', null);
smCheckAge($helper, '2000-01-01 12:61:00', '2026-11-08', null);
smCheckAge($helper, '2000-01-01 12:30:65', '2026-11-08', null);
smCheckAge($helper, '2000-01-01', '2026-11-08 24:00:00', null);
smCheckAge($helper, '2000-01-01', '2026-11-08 23:59:59', 26);

echo "Joomla 5/6 person-age validation passed.\n";
