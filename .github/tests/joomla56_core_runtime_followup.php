<?php
/**
 * Joomla 5/6 follow-up contracts for site ranking, admin models and calendar modules.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$files = [
    'site/src/Model/RankingModel.php',
    'admin/helpers/sportsmanagement.php',
    'modules/mod_sportsmanagement_google_calendar/src/Helper/GoogleCalendarHelper.php',
    'modules/mod_sportsmanagement_google_calendar/src/Dispatcher/Dispatcher.php',
];

$sources = [];
foreach ($files as $path) {
    $source = file_get_contents($root . '/' . $path);
    if ($source === false || !preg_match('/^<\?php\s*\/\*\*(.*?)\*\//s', $source, $doc)) {
        throw new RuntimeException($path . ': missing file PHPDoc');
    }
    foreach (['version', 'author', 'copyright', 'license'] as $tag) {
        if (!preg_match('/^\s*\*\s*@' . $tag . '\s+\S+/m', $doc[1])) {
            throw new RuntimeException($path . ': missing @' . $tag);
        }
    }
    $sources[$path] = $source;
}

$ranking = $sources['site/src/Model/RankingModel.php'];
$start = strpos($ranking, 'public function getPreviousGames(');
$end = strpos($ranking, 'public function getRoundOptions(', $start === false ? 0 : $start);
if ($start === false || $end === false) {
    throw new RuntimeException('Ranking round/match model contract is missing.');
}
$matchQuery = substr($ranking, $start, $end - $start);
foreach (['jsm_team1_alias', 'jsm_team2_alias', 'jsm_project_alias', '$game->project_slug'] as $required) {
    if (!str_contains($matchQuery, $required)) {
        throw new RuntimeException('Ranking portable slug contract is missing: ' . $required);
    }
}
if (str_contains($matchQuery, 'CONCAT_WS(') || str_contains($matchQuery, 'CHAR_LENGTH(')) {
    throw new RuntimeException('Ranking match slugs must not use MySQL-only functions.');
}

$administrator = $sources['admin/helpers/sportsmanagement.php'];
foreach (["->bootComponent('com_actionlogs')", '->getMVCFactory()->createModel('] as $required) {
    if (!str_contains($administrator, $required)) {
        throw new RuntimeException('Administrator Action Logs MVC factory migration is missing.');
    }
}
if (str_contains($administrator, '$this->setError($db->getErrorMsg())')
    || str_contains($administrator, 'ItemModel::getInstance(')
    || str_contains($administrator, 'BaseDatabaseModel::getInstance(')) {
    throw new RuntimeException('Removed administrator legacy model/error API found.');
}

$moduleHelper = $sources['modules/mod_sportsmanagement_google_calendar/src/Helper/GoogleCalendarHelper.php'];
$moduleDispatcher = $sources['modules/mod_sportsmanagement_google_calendar/src/Dispatcher/Dispatcher.php'];
if (!str_contains($moduleHelper, 'catch (\Throwable)')
    || str_contains($moduleDispatcher, '$exception->getMessage()')
    || !str_contains($moduleDispatcher, 'get_class($exception)')) {
    throw new RuntimeException('Google Calendar errors must not reveal remote response details.');
}

// Pure date formatting can be exercised without booting Joomla.
defined('_JEXEC') || define('_JEXEC', 1);
require_once $root . '/admin/helpers/sportsmanagement.php';
foreach ([
    ['31122011', 1, '2011-12-31'],
    ['2011-12-31', 1, '31-12-2011'],
    ['31-12-2011', 2, '2011-12-31'],
    [null, 1, ''],
] as [$input, $direction, $expected]) {
    $actual = sportsmanagementHelper::convertDate($input, $direction);
    if ($actual !== $expected) {
        throw new RuntimeException('Legacy administrator date conversion regression.');
    }
}

echo "Joomla 5/6 site, admin and module compatibility contracts passed.\n";
