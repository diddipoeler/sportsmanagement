<?php
/**
 * Regression coverage for native team logos and Joomla form/module guards.
 *
 * Run: php .github/tests/joomla56-team-logos-options-matches.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Component {
    /** Minimal component parameters for standalone rendering tests. */
    final class ComponentHelper
    {
        /**
         * Supply deliberately invalid placeholder values to test safe coercion.
         *
         * @param string $component Joomla component identifier.
         * @return object Registry-compatible parameter reader.
         */
        public static function getParams(string $component): object
        {
            return new class {
                /**
                 * Return invalid legacy image settings for safe fallback checks.
                 *
                 * @param string $key Registry parameter name.
                 * @param mixed $default Optional registry fallback.
                 * @return array<int,string> Invalid nested picture setting.
                 */
                public function get(string $key, mixed $default = ''): array
                {
                    return ['not-a-path'];
                }
            };
        }
    }
}

namespace Joomla\CMS\Uri {
    /** Minimal Joomla URI façade for local picture references. */
    final class Uri
    {
        /**
         * Return the standalone test website root.
         *
         * @return string Fully qualified site URL.
         */
        public static function root(): string
        {
            return 'https://example.test/';
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Helper {
    /** Test-only modal renderer that returns its validated URL and alt text. */
    final class ModalImageHelper
    {
        /**
         * Expose the URL and accessible label handed to the image renderer.
         *
         * @param string $target Modal identifier.
         * @param string $url Fully validated picture URL.
         * @param string $alt Accessible image description.
         * @param mixed ...$ignored Legacy sizing and popup settings.
         * @return string Encoded URL and alt text for test assertions.
         */
        public static function render(string $target, string $url, string $alt, ...$ignored): string
        {
            return $url . '|' . $alt;
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Site\Helper\TeamLogoHelper;

    \define('_JEXEC', 1);
    \define('JPATH_SITE', sys_get_temp_dir());
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Helper/PersonImageHelper.php';
    require_once $root . '/site/src/Helper/TeamLogoHelper.php';

    set_error_handler(
        /**
         * Promote PHP 8 warnings to failures when malformed legacy records are read.
         *
         * @param int $severity Error severity value.
         * @param string $message Diagnostic text.
         * @return never Always throws to fail the regression check.
         */
        static function (int $severity, string $message): never {
            throw new \ErrorException($message, 0, $severity);
        }
    );

    try {
        $team = (object) ['logo_small' => ['invalid'], 'name' => ['invalid']];

        if (TeamLogoHelper::render($team, 'modal') !== ''
            || TeamLogoHelper::renderVariant($team, 'logo_small', 'modal') !== '') {
            throw new \RuntimeException('Invalid array logo or placeholder was rendered.');
        }

        $team->logo_small = 'javascript:alert(1)';

        if (TeamLogoHelper::renderVariant($team, 'logo_small', 'modal') !== '') {
            throw new \RuntimeException('Executable picture scheme must be rejected.');
        }

        $team->logo_small = 'https://user:pass@example.test/logo.png';

        if (TeamLogoHelper::render($team, 'modal') !== '') {
            throw new \RuntimeException('Credential-bearing remote logo must be rejected.');
        }

        $team->logo_small = 'https://example.test/logo.png';
        $team->name = 'Example';

        if (TeamLogoHelper::render($team, 'modal') !== 'https://example.test/logo.png|Example') {
            throw new \RuntimeException('Valid remote team logos must still render.');
        }

        $team->logo_small = '../private.png';

        if (TeamLogoHelper::renderVariant($team, 'logo_small', 'modal') !== '') {
            throw new \RuntimeException('Local path traversal must be rejected.');
        }
    } finally {
        restore_error_handler();
    }

    $options = file_get_contents($root . '/admin/src/Helper/ExtraSelectOptionsHelper.php');
    $matches = file_get_contents($root . '/modules/mod_sportsmanagement_matches/src/Dispatcher/Dispatcher.php');

    if (!is_string($options) || !str_contains($options, "preg_match('//u', " . '$value . $label' . ")")) {
        throw new \RuntimeException('Administrator extra select options must reject malformed UTF-8.');
    }

    if (!is_string($matches) || !str_contains($matches, 'strlen($rawTemplate) <= 100')) {
        throw new \RuntimeException('Matches template names must be length bounded.');
    }

    echo "Joomla 5/6 team logo, extra options and matches regressions passed.\n";
}
