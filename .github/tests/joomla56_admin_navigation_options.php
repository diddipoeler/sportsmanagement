<?php
/**
 * Joomla 5/6 administrator navigation field unique-value regressions.
 *
 * Checks that native menu choices are exposed once while existing Joomla
 * XML-defined options and their translated labels remain unchanged.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Form\Field {
    class ListField
    {
        /**
         * Supply XML-defined menu options that overlap with native choices.
         *
         * @return array<int,object> Existing Joomla XML field entries.
         */
        protected function getOptions(): array
        {
            return [
                (object) ['value' => 'matrix', 'text' => 'Custom matrix label'],
                (object) ['value' => 'results', 'text' => 'Custom results label'],
                (object) ['value' => 'custom_xml_view', 'text' => 'Custom XML menu'],
            ];
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return deterministic translations for the isolated Joomla options.
         *
         * @param string $key Joomla language key.
         * @return string Same untranslated key.
         */
        public static function _(string $key): string { return $key; }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Field/JsmmenuitemsField.php';

    $field = new \Diddipoeler\Component\SportsManagement\Administrator\Field\JsmmenuitemsField();
    $method = new \ReflectionMethod($field, 'getOptions');

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $options = $method->invoke($field);
        $values = array_map(static fn (object $entry): string => (string) $entry->value, $options);

        if (count($values) !== count(array_unique($values))
            || ($options[0]->value ?? null) !== 'matrix'
            || ($options[0]->text ?? null) !== 'Custom matrix label'
            || ($options[1]->value ?? null) !== 'results'
            || ($options[1]->text ?? null) !== 'Custom results label'
            || !in_array('jltournamenttree', $values, true)
            || !in_array('teamstree', $values, true)
            || !in_array('custom_xml_view', $values, true)) {
            throw new \RuntimeException('Administrator navigation options lost XML priority or contain duplicates.');
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 administrator navigation field XML option priority OK\n";
}
