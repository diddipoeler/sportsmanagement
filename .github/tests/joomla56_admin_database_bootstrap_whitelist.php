<?php
/**
 * Ensure Joomla 5/6 sport bootstrap only uses explicitly allowed tables.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Model {
    class BaseDatabaseModel {}
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Model/DatabasetoolModel.php';

    $class = \Diddipoeler\Component\SportsManagement\Administrator\Model\DatabasetoolModel::class;
    $model = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
    $allowlist = new \ReflectionMethod($model, 'allowedBootstrapTable');

    foreach (['eventtype', 'position'] as $table) {
        if ($allowlist->invoke($model, $table, ['eventtype', 'position']) !== $table) {
            throw new \RuntimeException('Allowed sport-bootstrap relation rejected.');
        }
    }

    foreach (['', 'eventtype;DROP', 'position ', 'position/**/',
        '#__users', 'eventtype OR 1=1', 'position_eventtype'] as $invalid) {
        try {
            $allowlist->invoke($model, $invalid, ['eventtype', 'position']);
            throw new \RuntimeException('Bootstrap table selector accepted invalid input.');
        } catch (\InvalidArgumentException) {
            // Reject all unexpected table names before SQL query construction.
        }
    }

    echo "Database tool sport bootstrap table allowlist OK\n";
}
