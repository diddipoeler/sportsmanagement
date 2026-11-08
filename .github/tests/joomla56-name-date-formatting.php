<?php
/**
 * Regression checks for Joomla 5/6 name-format and date helpers.
 *
 * Runs without a Joomla bootstrap so that formatting contracts can be
 * validated on supported PHP runtimes before extension packaging.
 *
 * Usage: php .github/tests/joomla56-name-date-formatting.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

defined('_JEXEC') || define('_JEXEC', 1);

require_once dirname(__DIR__, 2) . '/site/src/Helper/PersonNameFormatter.php';
require_once dirname(__DIR__, 2) . '/admin/src/Helper/NameFormatter.php';
require_once dirname(__DIR__, 2) . '/admin/src/Helper/SportsManagementDateHelper.php';

use Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\NameFormatter;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDateHelper;

/**
 * Assert an exact formatting result with a useful test failure message.
 *
 * @param mixed $actual Value produced by the code under test.
 * @param mixed $expected Contractually expected value.
 * @param string $label Human-readable assertion name.
 * @return void
 * @throws RuntimeException When the formatting output differs.
 */
function smAssertSame(mixed $actual, mixed $expected, string $label): void
{
    if ($actual !== $expected) {
        throw new RuntimeException($label . ': expected ' . var_export($expected, true)
            . ', received ' . var_export($actual, true));
    }
}

foreach ([PersonNameFormatter::class, NameFormatter::class] as $formatter) {
    smAssertSame($formatter::format(null, 'Ömer', '', 'Müller', 8), 'Ö. Müller', $formatter . ' UTF-8 first initial');
    smAssertSame($formatter::format(null, 'Ömer', '', 'Müller', 18), 'M. Ö.', $formatter . ' both initials');
    smAssertSame($formatter::format('Dr.', 'Anna', '', 'Meier', 3), 'Dr. Anna Meier', $formatter . ' prefix');
    smAssertSame($formatter::format(null, 'Anna', '', 'Meier', 2), 'Meier, Anna', $formatter . ' surname first');
}

smAssertSame(SportsManagementDateHelper::toSqlDate('29.02.2024'), '2024-02-29', 'valid leap day');
smAssertSame(SportsManagementDateHelper::toSqlDate('29.02.2025'), '29.02.2025', 'reject rollover');
smAssertSame(SportsManagementDateHelper::toSqlDate('0000-00-00'), null, 'zero date');
smAssertSame(SportsManagementDateHelper::toSqlDate(''), null, 'empty date');
smAssertSame(SportsManagementDateHelper::convertDate('2025-11-08'), '08-11-2025', 'legacy date conversion');

echo "Joomla 5/6 formatting regressions passed.\n";
