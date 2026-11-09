<?php
/**
 * Standalone Joomla 5/6 timezone chooser regression tests.
 *
 * Usage: php .github/tests/joomla56-timezone-options.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Form\Field {
    /** Minimal Joomla list field with an inherited placeholder option. */
    class ListField
    {
        /**
         * Return inherited options used to verify that timezones are appended.
         *
         * @return array<int,object> Parent field placeholder.
         */
        protected function getOptions(): array
        {
            return [(object) ['value' => '__parent__', 'text' => 'Parent option']];
        }
    }
}

namespace {
    \defined('_JEXEC') || \define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/admin/src/Field/GoogletimezonesField.php';

    use Diddipoeler\Component\SportsManagement\Administrator\Field\GoogletimezonesField;

    $field = new GoogletimezonesField();
    $format = new \ReflectionMethod(GoogletimezonesField::class, 'formatOffset');

    foreach ([
        -19800 => '(UTC-05:30)',
        -3600 => '(UTC-01:00)',
        0 => '(UTC+00:00)',
        19800 => '(UTC+05:30)',
        20700 => '(UTC+05:45)',
    ] as $offset => $expected) {
        if ($format->invoke($field, $offset) !== $expected) {
            throw new \RuntimeException('Unexpected timezone offset label: ' . $offset);
        }
    }

    $options = (new \ReflectionMethod(GoogletimezonesField::class, 'getOptions'))->invoke($field);

    if (count($options) < 3 || $options[0]->value !== '__parent__'
        || $options[1]->value !== '') {
        throw new \RuntimeException('Inherited Joomla timezone selector options were lost.');
    }

    $utcFound = false;

    foreach ($options as $option) {
        if ($option->value === 'UTC') {
            $utcFound = true;

            if (!str_starts_with($option->text, '(UTC+00:00)')) {
                throw new \RuntimeException('UTC timezone was formatted incorrectly.');
            }
        }
    }

    if (!$utcFound) {
        throw new \RuntimeException('UTC timezone option is missing.');
    }

    echo "Joomla 5/6 timezone chooser regressions passed.\n";
}
