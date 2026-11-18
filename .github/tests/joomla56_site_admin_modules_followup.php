<?php
/**
 * Regression checks for Joomla 5/6 site, administrator and module inputs.
 *
 * Includes a minimal form-field parent so the native country filter can be
 * checked without installing Joomla or accessing a production database.
 *
 * Usage: php .github/tests/joomla56_site_admin_modules_followup.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Administrator\Field {
    // Only the league field's isolated country filter is invoked.
    class SportsManagementListField
    {
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Helper/SportsManagementDateHelper.php';
    require_once dirname(__DIR__, 2) . '/admin/src/Field/LeaguelistField.php';
    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_trainingsdata/src/Helper/TrainingsDataHelper.php';

    $assertions = 0;

    // Warnings from malformed PHP 8 inputs should fail the regression.
    set_error_handler(static function (int $level, string $message): never {
        throw new \ErrorException($message, 0, $level);
    });

    $dateCases = [
        ['2026-02-28 20:45:00', '28-02-2026', 1],
        ['10112026', '2026-11-10', 1],
        ['31-02-2026', '', 2],
        ['2026-02-30', '', 1],
        ["2026-02-28\x00", '', 1],
        [' 2026-02-28', '', 1],
        [str_repeat('1', 200000), '', 1],
    ];

    foreach ($dateCases as [$value, $expected, $direction]) {
        $actual = \Diddipoeler\Component\SportsManagement\Site\Helper\SportsManagementDateHelper::convertDate(
            $value,
            $direction
        );
        if ($actual !== $expected) {
            throw new \RuntimeException('Site date validation failed for a boundary input.');
        }
        ++$assertions;
    }

    $countryFilter = new \ReflectionMethod(
        \Diddipoeler\Component\SportsManagement\Administrator\Field\LeaguelistField::class,
        'postedCountry'
    );

    $countryCases = [
        [['search_nation' => 'GER'], 'GER'],
        [['search_nation' => ' FRA '], 'FRA'],
        [['search_nation' => []], ''],
        [['search_nation' => new \stdClass()], ''],
        [['search_nation' => true], ''],
        [['search_nation' => "DE\x00"], ''],
        [['search_nation' => str_repeat('A', 1000)], ''],
        ['DE', ''],
        [null, ''],
    ];

    foreach ($countryCases as [$value, $expected]) {
        if ($countryFilter->invoke(null, $value) !== $expected) {
            throw new \RuntimeException('Administrator league country filter accepted invalid input.');
        }
        ++$assertions;
    }

    $teamSelector = new \ReflectionMethod(
        \Diddipoeler\Module\SportsManagementTrainingsData\Site\Helper\TrainingsDataHelper::class,
        'positiveId'
    );

    $teamCases = [
        ['007:team-alias', 7],
        [7, 7],
        [' 7', 0],
        ['7 ', 0],
        ["7\x00", 0],
        ["7\n", 0],
        [str_repeat('7', 300), 0],
        [[], 0],
        [true, 0],
        ['0', 0],
    ];

    foreach ($teamCases as [$value, $expected]) {
        if ($teamSelector->invoke(null, $value) !== $expected) {
            throw new \RuntimeException('TrainingsData module accepted an invalid team selector.');
        }
        ++$assertions;
    }

    restore_error_handler();
    echo "Passed {$assertions} Joomla 5/6 site/admin/module input assertions.\n";
}
