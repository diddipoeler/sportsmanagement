<?php
/**
 * Exercise legacy MVC extension-directory filtering under PHP 8 without Joomla.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\MVC\Factory {
    class MVCFactory {}
}

namespace {
    define('_JEXEC', 1);

    $temp = sys_get_temp_dir() . '/jsm-mvc-' . bin2hex(random_bytes(6));
    define('JPATH_ADMINISTRATOR', $temp . '/administrator');
    define('JPATH_SITE', $temp . '/site');

    $extensionsRoot = JPATH_SITE . '/components/com_sportsmanagement/extensions';
    $validPath = $extensionsRoot . '/valid-extension/admin';

    if (!mkdir($validPath, 0700, true)) {
        throw new \RuntimeException('Could not create the MVC extension fixture.');
    }

    /**
     * Supply valid and malformed legacy extension records for path discovery.
     */
    final class sportsmanagementHelper
    {
        /**
         * Return mixed types as encountered in corrupted extension registries.
         *
         * @return array Mixed historical extension names.
         */
        public static function getExtensions(): array
        {
            return ['valid-extension', [], new \stdClass(), false, 22, '../outside', 'not-installed'];
        }
    }

    require dirname(__DIR__, 2) . '/admin/src/Service/SportsManagementMVCFactory.php';

    $factory = new \Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementMVCFactory();
    $method = new \ReflectionMethod($factory, 'legacySearchPaths');

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $paths = $method->invoke($factory, 'Administrator');
    } finally {
        restore_error_handler();
    }

    $expected = [
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement',
        $validPath,
    ];

    if ($paths !== $expected) {
        throw new \RuntimeException('Unexpected or unsafe legacy MVC search paths: ' . var_export($paths, true));
    }

    echo "MVC extension paths: invalid records ignored, valid installed path retained.\n";
}
