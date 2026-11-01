<?php
/**
 * Regression: Joomla 5/6 SEF router must not coerce arrays into "Array".
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Component\Router {
    class RouterBase
    {
        public object $menu;
        public object $app;
    }
}

namespace {
    define('_JEXEC', 1);

    require dirname(__DIR__, 2) . '/site/src/Service/SiteRouteSchema.php';
    require dirname(__DIR__, 2) . '/site/src/Service/Router.php';

    $class = \Diddipoeler\Component\SportsManagement\Site\Service\Router::class;
    $router = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
    $compare = new \ReflectionMethod($class, 'sameValue');

    $cases = [
        [['one'], ['two'], false],
        [['one'], 'Array', false],
        ['Array', ['one'], false],
        ['12', 12, true],
        ['', null, true],
        ['test', 'other', false],
    ];

    foreach ($cases as [$left, $right, $expected]) {
        $actual = $compare->invoke($router, $left, $right);

        if ($actual !== $expected) {
            fwrite(STDERR, 'SEF scalar comparison regression failed.' . PHP_EOL);
            exit(1);
        }
    }

    $menuItemLookup = new \ReflectionMethod($class, 'getMenuItemFromQuery');

    foreach ([[], ['Itemid' => ['123']], ['Itemid' => '123x'], ['Itemid' => '-1'], ['Itemid' => new \stdClass()]] as $query) {
        if ($menuItemLookup->invoke($router, $query) !== null) {
            fwrite(STDERR, 'Invalid SEF menu ID was accepted.' . PHP_EOL);
            exit(1);
        }
    }

    $isMenuItem = new \ReflectionMethod($class, 'isSportsManagementMenuItem');
    $menuWithArrayOption = (object) ['query' => ['option' => ['com_sportsmanagement']]];
    $validMenu = (object) ['query' => ['option' => 'com_sportsmanagement']];
    $menuWithArrayComponent = (object) ['component' => ['com_sportsmanagement']];

    if ($isMenuItem->invoke($router, $menuWithArrayOption) !== false
        || $isMenuItem->invoke($router, $validMenu) !== true
        || $isMenuItem->invoke($router, $menuWithArrayComponent) !== false
    ) {
        fwrite(STDERR, 'SEF menu component selector regression failed.' . PHP_EOL);
        exit(1);
    }

    $representsQuery = new \ReflectionMethod($class, 'menuItemRepresentsQuery');
    $invalidMenuView = (object) ['query' => ['view' => ['ranking']]];

    if ($representsQuery->invoke($router, $invalidMenuView, [], 'ranking', []) !== false) {
        fwrite(STDERR, 'Invalid SEF menu view was accepted.' . PHP_EOL);
        exit(1);
    }

    // A recognised route with malformed positional values must be rejected.
    $invalidSegments = ['ranking', ['bad']];
    try {
        $router->parse($invalidSegments);
        fwrite(STDERR, 'Invalid positional SEF segment was accepted.' . PHP_EOL);
        exit(1);
    } catch (\InvalidArgumentException $exception) {
        if ($exception->getCode() !== 400) {
            throw $exception;
        }
    }

    $validSegments = ['ranking', '0'];
    $parsed = $router->parse($validSegments);
    if (($parsed['view'] ?? null) !== 'ranking' || ($parsed['cfg_which_database'] ?? null) !== '0') {
        fwrite(STDERR, 'Valid positional SEF segment was rejected.' . PHP_EOL);
        exit(1);
    }

    // Invalid published metadata must not be treated as a published menu item.
    $router->menu = new class {
        public function getItems(string $key, mixed $value): array
        {
            return [
                (object) ['id' => 7, 'component' => 'com_sportsmanagement',
                    'published' => ['1'], 'query' => ['view' => 'ranking']],
                (object) ['id' => 8, 'component' => 'com_sportsmanagement',
                    'published' => 1, 'query' => ['view' => 'ranking']],
            ];
        }
    };
    $router->app = new \stdClass();
    $findMenu = new \ReflectionMethod($class, 'findBestMenuItem');
    $selected = $findMenu->invoke($router, [], 'ranking');
    if (($selected->id ?? null) !== 8) {
        fwrite(STDERR, 'Malformed publication metadata influenced SEF menu matching.' . PHP_EOL);
        exit(1);
    }

    // Authorised access filtering must reject non-scalar menu levels.
    $router->app = new class {
        public function getIdentity(): object
        {
            return new class {
                public function getAuthorisedViewLevels(): array
                {
                    return [1, 2];
                }
            };
        }
    };
    $router->menu = new class {
        public function getItems(string $key, mixed $value): array
        {
            return [
                (object) ['id' => 9, 'component' => 'com_sportsmanagement',
                    'published' => 1, 'access' => [1], 'query' => ['view' => 'ranking']],
                (object) ['id' => 10, 'component' => 'com_sportsmanagement',
                    'published' => 1, 'access' => 2, 'query' => ['view' => 'ranking']],
            ];
        }
    };
    $selected = $findMenu->invoke($router, [], 'ranking');
    if (($selected->id ?? null) !== 10) {
        fwrite(STDERR, 'Invalid menu access level influenced SEF matching.' . PHP_EOL);
        exit(1);
    }

    // A malformed menu ID must not gain the current-Itemid scoring bonus.
    $router->app = new \stdClass();
    $router->menu = new class {
        public function getItems(string $key, mixed $value): array
        {
            return [
                (object) ['id' => ['42'], 'component' => 'com_sportsmanagement',
                    'published' => 1, 'query' => ['view' => 'ranking']],
                (object) ['id' => 42, 'component' => 'com_sportsmanagement',
                    'published' => 1, 'query' => ['view' => 'ranking']],
            ];
        }
    };
    $selected = $findMenu->invoke($router, ['Itemid' => 42], 'ranking');
    if (($selected->id ?? null) !== 42) {
        fwrite(STDERR, 'Malformed menu ID affected SEF scoring.' . PHP_EOL);
        exit(1);
    }

    echo "SEF scalar comparison and menu selector checks passed.\n";
}
