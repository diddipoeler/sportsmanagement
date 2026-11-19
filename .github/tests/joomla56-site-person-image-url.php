<?php
/**
 * Check native site person-image URL validation against malformed inputs.
 *
 * Usage: php .github/tests/joomla56-site-person-image-url.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Uri {
    /** Minimal Joomla Uri shim for isolated URL validation tests. */
    final class Uri
    {
        /**
         * Supply a deterministic Joomla site root without bootstrapping Joomla.
         *
         * @return string Test-only site origin.
         */
        public static function root(): string
        {
            return 'https://sports.example/';
        }
    }
}

namespace {
    defined('_JEXEC') || define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Helper/PersonImageHelper.php';

    $class = \Diddipoeler\Component\SportsManagement\Site\Helper\PersonImageHelper::class;

    if ($class::url('images/club.png') !== 'https://sports.example/images/club.png') {
        throw new \RuntimeException('Valid site image URL changed unexpectedly.');
    }

    foreach ([
        '', "\xC3\x28", "images/club.png\n", "\timages/club.png",
        "images/\x00club.png", str_repeat('p', 4097),
        'javascript:alert(1)', '//evil.example/logo.png',
        '../private.png', 'images/%2e%2e/private.png'
    ] as $invalid) {
        if ($class::url($invalid) !== '') {
            throw new \RuntimeException('Unsafe person image URL accepted.');
        }
    }

    foreach (['javascript:alert(1)', '//evil.example/logo.png', '../private.png'] as $unsafe) {
        for ($layers = 1; $layers <= 10; $layers++) {
            $unsafe = rawurlencode($unsafe);
            if ($class::url($unsafe) !== '') {
                throw new \RuntimeException('Nested encoded person image URL accepted.');
            }
        }
    }

    echo "Joomla 5/6 site person image URL checks passed.\n";
}
