<?php
/**
 * Verify Joomla 5/6 sport bootstrap imports reject malformed legacy input.
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

namespace Joomla\CMS\Language {
    class Text
    {
        /**
         * Use an untranslated error key for the isolated bootstrap test.
         *
         * @param string $key Joomla translation identifier.
         * @return string Stable validation error.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Model/DatabasetoolModel.php';

    $type = \Diddipoeler\Component\SportsManagement\Administrator\Model\DatabasetoolModel::class;
    $model = new $type();
    $idParser = new \ReflectionMethod($type, 'positiveBootstrapId');

    set_error_handler(
        /**
         * Fail on any implicit PHP 8 conversion warnings.
         *
         * @param int $severity Diagnostic severity.
         * @param string $message PHP warning or notice.
         * @return never Always throws on warnings.
         */
        static function (int $severity, string $message): never {
            throw new \ErrorException($message, 0, $severity);
        }
    );

    try {
        foreach ([1 => 1, 25 => 25, '00037' => 37] as $raw => $expected) {
            if ($idParser->invoke(null, $raw) !== $expected) {
                throw new \RuntimeException('Valid Joomla sport ID was rejected.');
            }
        }

        foreach ([null, false, true, 0, -1, 1.2, [], ['1'], new \stdClass(),
            '0', '-1', '1junk', '999999999999999999999999999999'] as $raw) {
            if ($idParser->invoke(null, $raw) !== 0) {
                throw new \RuntimeException('Malformed sport ID was accepted.');
            }
        }

        foreach ([null, false, true, [], ['football'], new \stdClass(), '../football',
            'football/other', str_repeat('a', 65)] as $token) {
            // No JPATH_ADMINISTRATOR or Joomla DB service is configured.
            // Malformed tokens must return without touching the filesystem.
            if ($model->checkSportTypeStructur($token) !== false) {
                throw new \RuntimeException('Malformed sport XML token was accepted.');
            }

            $model->addStandardForSportType('Sport', 1, $token);
        }

        foreach ([null, false, [], ['2'], new \stdClass(), 0, -1,
            '2suffix', '999999999999999999999999'] as $sportId) {
            $model->addStandardForSportType('Sport', $sportId, 'football');

            if ($model->insertAgegroup('DE', $sportId) !== 'JERROR_AN_ERROR_HAS_OCCURRED') {
                throw new \RuntimeException('Malformed age-group sport ID reached the database.');
            }
        }

        foreach ([null, false, [], ['DE'], new \stdClass(), '../DE', str_repeat('x', 33)] as $country) {
            if ($model->insertAgegroup($country, 1) !== 'JERROR_AN_ERROR_HAS_OCCURRED') {
                throw new \RuntimeException('Malformed age-group country reached the database.');
            }
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 sport bootstrap input validation: OK\n";
}
