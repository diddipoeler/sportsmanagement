<?php
/**
 * Regression tests for project navigation, administrator initials and matches DI.
 *
 * Run from the repository root:
 * php .github/tests/joomla56-project-navigation-name-matches.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Database {
    /**
     * Minimal type contract for standalone input validation tests.
     */
    interface DatabaseInterface
    {
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Site\Helper\ProjectNavigationHelper;
    use Diddipoeler\Component\SportsManagement\Administrator\Helper\NameFormatter;

    \define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);

    require_once $root . '/site/src/Helper/ProjectNavigationHelper.php';
    require_once $root . '/admin/src/Helper/NameFormatter.php';

    $db = new class implements \Joomla\Database\DatabaseInterface {
    };

    $project = (object) [
        'id' => 1,
        'league_id' => 4,
        'name' => "broken\xC3\x28",
    ];

    // Invalid names must return before any database method gets called.
    if (ProjectNavigationHelper::previous($db, $project) !== null
        || ProjectNavigationHelper::next($db, $project) !== null) {
        throw new \RuntimeException('Project navigation accepted invalid UTF-8.');
    }

    // Invalid controls must return before the first query method is called,
    // including controls that trim() would otherwise remove.
    foreach (["Project\n", "Project\0", "\tProject"] as $invalidProjectName) {
        $project->name = $invalidProjectName;

        if (ProjectNavigationHelper::previous($db, $project) !== null
            || ProjectNavigationHelper::next($db, $project) !== null) {
            throw new \RuntimeException('Project navigation accepted raw control bytes.');
        }
    }

    $invalidFirstName = "broken\xC3\x28";

    if (NameFormatter::format(null, $invalidFirstName, '', 'Doe', 8) !== 'Doe') {
        throw new \RuntimeException('Invalid UTF-8 first name created a broken initial.');
    }

    if (NameFormatter::format(null, 'Ada', '', 'Lovelace', 8) !== 'A. Lovelace') {
        throw new \RuntimeException('Valid administrator initials were changed.');
    }

    $dispatcher = file_get_contents(
        $root . '/modules/mod_sportsmanagement_matches/src/Dispatcher/Dispatcher.php'
    );

    if (!is_string($dispatcher)
        || !str_contains($dispatcher, 'catch (\\Throwable $exception)')
        || !str_contains($dispatcher, '500, $exception')
        || !str_contains($dispatcher, 'DatabaseInterface::class')) {
        throw new \RuntimeException('Matches module database resolution lost exception wrapping.');
    }

    echo "Joomla 5/6 navigation, initials and matches DI regressions passed.\n";
}
