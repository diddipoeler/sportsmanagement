<?php
/**
 * Regression checks for frontend project title values and module database guards.
 *
 * Run from repository root:
 * php .github/tests/joomla56-project-title-module-db-guards.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

use Diddipoeler\Component\SportsManagement\Site\Helper\ProjectTitleHelper;

define('_JEXEC', 1);

$root = dirname(__DIR__, 2);
require_once $root . '/site/src/Helper/ProjectTitleHelper.php';

$info = ProjectTitleHelper::createInfo('Results');
$info->clubName = ['unexpected', 'legacy', 'data'];
$info->roundName = new stdClass();
$info->projectName = 'League A';
$info->divisionName = 'Group 1';

set_error_handler(
    static function (int $severity, string $message): never {
        throw new ErrorException($message, 0, $severity);
    }
);

try {
    $title = ProjectTitleHelper::format($info, 0);

    if ($title !== 'Results: League A - Group 1') {
        throw new RuntimeException('Malformed club and round names must not enter project titles: ' . $title);
    }

    $info->clubName = 'Sports Club';
    $info->roundName = 'Final';
    $title = ProjectTitleHelper::format($info, 0);

    if ($title !== 'Results: Sports Club | Final | League A - Group 1') {
        throw new RuntimeException('Valid club and round names must remain visible: ' . $title);
    }
} finally {
    restore_error_handler();
}

foreach ([
    'mod_sportsmanagement_liveticker',
    'mod_sportsmanagement_count_rekord',
] as $module) {
    $file = $root . '/modules/' . $module . '/src/Dispatcher/Dispatcher.php';
    $contents = file_get_contents($file);

    if (!is_string($contents)
        || !str_contains($contents, 'catch (\\Throwable $exception)')
        || !str_contains($contents, '500, $exception')
        || !str_contains($contents, 'DatabaseInterface::class')) {
        throw new RuntimeException('Missing guarded Joomla database service lookup: ' . $module);
    }
}

echo "Joomla 5/6 project title and module database guard regressions passed.\n";
