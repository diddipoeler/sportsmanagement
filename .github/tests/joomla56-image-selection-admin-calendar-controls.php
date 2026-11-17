<?php
/**
 * Regression coverage for safe image selection and strict legacy date controls.
 *
 * Run: php .github/tests/joomla56-image-selection-admin-calendar-controls.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Component {
    /** Provide configurable Joomla placeholder paths for isolated assertions. */
    final class ComponentHelper
    {
        /** @var mixed Mocked player image placeholder setting. */
        public static mixed $placeholder = '';

        /**
         * Expose a minimal Joomla-compatible parameter reader.
         *
         * @param string $component Component identifier.
         * @return object Registry-like test object.
         */
        public static function getParams(string $component): object
        {
            return new class {
                /**
                 * Read the configured test placeholder.
                 *
                 * @param string $key Registry setting name.
                 * @param mixed $default Default value.
                 * @return mixed Placeholder setting.
                 */
                public function get(string $key, mixed $default = ''): mixed
                {
                    return ComponentHelper::$placeholder;
                }
            };
        }
    }
}

namespace Joomla\CMS\Uri {
    /** Supply a stable site root for relative image URL validation. */
    final class Uri
    {
        /**
         * Return the standalone fixture root URL.
         *
         * @return string Test site URL.
         */
        public static function root(): string
        {
            return 'https://example.test/';
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Site\Helper\PersonImageHelper;
    use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDateHelper;
    use Diddipoeler\Module\SportsManagementCalendar\Site\Runtime\CalendarFunctions;
    use Joomla\CMS\Component\ComponentHelper;

    \define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);

    require_once $root . '/site/src/Helper/PersonImageHelper.php';
    require_once $root . '/admin/src/Helper/SportsManagementDateHelper.php';
    require_once $root . '/modules/mod_sportsmanagement_calendar/src/Runtime/CalendarFunctions.php';

    ComponentHelper::$placeholder = 'images/players/default.png';

    if (PersonImageHelper::resolve(
        'javascript:alert(1)', '../secret.png', 'images/players/good.png'
    ) !== 'images/players/good.png') {
        throw new \RuntimeException('Unsafe early image suppressed valid later candidate.');
    }

    if (PersonImageHelper::resolve('javascript:alert(1)', 'images/players/default.png')
        !== 'images/players/default.png') {
        throw new \RuntimeException('Valid image placeholder was lost.');
    }

    // A malformed priority picture must not become valid after trim().
    if (PersonImageHelper::resolve(
        "images/players/broken.png\n", 'images/players/good.png'
    ) !== 'images/players/good.png') {
        throw new \RuntimeException('Player image selection accepted a trimmed control byte.');
    }

    if (PersonImageHelper::resolve("images/players/broken.png\0")
        !== 'images/players/default.png') {
        throw new \RuntimeException('Player image selection lost its safe placeholder.');
    }

    foreach ([
        "images/default.png\n",
        "../secret.png",
        "bad\xC3\x28.png",
        ['invalid', 'registry'],
    ] as $placeholder) {
        ComponentHelper::$placeholder = $placeholder;

        if (PersonImageHelper::placeholder() !== ''
            || PersonImageHelper::resolve('javascript:alert(1)') !== '') {
            throw new \RuntimeException('Unsafe image placeholder was accepted.');
        }
    }

    foreach ([
        "2026-11-17 12:00:00\n",
        "2026-11-17\0",
        "\t2026-11-17",
    ] as $badDate) {
        if (SportsManagementDateHelper::getTimestamp($badDate) !== 0) {
            throw new \RuntimeException('Administrator accepted malformed timestamp input.');
        }

        if (CalendarFunctions::events($badDate) !== CalendarFunctions::events('')) {
            throw new \RuntimeException('Calendar did not fall back for malformed date input.');
        }
    }

    echo "Joomla 5/6 image selection, administrator and calendar guards passed.\n";
}
