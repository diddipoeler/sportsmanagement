<?php
/**
 * Standalone regression for native ranking loader route generation and invalid filters.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace {
    define('_JEXEC', 1);
    require dirname(__DIR__, 2) . '/site/src/Service/RankingDataLoader.php';
    require dirname(__DIR__, 2) . '/site/src/Service/RankingEngine.php';

    // No query methods: an invalid filter must never access the database.
    $db = new class implements \Joomla\Database\DatabaseInterface {};
    $loader = new \Diddipoeler\Component\SportsManagement\Site\Service\RankingDataLoader($db);
    $engine = new \Diddipoeler\Component\SportsManagement\Site\Service\RankingEngine($db);

    foreach ([[0, 0, 0, 0], [-1, 0, 0, 0], [1, -1, 0, 0],
        [1, 0, -1, 0], [1, 0, 0, -1]] as $filters) {
        $data = $loader->load(...$filters);
        if ($data !== ['project' => null, 'teams' => [], 'matches' => [], 'config' => [], 'colors' => []]) {
            throw new \RuntimeException('Invalid ranking loader filter returned data.');
        }
        $result = $engine->calculate(...$filters);
        if ((array) $result['project'] !== [] || $result['ranking'] !== []
            || $result['config'] !== [] || $result['colors'] !== [] || $result['matches'] !== 0) {
            throw new \RuntimeException('Invalid ranking engine filter returned data.');
        }
    }

    $slug = new \ReflectionMethod($loader, 'routeSlug');
    foreach ([
        [12, 'verein', '12:verein'],
        ['12', '', '12:'],
        [12, null, '12'],
        [null, null, ''],
        [null, 'alias', 'alias'],
        [12, ['invalid'], '12'],
        [['invalid'], null, ''],
        ['12junk', null, ''],
        [0, '', '0:'],
    ] as [$id, $alias, $expected]) {
        if ($slug->invoke(null, $id, $alias) !== $expected) {
            throw new \RuntimeException('Ranking route slug regression.');
        }
    }

    echo "Ranking loader filter and route slug regressions passed.\n";
}
