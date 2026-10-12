<?php
/**
 * Standalone regression checks for club-birthday IDs and image paths.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Registry {
    final class Registry
    {
        public function __construct(private array $values = [])
        {
        }

        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }
    }
}

namespace Joomla\Filesystem {
    final class File
    {
        public static function exists(string $path): bool
        {
            return is_file($path);
        }
    }
}

namespace Joomla\CMS\Uri {
    final class Uri
    {
        public static function root(): string
        {
            return 'https://example.test/';
        }
    }
}

namespace {
    \defined('_JEXEC') || define('_JEXEC', 1);

    $testRoot = sys_get_temp_dir() . '/jsm-club-birthday-' . bin2hex(random_bytes(8));

    if (!mkdir($testRoot . '/images', 0700, true)) {
        throw new \RuntimeException('Cannot create temporary image fixtures');
    }

    define('JPATH_ROOT', $testRoot);

    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_club_birthday/src/Helper/ClubBirthdayHelper.php';

    $helper = \Diddipoeler\Module\SportsManagementClubBirthday\Site\Helper\ClubBirthdayHelper::class;
    $ids = new \ReflectionMethod($helper, 'normaliseIds');
    $result = $ids->invoke(null, ['0002', '12:team', str_repeat('9', 40), ['17'], '0', 'invalid', '12']);

    if ($result !== [2, 12]) {
        throw new \RuntimeException('Invalid or overflowing season IDs were accepted');
    }

    $pathMethod = new \ReflectionMethod($helper, 'localImagePath');

    foreach ([
        'images/allowed.png' => 'images/allowed.png',
        '../secret.png' => '',
        'images/../secret.png' => '',
        'https://example.test/image.png' => '',
        '//example.test/image.png' => '',
        'images\backslash.png' => '',
    ] as $candidate => $expected) {
        if ($pathMethod->invoke(null, $candidate) !== $expected) {
            throw new \RuntimeException('Unexpected image path accepted: ' . $candidate);
        }
    }

    if ($pathMethod->invoke(null, "images/\x00broken.png") !== '') {
        throw new \RuntimeException('NUL byte accepted in image path');
    }

    $picture = new \ReflectionMethod($helper, 'pictureUrl');
    $params = new \Joomla\Registry\Registry(['ph_clublogo' => 'images/fallback.png']);

    try {
        file_put_contents($testRoot . '/images/allowed.png', 'test');
        file_put_contents($testRoot . '/images/fallback.png', 'test');

        $cases = [
            ['images/allowed.png', 'https://example.test/images/allowed.png'],
            ['../secret.png', 'https://example.test/images/fallback.png'],
            ['https://example.test/foreign.png', 'https://example.test/images/fallback.png'],
            ['missing.png', 'https://example.test/images/fallback.png'],
        ];

        foreach ($cases as [$input, $expected]) {
            if ($picture->invoke(null, $input, $params) !== $expected) {
                throw new \RuntimeException('Unexpected image URL for ' . $input);
            }
        }

        $outside = tempnam(sys_get_temp_dir(), 'jsm-outside-');
        if ($outside === false) {
            throw new \RuntimeException('Cannot create external file fixture');
        }

        try {
            if (function_exists('symlink') && @symlink($outside, $testRoot . '/images/outside.png')) {
                if ($picture->invoke(null, 'images/outside.png', $params)
                    !== 'https://example.test/images/fallback.png') {
                    throw new \RuntimeException('External symbolic link accepted as club image');
                }
                unlink($testRoot . '/images/outside.png');
            }
        } finally {
            unlink($outside);
        }

        $flag = new \ReflectionMethod($helper, 'flagHtml');
        $cssParams = new \Joomla\Registry\Registry(['cfg_flags_css' => 1]);
        $validFlag = $flag->invoke(null, (object) [
            'country' => 'DEU',
            'country_alpha2' => 'de',
        ], $cssParams);

        if (!str_contains($validFlag, 'fi-de')) {
            throw new \RuntimeException('Valid ISO flag code was not rendered');
        }

        $invalidFlag = $flag->invoke(null, (object) [
            'country' => 'ZZZ',
            'country_alpha2' => '../../wrong',
        ], $cssParams);

        if ($invalidFlag !== '') {
            throw new \RuntimeException('Malformed country code was accepted as a CSS flag');
        }

        $imageParams = new \Joomla\Registry\Registry(['cfg_flags_css' => 0]);
        $unsafeFlag = $flag->invoke(null, (object) [
            'country' => 'ZZZ',
            'country_alpha2' => '',
            'country_picture' => '../private.png',
        ], $imageParams);

        if ($unsafeFlag !== '') {
            throw new \RuntimeException('Parent-directory flag path was accepted');
        }

        echo "Club birthday IDs and image paths OK\n";
    } finally {
        @unlink($testRoot . '/images/outside.png');
        @unlink($testRoot . '/images/allowed.png');
        @unlink($testRoot . '/images/fallback.png');
        @rmdir($testRoot . '/images');
        @rmdir($testRoot);
    }
}
