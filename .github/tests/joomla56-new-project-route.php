<?php
/**
 * Regression: New Project links preserve configured external database IDs.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Site\Helper {
    final class SiteRouteHelper
    {
        /**
         * Return the supplied route parameters for reflection-based tests.
         *
         * @param string $view Requested component view.
         * @param array<string, mixed> $parameters Route parameters.
         * @return string Encoded view and configuration for assertions.
         */
        public static function view(string $view, array $parameters = []): string
        {
            return json_encode([$view, $parameters], JSON_THROW_ON_ERROR);
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_new_project/src/Helper/NewProjectHelper.php';

    $class = \Diddipoeler\Module\SportsManagementNewProject\Site\Helper\NewProjectHelper::class;
    $helper = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
    $route = new \ReflectionMethod($class, 'resultsUrl');
    $project = (object) ['project_slug' => '7:test', 'round_slug' => '2:round'];

    foreach ([0, 1, 2, 9] as $selector) {
        $result = json_decode($route->invoke($helper, $project, $selector), true, 512, JSON_THROW_ON_ERROR);

        if (($result[0] ?? null) !== 'resultsranking'
            || ($result[1]['cfg_which_database'] ?? null) !== $selector
            || ($result[1]['p'] ?? null) !== '7:test'
            || ($result[1]['r'] ?? null) !== '2:round') {
            fwrite(STDERR, 'New Project database route selector regression.' . PHP_EOL);
            exit(1);
        }
    }

    $moduleId = new \ReflectionMethod($class, 'contentCategoryId');
    foreach ([
        [7, 7], ['0007', 7], ['7garbage', 0], [['7'], 0],
        [true, 0], [-1, 0], ['0', 0], [PHP_INT_MAX . '0', 0],
    ] as [$input, $expected]) {
        if ($moduleId->invoke($helper, $input) !== $expected) {
            fwrite(STDERR, 'New Project AJAX module identifier regression.' . PHP_EOL);
            exit(1);
        }
    }

    $validProject = new \ReflectionMethod($class, 'validArticleProject');
    foreach ([
        [(object) ['id' => 7, 'name' => 'Test project'], true],
        [(object) ['id' => '0007', 'name' => 'Test'], true],
        [(object) ['id' => '7invalid', 'name' => 'Test'], false],
        [(object) ['id' => 7, 'name' => '   '], false],
        [(object) ['id' => 7, 'name' => ['invalid']], false],
        [(object) ['id' => 0, 'name' => 'Test'], false],
        [['id' => 7, 'name' => 'Test'], false],
        [null, false],
    ] as [$project, $expected]) {
        if ($validProject->invoke($helper, $project) !== $expected) {
            fwrite(STDERR, 'New Project article record validation regression.' . PHP_EOL);
            exit(1);
        }
    }

    $matches = new \\ReflectionMethod($class, 'matchedProjectReferences');
    $found = $matches->invoke(null, [
        '12',
        'sportsmanagement-project:3',
        'unrelated-project:12',
        'sportsmanagement-project:12tail',
        ['12'],
        '999999999999999999999999999999',
        '4',
    ], [3 => 3, 12 => 12]);

    if ($found !== [12 => true, 3 => true]) {
        fwrite(STDERR, 'New Project exact article references regression.' . PHP_EOL);
        exit(1);
    }

    echo "New Project database route selectors passed.\n";
}
