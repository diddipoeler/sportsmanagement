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
require_once dirname(__DIR__, 2) . '/site/src/Helper/NamePresentationHelper.php';
require_once dirname(__DIR__, 2) . '/site/src/Helper/SportsManagementDateHelper.php';
require_once dirname(__DIR__, 2) . '/admin/src/Helper/NameFormatter.php';
require_once dirname(__DIR__, 2) . '/admin/src/Helper/SportsManagementDateHelper.php';

use Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter;
use Diddipoeler\Component\SportsManagement\Site\Helper\NamePresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SportsManagementDateHelper as SiteDateHelper;
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
smAssertSame(SportsManagementDateHelper::convertDate('2026-11-10 15:30:00'), '10-11-2026', 'datetime to display date');
smAssertSame(SportsManagementDateHelper::convertDate('2026-11-10 25:30:00'), '', 'admin invalid SQL time');
smAssertSame(SportsManagementDateHelper::convertDate('2025-02-29'), '', 'admin invalid SQL calendar date');
smAssertSame(SportsManagementDateHelper::convertDate('29-02-2025', 0), '', 'admin invalid display calendar date');
smAssertSame(SportsManagementDateHelper::convertDate('29-02-2024', 0), '2024-02-29', 'admin valid display leap date');
smAssertSame(SportsManagementDateHelper::convertDate('2026-11-10garbage'), '', 'admin reject SQL suffix');
smAssertSame(SportsManagementDateHelper::convertDate('29022024'), '2024-02-29', 'compact leap day');
smAssertSame(SportsManagementDateHelper::convertDate('29022025'), '', 'invalid compact leap day');
smAssertSame(SportsManagementDateHelper::convertDate('abcdefgh'), '', 'non-digit compact date');
smAssertSame(SportsManagementDateHelper::convertDate('310299'), '', 'invalid compact short date');
smAssertSame(SiteDateHelper::convertDate('2026-11-10 15:30:00'), '10-11-2026', 'site datetime conversion');
smAssertSame(SiteDateHelper::convertDate('2026-11-10 25:30:00'), '', 'site invalid datetime');
smAssertSame(NamePresentationHelper::person((object) ['firstname' => 'Ömer', 'lastname' => 'Müller'], 8), 'Ö. Müller', 'site Unicode initials without mbstring');
smAssertSame(NamePresentationHelper::person((object) ['firstname' => ['broken'], 'nickname' => new stdClass(), 'lastname' => 'Müller'], 8), 'Müller', 'ignore malformed person name fields');
smAssertSame(NamePresentationHelper::person((object) ['firstname' => 'Anna', 'nickname' => ['broken'], 'lastname' => 'Meier'], 0), 'Anna Meier', 'ignore invalid nickname');
smAssertSame(NamePresentationHelper::person((object) ['firstname' => 'Anna', 'nickname' => '', 'lastname' => '<Meier>'], 3), 'Anna &lt;Meier&gt;', 'escape person name HTML');
smAssertSame(NamePresentationHelper::team((object) ['short_name' => ['invalid'], 'name' => 'Real Club'], 0), 'Real Club', 'site team fallback for invalid name fields');

// Imported format selectors must not cast "8junk" to style 8, or allow
// overflowing numeric strings to choose an unintended layout.
$personRecord = (object) ['firstname' => 'Anna', 'lastname' => 'Meier'];
foreach (['8junk', '-1', '999999999999999999999999', '19'] as $badFormat) {
    smAssertSame(
        NamePresentationHelper::person($personRecord, $badFormat),
        'Anna Meier',
        'reject malformed person style ' . $badFormat
    );
}
smAssertSame(
    NamePresentationHelper::person($personRecord, '0008'),
    'A. Meier',
    'allow leading-zero person format selector'
);

$teamRecord = (object) ['name' => 'Full Team', 'short_name' => 'FT', 'middle_name' => 'Middle Team'];
foreach (['0junk', '-1', '999999999999999999999999', '3'] as $badFormat) {
    smAssertSame(
        NamePresentationHelper::team($teamRecord, $badFormat),
        'Full Team',
        'reject malformed team style ' . $badFormat
    );
}
smAssertSame(
    NamePresentationHelper::team($teamRecord, '0000'),
    'FT',
    'allow leading-zero team format selector'
);
smAssertSame(
    NamePresentationHelper::team((object) ['name' => '', 'short_name' => '', 'middle_name' => 'Middle Team'], 0),
    'Middle Team',
    'fallback to available middle name in short-name style'
);
smAssertSame(
    NamePresentationHelper::team((object) ['name' => '', 'short_name' => 'FT', 'middle_name' => ''], 1),
    'FT',
    'fallback to available short name in middle-name style'
);

echo "Joomla 5/6 formatting regressions passed.\n";
