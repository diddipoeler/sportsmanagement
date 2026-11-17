<?php
/**
 * Check Joomla 5/6 logo fallbacks and administrator raw select-option guards.
 *
 * Run: php .github/tests/joomla56-logo-fallback-raw-option-controls.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Component {
    /** Provide component placeholders without a Joomla bootstrap. */
    final class ComponentHelper
    {
        /**
         * Return a registry-like test object with no configured placeholder.
         *
         * @param string $component Component identifier.
         * @return object Test settings accessor.
         */
        public static function getParams(string $component): object
        {
            return new class {
                /**
                 * Return the default component option.
                 *
                 * @param string $key Registry key.
                 * @param mixed $default Optional fallback value.
                 * @return mixed Configured fallback value.
                 */
                public function get(string $key, mixed $default = ''): mixed
                {
                    return $default;
                }
            };
        }
    }
}

namespace Joomla\CMS\Uri {
    /** Provide a deterministic site URL for relative logo images. */
    final class Uri
    {
        /**
         * Return the fake site root.
         *
         * @return string Root URL.
         */
        public static function root(): string
        {
            return 'https://example.test/';
        }
    }
}

namespace Joomla\CMS\HTML {
    /** Capture images passed into Joomla's HTML rendering helper. */
    final class HTMLHelper
    {
        /**
         * Return the validated source for HTML image assertions.
         *
         * @param string $source Logo path or URL.
         * @param string $alt Alternative text.
         * @param array $attributes HTML image settings.
         * @return string Captured source URL.
         */
        public static function image(string $source, string $alt, array $attributes): string
        {
            return $source;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Helper {
    /** Capture URLs selected by the team logo renderer without Joomla modals. */
    final class ModalImageHelper
    {
        /**
         * Return the final media URL for use in regression assertions.
         *
         * @param string $target Modal element identifier.
         * @param string $picture Fully resolved picture URL.
         * @param mixed ...$remaining Original legacy presentation arguments.
         * @return string Selected picture URL.
         */
        public static function render(string $target, string $picture, mixed ...$remaining): string
        {
            return $picture;
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Site\Helper\ClubLogoHelper;
    use Diddipoeler\Component\SportsManagement\Site\Helper\TeamLogoHelper;

    \define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Helper/PersonImageHelper.php';
    require_once $root . '/site/src/Helper/TeamLogoHelper.php';
    require_once $root . '/site/src/Helper/ClubLogoHelper.php';

    $team = (object) [
        'picture' => '../unsafe.png',
        'logo_small' => 'images/clubs/safe.png',
        'name' => 'Safe Club',
    ];

    if (TeamLogoHelper::render($team, 'logo') !== 'https://example.test/images/clubs/safe.png') {
        throw new \RuntimeException('Unsafe priority picture hid a valid fallback team logo.');
    }

    $team->picture = 'https://user:secret@example.test/broken.png';

    if (TeamLogoHelper::render($team, 'logo') !== 'https://example.test/images/clubs/safe.png') {
        throw new \RuntimeException('Credential-bearing priority picture hid fallback logo.');
    }

    if (ClubLogoHelper::render('../outside.png') !== ''
        || ClubLogoHelper::render('javascript:alert(1)') !== ''
        || ClubLogoHelper::render('%252e%252e/private.png') !== '') {
        throw new \RuntimeException('Compact club logo accepted an unsafe image path.');
    }

    if (ClubLogoHelper::render('images/clubs/safe.png') !== 'images/clubs/safe.png') {
        throw new \RuntimeException('Valid compact club logo was rejected.');
    }

    $admin = file_get_contents($root . '/admin/src/Helper/ExtraSelectOptionsHelper.php');

    if (!is_string($admin)
        || !str_contains($admin, '$rawLabel = $labels[$key] ?? $value;')
        || !str_contains($admin, "preg_match('/[\\x00-\\x1F\\x7F]/', " . '$value . $rawLabel' . ")")
        || strpos($admin, '$rawLabel = $labels[$key] ?? $value;') >
            strpos($admin, '$value = trim($value);')) {
        throw new \RuntimeException('Administrator option control bytes must be checked before trim.');
    }

    echo "Joomla 5/6 team and club logo fallbacks and option controls passed.\n";
}
