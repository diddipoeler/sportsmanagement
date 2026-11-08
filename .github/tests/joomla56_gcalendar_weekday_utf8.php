<?php
/**
 * Exercise Joomla 5/6 GCalendar weekday formatting with Unicode locales.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);
require_once dirname(__DIR__, 2)
    . '/modules/mod_sportsmanagement_gcalendar/src/Helper/GcalendarHelper.php';

use Diddipoeler\Module\SportsManagementGcalendar\Site\Helper\GcalendarHelper;

$examples = [
    'Monday' => 'Mo',
    'Tuesday' => 'Tu',
    'Ärger' => 'Är',
    'Über' => 'Üb',
    'Été' => 'Ét',
    '日本曜日' => '日本',
    '星期五' => '星期',
    '月' => '月',
    '' => '',
];

foreach ($examples as $name => $expected) {
    $actual = GcalendarHelper::shortWeekdayLabel($name);

    if ($actual !== $expected
        || ($actual !== '' && preg_match('//u', $actual) !== 1)) {
        throw new RuntimeException(
            'Broken UTF-8 weekday abbreviation: ' . var_export([$name, $actual], true)
        );
    }
}

echo "Joomla 5/6 GCalendar Unicode weekday abbreviations passed.\n";
