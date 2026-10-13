<?php
/**
 * Cross-area regression checks for Joomla 5/6 date inputs and birthday limits.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

defined('_JEXEC') || define('_JEXEC', 1);

$root = dirname(__DIR__, 2);
require_once $root . '/site/src/Helper/SportsManagementDateHelper.php';
require_once $root . '/admin/helpers/sportsmanagement.php';
require_once $root . '/modules/mod_sportsmanagement_birthday/src/Helper/BirthdayHelper.php';

$cases = [
    ['31122011', 1, '2011-12-31'],
    ['311211', 1, substr(date('Y'), 0, 2) . '11-12-31'],
    ['2011-12-31', 1, '31-12-2011'],
    ['31-12-2011', 2, '2011-12-31'],
    ['2024-02-29 13:05:09', 1, '29-02-2024'],
    ['2024-02-29T23:59:59', 1, '29-02-2024'],
    ['2025-02-29', 1, ''],
    ['31-02-2024', 2, ''],
    ['0000-00-00', 1, ''],
    ['2024-02-29 24:00:00', 1, ''],
    ['2024-02-29 12:60:00', 1, ''],
    ['2024-02-29 12:00:60', 1, ''],
    ['2024-03-01 trailing', 1, ''],
    ['32122011', 1, ''],
    ['31x22011', 1, ''],
];

foreach ($cases as [$input, $direction, $expected]) {
    $site = \Diddipoeler\Component\SportsManagement\Site\Helper\SportsManagementDateHelper::convertDate(
        $input,
        $direction
    );
    $admin = sportsmanagementHelper::convertDate($input, $direction);

    if ($site !== $expected || $admin !== $expected) {
        throw new RuntimeException(sprintf(
            'Date conversion mismatch for %s (%d): site=%s admin=%s expected=%s',
            $input,
            $direction,
            $site,
            $admin,
            $expected
        ));
    }
}

if (sportsmanagementHelper::convertDate(null) !== '') {
    throw new RuntimeException('Legacy administrator null date must remain empty.');
}

$birthday = new \Diddipoeler\Module\SportsManagementBirthday\Site\Helper\BirthdayHelper();
$normalise = new ReflectionMethod($birthday, 'normaliseDaysLimit');

foreach ([
    ['', null], [' ', null], ['0', 0], ['000', 0],
    ['7', 7], ['007', 7], [' 30 ', 30],
    ['-1', null], ['3days', null], ['1.5', null],
    ['9999999999999999999999999999999', null], [[], null], [null, null],
] as [$input, $expected]) {
    $actual = $normalise->invoke($birthday, $input);

    if ($actual !== $expected) {
        throw new RuntimeException('Birthday maxdays filter incorrectly parsed ' . var_export($input, true));
    }
}

echo "Joomla 5/6 site/admin date conversions and birthday limits OK\n";
