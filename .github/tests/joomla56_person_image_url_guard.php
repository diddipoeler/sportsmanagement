<?php
/**
 * Joomla 5/6 frontend person image URI and placeholder compatibility tests.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Component {
    final class ComponentHelper
    {
        public static function getParams(string $component): object
        {
            if ($component !== 'com_sportsmanagement') {
                throw new \RuntimeException('Unexpected component.');
            }

            return new class {
                public function get(string $key, string $default = ''): string
                {
                    return $key === 'ph_player' ? 'images/placeholder.png' : $default;
                }
            };
        }
    }
}

namespace Joomla\CMS\Uri {
    final class Uri
    {
        public static function root(): string
        {
            return 'https://sports.example/joomla/';
        }
    }
}

namespace {
    define('_JEXEC', 1);
    // When a remote-picture-server setting is malformed, use Joomla's site root.
    define('COM_SPORTSMANAGEMENT_PICTURE_SERVER', 'javascript:alert(1)');

    require_once dirname(__DIR__, 2) . '/site/src/Helper/PersonImageHelper.php';

    $helper = \Diddipoeler\Component\SportsManagement\Site\Helper\PersonImageHelper::class;

    foreach ([
        'images/person.jpg' => 'https://sports.example/joomla/images/person.jpg',
        '/images/person.jpg' => 'https://sports.example/joomla/images/person.jpg',
        'https://cdn.example/person.jpg' => 'https://cdn.example/person.jpg',
        'https://cdn.example/person.jpg?size=4' => 'https://cdn.example/person.jpg?size=4',
        '' => '',
        '//evil.example/person.jpg' => '',
        'javascript:alert(1)' => '',
        'data:image/png;base64,AA' => '',
        'vbscript:alert(1)' => '',
        'ftp://files.example/person.jpg' => '',
        'https://user:password@cdn.example/person.jpg' => '',
        'images/../secret.jpg' => '',
        'images/%2e%2e/secret.jpg' => '',
        'images/%252e%252e/secret.jpg' => '',
        'images/%0Dsecret.jpg' => '',
        'images/test.jpg?token=1' => '',
        'images/test.jpg#fragment' => '',
        'images\outside.jpg' => '',
    ] as $input => $expected) {
        $actual = $helper::url($input);

        if ($actual !== $expected) {
            throw new \RuntimeException(
                'Person image URI normalized incorrectly: '
                . var_export($input, true) . ': ' . var_export($actual, true)
            );
        }
    }

    if ($helper::placeholder() !== 'images/placeholder.png'
        || $helper::resolve('', 'images/placeholder.png', 'images/person.jpg') !== 'images/person.jpg'
        || $helper::resolve('', 'images/placeholder.png') !== 'images/placeholder.png') {
        throw new \RuntimeException('Person image placeholders or selection have regressed.');
    }

    echo "Joomla 5/6 person image URL validation passed.\n";
}
