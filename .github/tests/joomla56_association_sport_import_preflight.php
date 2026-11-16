<?php
/**
 * Validate Joomla 5/6 association cleanup and sport insertion preflight.
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

namespace Joomla\CMS\Component {
    class ComponentHelper
    {
        public static mixed $configuredCountries = [];

        /**
         * Supply the configured countries without a Joomla installation.
         *
         * @param string $component Component identifier.
         * @return object Joomla registry-style configuration.
         */
        public static function getParams(string $component): object
        {
            return new class {
                /**
                 * Return the test country configuration.
                 *
                 * @param string $name Joomla setting identifier.
                 * @param mixed $default Fallback setting value.
                 * @return mixed Raw configuration.
                 */
                public function get(string $name, mixed $default = null): mixed
                {
                    return ComponentHelper::$configuredCountries;
                }
            };
        }
    }
}

namespace Joomla\CMS\Language {
    class Text
    {
        /**
         * Keep translation identifiers stable for isolated validation.
         *
         * @param string $key Joomla language identifier.
         * @return string Untranslated language identifier.
         */
        public static function _(string $key): string
        {
            return $key;
        }

        /**
         * Return the translation identifier with no Joomla application.
         *
         * @param string $key Joomla translation identifier.
         * @param mixed ...$values Translation substitution values.
         * @return string Stable identifier.
         */
        public static function sprintf(string $key, mixed ...$values): string
        {
            return $key;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Model/DatabasetoolModel.php';

    $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\DatabasetoolModel();

    set_error_handler(
        /**
         * Fail tests on any PHP 8 input conversion warning.
         *
         * @param int $severity PHP diagnostic severity.
         * @param string $message Original diagnostic.
         * @return never Always throws.
         */
        static function (int $severity, string $message): never {
            throw new \ErrorException($message, 0, $severity);
        }
    );

    try {
        $invalidCountries = [
            false,
            123,
            new \stdClass(),
            [['DE']],
            ['DE', new \stdClass()],
            ['DE', ['FR']],
            ['DE', 'US;DROP'],
            ['DE', '../GB'],
            ['DE', str_repeat('Z', 33)],
            array_fill(0, 301, 'DE'),
        ];

        foreach ($invalidCountries as $invalid) {
            \Joomla\CMS\Component\ComponentHelper::$configuredCountries = $invalid;

            // There is no database or application container in this fixture.
            // A request to execute the association DELETE will fail the test.
            if ($model->checkAssociations() !== false) {
                throw new \RuntimeException('Malformed association list reached destructive SQL.');
            }
        }

        \Joomla\CMS\Component\ComponentHelper::$configuredCountries = [];

        if ($model->checkAssociations() !== true) {
            throw new \RuntimeException('Empty country selection changed behavior.');
        }

        foreach ([
            false, true, null, [], ['football'], new \stdClass(),
            '../football', 'foot/ball', 'foot ball', str_repeat('x', 65),
        ] as $invalidType) {
            // Invalid sport types must be rejected without accessing XML or DB.
            if ($model->insertSportType($invalidType) !== false) {
                throw new \RuntimeException('Malformed sport token accepted for insert.');
            }
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 association and sport insert preflight: OK\n";
}
