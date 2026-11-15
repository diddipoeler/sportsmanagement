<?php
/**
 * Regression test for Joomla 5/6 frontend and admin image-size settings.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Component {
    class ComponentHelper
    {
        public static mixed $configured = 120;

        /**
         * Provide component configuration for image size limit checks.
         *
         * @param string $component Joomla extension identifier.
         * @return object Mock registry with the selected image limit.
         */
        public static function getParams(string $component): object
        {
            return new class {
                /**
                 * Read the component image limit without type coercion.
                 *
                 * @param string $key Component configuration option.
                 * @param mixed $default Fallback value.
                 * @return mixed User-configured or corrupt legacy value.
                 */
                public function get(string $key, mixed $default = null): mixed
                {
                    return \Joomla\CMS\Component\ComponentHelper::$configured;
                }
            };
        }
    }
}

namespace Joomla\CMS\MVC\Controller {
    class BaseController
    {
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller {
    class SportsManagementAdminController extends \Joomla\CMS\MVC\Controller\BaseController
    {
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Controller/ImagehandlerController.php';
    require_once dirname(__DIR__, 2) . '/admin/src/Controller/ImagehandlerController.php';

    $controllers = [
        \Diddipoeler\Component\SportsManagement\Site\Controller\ImagehandlerController::class,
        \Diddipoeler\Component\SportsManagement\Administrator\Controller\ImagehandlerController::class,
    ];

    set_error_handler(
        /**
         * Treat invalid image configuration warnings as test failures.
         *
         * @param int $severity PHP warning or notice level.
         * @param string $message PHP diagnostic text.
         * @return never Always throws so regressions cannot be ignored.
         */
        static function (int $severity, string $message): never {
            throw new \ErrorException($message, 0, $severity);
        }
    );

    try {
        $cases = [
            [120, 120 * 1024],
            ['0120', 120 * 1024],
            ['0000', 1024],
            [0, 1024],
            [65536, 65536 * 1024],
            ['65537', 65536 * 1024],
            ['99999999999999999999', 120 * 1024],
            ['120junk', 120 * 1024],
            ['-5', 120 * 1024],
            [false, 120 * 1024],
            [true, 120 * 1024],
            [['120'], 120 * 1024],
            [new \stdClass(), 120 * 1024],
        ];

        foreach ($controllers as $class) {
            $instance = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
            $method = new \ReflectionMethod($class, 'maxImageBytes');

            foreach ($cases as [$configured, $expected]) {
                \Joomla\CMS\Component\ComponentHelper::$configured = $configured;
                $actual = $method->invoke($instance);

                if ($actual !== $expected) {
                    throw new \RuntimeException(
                        $class . ' returned ' . $actual . ' bytes for ' . get_debug_type($configured)
                    );
                }
            }
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 site/admin image size configuration: OK\n";
}
