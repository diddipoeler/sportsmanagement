<?php
/**
 * Joomla 5/6 regression tests for editmatch redirects and calendar instance/date validation.
 *
 * Usage: php .github/tests/joomla56_editmatch_calendar_navigation.php
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Controller {
    class FormController {}
}

namespace Joomla\CMS\Uri {
    final class Uri
    {
        public static function getInstance(?string $uri = null): object
        {
            return new class {
                public function getHost(): string { return 'sports.example'; }
            };
        }
    }
}

namespace Joomla\CMS\Helper {
    final class ModuleHelper
    {
        public static ?string $lastId = null;
        public static ?object $module = null;

        public static function getModuleById(string $id): ?object
        {
            self::$lastId = $id;

            return self::$module;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Controller/EditmatchController.php';
    require_once $root . '/modules/mod_sportsmanagement_calendar/src/Helper/CalendarHelper.php';

    $controller = new \Diddipoeler\Component\SportsManagement\Site\Controller\EditmatchController();
    $returnUrl = new \ReflectionMethod($controller, 'returnUrl');
    $fallback = 'index.php?option=com_sportsmanagement&view=editmatch';
    $cases = [
        ['index.php?option=com_sportsmanagement&view=editmatch', 'index.php?option=com_sportsmanagement&view=editmatch'],
        ['/matches/edit?match=4', '/matches/edit?match=4'],
        ['?task=editmatch.display', '?task=editmatch.display'],
        ['https://sports.example/matches', 'https://sports.example/matches'],
        ['http://sports.example/results', 'http://sports.example/results'],
        ['https://evil.example/', $fallback],
        ['//evil.example/path', $fallback],
        ['//sports.example/path', $fallback],
        ['javascript:alert(1)', $fallback],
        ['data:text/html,unsafe', $fallback],
        ['ftp://sports.example/results', $fallback],
        ['https://user@sports.example/results', $fallback],
        ['http://user:secret@sports.example/', $fallback],
        ["https://sports.example/\r\nX-Fake: 1", $fallback],
        ['\evil.example\results', $fallback],
        ['', $fallback],
    ];

    foreach ($cases as [$referer, $expected]) {
        $_SERVER['HTTP_REFERER'] = $referer;
        $actual = $returnUrl->invoke($controller);
        if ($actual !== $expected) {
            throw new \RuntimeException('Unsafe or broken editmatch return URL: ' . json_encode($referer)
                . ', received ' . json_encode($actual));
        }
    }

    unset($_SERVER['HTTP_REFERER']);
    if ($returnUrl->invoke($controller) !== $fallback) {
        throw new \RuntimeException('Missing referrer must return to editmatch.');
    }

    $helper = new \Diddipoeler\Module\SportsManagementCalendar\Site\Helper\CalendarHelper();
    $module = \Joomla\CMS\Helper\ModuleHelper::class;
    $requestedModule = new \ReflectionMethod($helper, 'requestedModule');

    $module::$module = (object) ['id' => '19', 'module' => 'mod_sportsmanagement_calendar'];
    if ($requestedModule->invoke($helper, 19) !== $module::$module || $module::$lastId !== '19') {
        throw new \RuntimeException('Calendar AJAX module did not resolve with string module ID.');
    }

    $module::$module = (object) ['id' => '19', 'module' => 'mod_sportsmanagement_liveticker'];
    if ($requestedModule->invoke($helper, 19) !== null) {
        throw new \RuntimeException('Calendar module accepted another module type.');
    }

    $module::$module = (object) ['id' => '20', 'module' => 'mod_sportsmanagement_calendar'];
    if ($requestedModule->invoke($helper, 19) !== null) {
        throw new \RuntimeException('Calendar module accepted another module ID.');
    }

    $module::$lastId = null;
    if ($requestedModule->invoke($helper, 0) !== null || $module::$lastId !== null) {
        throw new \RuntimeException('Calendar module queried an invalid ID.');
    }

    $normalise = new \ReflectionMethod($helper, 'normaliseDate');
    foreach ([
        ['2024-02-29', '2024-02-29'],
        [' 2026-10-11 ', '2026-10-11'],
        ['2026-02-30', null],
        ['2025-02-29', null],
        ['2026-13-01', null],
        ['2026-00-15', null],
        ['2026-12-32', null],
        ['2026-1-01', null],
        ['2026-01-01 12:00', null],
        ['2026/10/11', null],
        ['', null],
    ] as [$value, $expected]) {
        if ($normalise->invoke($helper, $value) !== $expected) {
            throw new \RuntimeException('Calendar normalized invalid date: ' . json_encode($value));
        }
    }

    $helperSource = file_get_contents($root . '/modules/mod_sportsmanagement_calendar/src/Helper/CalendarHelper.php');
    if (!str_contains($helperSource, 'if (!checkdate($month, $day, $year))')
        || !str_contains($helperSource, 'if ($start > $end)')) {
        throw new \RuntimeException('Calendar day or reversed date range guard is missing.');
    }

    echo "Joomla 5/6 editmatch redirects, calendar module IDs and dates OK\n";
}
