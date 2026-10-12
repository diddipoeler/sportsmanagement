<?php
/**
 * Regression checks for February 29 birthdays across leap years.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

\defined('_JEXEC') || define('_JEXEC', 1);

require_once dirname(__DIR__, 2)
    . '/modules/mod_sportsmanagement_birthday/src/Helper/BirthdayHelper.php';

$helper = new \Diddipoeler\Module\SportsManagementBirthday\Site\Helper\BirthdayHelper();
$method = new ReflectionMethod($helper, 'nextBirthday');
$timezone = new DateTimeZone('UTC');

$cases = [
    ['2000-02-29', '2027-02-28', '2027-03-01'],
    ['2000-02-29', '2027-03-01', '2027-03-01'],
    ['2000-02-29', '2027-03-02', '2028-02-29'],
    ['2000-02-29', '2028-02-28', '2028-02-29'],
    ['2000-02-29', '2028-03-01', '2029-03-01'],
    ['2001-05-15', '2027-05-16', '2028-05-15'],
];

foreach ($cases as [$birth, $today, $expected]) {
    $result = $method->invoke(
        $helper,
        new DateTimeImmutable($birth, $timezone),
        new DateTimeImmutable($today, $timezone)
    );

    if (!$result instanceof DateTimeImmutable || $result->format('Y-m-d') !== $expected) {
        throw new RuntimeException(
            sprintf('Next birthday for %s on %s should be %s, got %s',
                $birth, $today, $expected, $result?->format('Y-m-d') ?? '(invalid)')
        );
    }
}

echo "Birthday leap-year cases OK\n";
