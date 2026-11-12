<?php
/**
 * Verify Joomla 5/6 project navigation, administrator form paths and module database selection.
 *
 * Usage: php .github/tests/joomla56_project_navigation_extended_forms_playground_db.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

define('_JEXEC', 1);

$root = dirname(__DIR__, 2);

/**
 * Assert that an expected regression condition is satisfied.
 *
 * @param bool $condition Whether the investigated guard behaves correctly.
 * @param string $message Diagnostic to report if the condition fails.
 * @return void
 * @throws RuntimeException When the regression assertion does not hold.
 */
function smAssertMigrationGuard(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

require_once $root . '/admin/src/Helper/ExtendedFormHelper.php';
require_once $root . '/modules/mod_sportsmanagement_playgroundplan/src/Helper/PlaygroundPlanHelper.php';

$adminHelper = new \Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtendedFormHelper();
$moduleHelper = \Diddipoeler\Module\SportsManagementPlaygroundPlan\Site\Helper\PlaygroundPlanHelper::class;
$selector = new ReflectionMethod($moduleHelper, 'databaseSelector');

foreach ([null, true, false, -1, 2, 1.0, '1junk', ' 1', '2', [], str_repeat('0', 65)] as $invalid) {
    smAssertMigrationGuard(
        $selector->invoke(null, $invalid) === null,
        'PlaygroundPlan accepted an invalid database selector.'
    );
}

foreach ([0 => 0, 1 => 1, '0' => 0, '1' => 1, '0001' => 1, '000' => 0] as $raw => $expected) {
    smAssertMigrationGuard(
        $selector->invoke(null, $raw) === $expected,
        'PlaygroundPlan rejected a valid database selector.'
    );
}

foreach ([' ../team', '../team', 'team ', 'team/other', 'team.xml'] as $invalidView) {
    smAssertMigrationGuard(
        $adminHelper->load('extended', $invalidView) === null,
        'Administrator extended form accepted an invalid view name.'
    );
}

$base = sys_get_temp_dir() . '/sm-admin-xml-path-' . bin2hex(random_bytes(8));
$assets = $base . '/components/com_sportsmanagement/assets';
$directory = $assets . '/extended';
$outside = $base . '/outside.xml';
$symlink = $directory . '/escaped.xml';

mkdir($directory, 0777, true);
file_put_contents($outside, '<form/>');
define('JPATH_ADMINISTRATOR', $base);

try {
    if (function_exists('symlink') && @symlink($outside, $symlink)) {
        smAssertMigrationGuard(
            $adminHelper->load('extended', 'escaped') === null,
            'Administrator extended form followed a symlink outside the XML directory.'
        );
    }
} finally {
    if (is_link($symlink)) {
        unlink($symlink);
    }

    unlink($outside);
    rmdir($directory);
    rmdir($assets);
    rmdir(dirname($assets));
    rmdir(dirname(dirname($assets)));
    rmdir($base);
}

$siteNavigation = file_get_contents($root . '/site/src/Helper/ProjectNavigationHelper.php');
$moduleSource = file_get_contents(
    $root . '/modules/mod_sportsmanagement_playgroundplan/src/Helper/PlaygroundPlanHelper.php'
);

smAssertMigrationGuard(
    is_string($siteNavigation)
        && str_contains($siteNavigation, ':navigationEqualName')
        && str_contains($siteNavigation, ':navigationProjectId')
        && str_contains($siteNavigation, "->order(")
        && str_contains($siteNavigation, "'p.id'"),
    'Project navigation no longer handles duplicate names deterministically.'
);

smAssertMigrationGuard(
    is_string($moduleSource)
        && str_contains($moduleSource, 'self::databaseSelector($params->get(')
        && !str_contains($moduleSource, '$exception->getMessage()'),
    'PlaygroundPlan database selector or logging privacy regressed.'
);

echo "Joomla 5/6 project navigation, administrator form paths and module selectors: OK\n";
