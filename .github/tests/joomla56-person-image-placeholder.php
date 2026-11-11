<?php
/**
 * Joomla 5/6 site player picture placeholder and URL regression tests.
 *
 * Runs without the CMS so invalid component parameters can be exercised
 * under PHP 8 with warnings promoted to exceptions.
 *
 * Usage: php .github/tests/joomla56-person-image-placeholder.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Component {
    final class ComponentHelper
    {
        public static mixed $placeholder = '';

        /**
         * Return a lightweight component Registry with a configurable value.
         *
         * @param string $component Component option expected by site helpers.
         * @return object Joomla parameter fixture exposing get().
         */
        public static function getParams(string $component): object
        {
            if ($component !== 'com_sportsmanagement') {
                throw new \RuntimeException('Unexpected component configuration lookup.');
            }

            return new class {
                /**
                 * Return the placeholder fixture rather than reading Joomla.
                 *
                 * @param string $name Requested Registry option.
                 * @param mixed $default Missing-option fallback.
                 * @return mixed Configured placeholder value.
                 */
                public function get(string $name, mixed $default = null): mixed
                {
                    return $name === 'ph_player' ? ComponentHelper::$placeholder : $default;
                }
            };
        }
    }
}

namespace Joomla\CMS\Uri {
    final class Uri
    {
        /**
         * Return the installation root of a Joomla site in a subdirectory.
         *
         * @return string Canonical HTTPS root URL.
         */
        public static function root(): string
        {
            return 'https://sports.example.test/subdir/';
        }
    }
}

namespace {
    \define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/site/src/Helper/PersonImageHelper.php';

    $helper = \Diddipoeler\Component\SportsManagement\Site\Helper\PersonImageHelper::class;
    $component = \Joomla\CMS\Component\ComponentHelper::class;

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        foreach ([['invalid'], new \stdClass(), false, null] as $badPlaceholder) {
            $component::$placeholder = $badPlaceholder;

            if ($helper::placeholder() !== '' || $helper::resolve('', '') !== '') {
                throw new \RuntimeException('Invalid Joomla player placeholder was not discarded.');
            }
        }

        $component::$placeholder = ' images/player-default.png ';

        if ($helper::placeholder() !== 'images/player-default.png'
            || $helper::resolve('', 'images/player.jpg') !== 'images/player.jpg'
            || $helper::resolve('images/player-default.png') !== 'images/player-default.png'
        ) {
            throw new \RuntimeException('Valid player picture selection changed.');
        }

        foreach ([
            ['images/player.png', 'https://sports.example.test/subdir/images/player.png'],
            ['https://cdn.example.test/person.png', 'https://cdn.example.test/person.png'],
            ['javascript:alert(1)', ''],
            ['../secret.png', ''],
            ['images/%2e%2e/secret.png', ''],
            ['https://user:password@cdn.example.test/person.png', ''],
        ] as [$picture, $expected]) {
            if ($helper::url($picture) !== $expected) {
                throw new \RuntimeException('Unsafe or incorrect player picture URL: ' . $picture);
            }
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 player picture placeholder regressions passed.\n";
}
