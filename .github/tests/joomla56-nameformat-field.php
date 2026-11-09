<?php
/**
 * Regression test for Joomla 5/6 name-format FormField setup compatibility.
 *
 * Usage: php .github/tests/joomla56-nameformat-field.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Form\Field {
    /** Minimal Joomla list field preserving the typed setup contract. */
    class ListField
    {
        /**
         * Match Joomla FormField's boolean XML setup return contract.
         *
         * @param \SimpleXMLElement $element Form field XML configuration.
         * @param mixed $value Persisted field value.
         * @param string|null $group Optional group name.
         * @return bool Whether the supplied XML field was accepted.
         */
        public function setup(\SimpleXMLElement $element, $value, $group = null): bool
        {
            return true;
        }

        /**
         * Return a standard inherited placeholder option.
         *
         * @return array<int,object> Base Joomla form options.
         */
        protected function getOptions(): array
        {
            return [(object) ['value' => '', 'text' => 'Select format']];
        }
    }
}

namespace Joomla\CMS\Language {
    /** Translation stub for name-format option labels. */
    final class Text
    {
        /**
         * Keep the language key as the visible name.
         *
         * @param string $key Person-name format translation key.
         * @return string Original translation key.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Service {
    /** Isolated application resolver for administrator language setup. */
    final class SportsManagementAdministratorApplicationResolver
    {
        /**
         * Provide an application with a stub Joomla language object.
         *
         * @return object Administrator application facade.
         */
        public static function resolve(): object
        {
            return new class {
                /**
                 * Return the minimal language loader for format options.
                 *
                 * @return object Translation loader stub.
                 */
                public function getLanguage(): object
                {
                    return new class {
                        /**
                         * Pretend language files were loaded successfully.
                         *
                         * @param mixed ...$args Joomla language loading arguments.
                         * @return bool Whether loading succeeded.
                         */
                        public function load(mixed ...$args): bool { return true; }

                        /**
                         * Return a stable language fallback identifier.
                         *
                         * @return string Default language tag.
                         */
                        public function getDefault(): string { return 'en-GB'; }
                    };
                }
            };
        }
    }
}

namespace {
    \defined('_JEXEC') || \define('_JEXEC', 1);
    \defined('JPATH_ADMINISTRATOR') || \define('JPATH_ADMINISTRATOR', '/tmp/testadministrator');

    require_once dirname(__DIR__, 2) . '/admin/src/Field/NameformatField.php';

    use Diddipoeler\Component\SportsManagement\Administrator\Field\NameformatField;

    $setup = new \ReflectionMethod(NameformatField::class, 'setup');
    $return = $setup->getReturnType();

    if (!$return instanceof \ReflectionNamedType || $return->getName() !== 'bool') {
        throw new \RuntimeException('NameformatField::setup must return bool for Joomla 5/6.');
    }

    if (class_exists('SimpleXMLElement')) {
        $xml = new \SimpleXMLElement('<field />');
        if ((new NameformatField())->setup($xml, '0') !== true
            || (string) $xml['class'] !== 'inputbox'
            || (string) $xml['size'] !== '1') {
            throw new \RuntimeException('Nameformat XML field setup contract changed.');
        }
    }

    $options = (new \ReflectionMethod(NameformatField::class, 'getOptions'))
        ->invoke(new NameformatField());

    if (count($options) !== 20 || $options[0]->value !== ''
        || $options[1]->value !== '0' || $options[19]->value !== '18') {
        throw new \RuntimeException('Joomla name format option IDs changed unexpectedly.');
    }

    echo "Joomla 5/6 name-format field compatibility passed.\n";
}
