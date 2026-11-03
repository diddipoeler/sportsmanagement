<?php
/**
 * Regression checks for Playground Plan venue selection and malformed fields.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);
require dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_playgroundplan/src/Helper/PlaygroundPlanHelper.php';

$class = \Diddipoeler\Module\SportsManagementPlaygroundPlan\Site\Helper\PlaygroundPlanHelper::class;
$helper = (new ReflectionClass($class))->newInstanceWithoutConstructor();
$select = new ReflectionMethod($class, 'playground');

$cases = [
    [(object) ['playground_id' => '12', 'playground_alias' => 'main', 'playground_name' => 'Main', 'playground_picture' => 'main.png'], [12, 'main', 'Main', 'main.png']],
    [(object) ['playground_id' => ['12'], 'team_playground_id' => 3, 'team_playground_name' => 'Second'], [3, '', 'Second', '']],
    [(object) ['playground_id' => PHP_INT_MAX . '0', 'club_playground_id' => '5', 'club_playground_alias' => ['bad']], [5, '', '', '']],
    [(object) ['playground_id' => 'nope', 'team_playground_id' => 0], [0, '', '', '']],
];

foreach ($cases as [$row, $expected]) {
    if ($select->invoke($helper, $row) !== $expected) {
        fwrite(STDERR, 'Playground Plan venue selection regression.' . PHP_EOL);
        exit(1);
    }
}

// A lightweight component-configuration double lets this CLI regression
// exercise placeholder type handling without booting Joomla.
if (!class_exists('Joomla\\CMS\\Component\\ComponentHelper', false)) {
    eval('namespace Joomla\\CMS\\Component; class ComponentHelper { public static mixed $params; public static function getParams(string $name): object { return self::$params; } }');
}
\Joomla\CMS\Component\ComponentHelper::$params = new class {
    /**
     * Return malformed placeholder configuration from the test fixture.
     *
     * @param string $key Requested placeholder setting.
     * @param mixed $default Fallback parameter value.
     * @return mixed Simulated Joomla component configuration.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return ['bad'];
    }
};
$logo = new ReflectionMethod($class, 'logo');
if ($logo->invoke($helper, '', 'logo_big') !== ''
    || $logo->invoke($helper, 'valid.png', 'logo_big') !== 'valid.png') {
    fwrite(STDERR, 'Playground Plan logo placeholder validation regression.' . PHP_EOL);
    exit(1);
}

$validFixtureRows = new ReflectionMethod($class, 'validFixtureRows');
$validFirst = (object) ['project_id' => '0007'];
$validSecond = (object) ['project_id' => 8];
$fixtures = [
    $validFirst,
    (object) ['project_id' => 0],
    (object) ['project_id' => ['9']],
    (object) ['project_id' => '4invalid'],
    (object) ['project_id' => PHP_INT_MAX . '0'],
    (object) ['project_id' => -1],
    new stdClass(),
    ['project_id' => 2],
    $validSecond,
];
if ($validFixtureRows->invoke(null, $fixtures) !== [$validFirst, $validSecond]
    || $validFixtureRows->invoke(null, new stdClass()) !== []) {
    fwrite(STDERR, 'Playground Plan invalid project row regression.' . PHP_EOL);
    exit(1);
}

$nonNegativeInteger = new ReflectionMethod($class, 'nonNegativeInteger');
foreach ([
    [['7'], 0], [false, 0], ['7invalid', 0],
    [PHP_INT_MAX . '0', 0], ['0002', 2],
] as [$value, $expected]) {
    if ($nonNegativeInteger->invoke(null, $value) !== $expected) {
        fwrite(STDERR, 'Playground Plan season identifier regression.' . PHP_EOL);
        exit(1);
    }
}

echo "Playground Plan venue checks passed.\n";
