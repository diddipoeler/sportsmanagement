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
    class RouterBase {}
}

namespace {
    define('_JEXEC', 1);

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

    echo "SEF scalar comparison checks passed.\n";
}
