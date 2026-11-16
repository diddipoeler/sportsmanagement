<?php
/**
 * Verify Joomla 5/6 database quote import rejects malformed module settings.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Model {
    class BaseDatabaseModel
    {
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Model/DatabasetoolModel.php';

    $class = \Diddipoeler\Component\SportsManagement\Administrator\Model\DatabasetoolModel::class;
    $model = new $class();
    $parser = new \ReflectionMethod($class, 'normaliseQuoteDefinitions');

    set_error_handler(
        /**
         * Convert PHP warnings from malformed import settings into failures.
         *
         * @param int $severity PHP warning level.
         * @param string $message Original diagnostic.
         * @return never Always raises a test failure.
         */
        static function (int $severity, string $message): never {
            throw new \ErrorException($message, 0, $severity);
        }
    );

    try {
        $valid = [
            'football,10',
            'hockey,0007',
            ['basketball,3', 'football,10'],
        ];
        $expected = [
            [['football', 10]],
            [['hockey', 7]],
            [['basketball', 3], ['football', 10]],
        ];

        foreach ($valid as $index => $input) {
            if ($parser->invoke(null, $input) !== $expected[$index]) {
                throw new \RuntimeException('Valid legacy quote definition was rejected.');
            }
        }

        foreach ([
            null,
            true,
            new \stdClass(),
            ['football,0', ['nested']],
            [new \stdClass(), 'bad/types,10', 'foo,NaN'],
            ['football,-10', 'football,12junk'],
            ['../../secret,12', 'football,9999999999999999999999999'],
            array_fill(0, 101, 'football,1'),
        ] as $invalid) {
            if ($parser->invoke(null, $invalid) !== []) {
                throw new \RuntimeException('Unsafe quote import definition was accepted.');
            }

            // No database resolver is registered in this isolated test.
            // Invalid input therefore must return before touching SQL.
            if ($model->checkQuotes($invalid) !== '') {
                throw new \RuntimeException('Malformed quote configuration imported data.');
            }
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 database quote import validation: OK\n";
}
