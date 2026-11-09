<?php
/**
 * Isolated Joomla 5/6 Google color field value-validation tests.
 *
 * Run: php .github/tests/joomla56-googlecolorchooser-field.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Form {
    /** Minimal form field for native color chooser rendering. */
    class FormField
    {
        /** @var mixed Stored color preference. */
        protected mixed $value;
        protected string $name = 'theme_color';
        protected string $id = 'theme_color';

        /**
         * Initialize the raw Joomla color setting under test.
         *
         * @param mixed $value Potentially malformed saved setting.
         */
        public function __construct(mixed $value)
        {
            $this->value = $value;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Service {
    /** Avoid asset registration when running without Joomla's HTML document. */
    final class SportsManagementAdministratorApplicationResolver
    {
        /**
         * Return an application stub without a Joomla HTML document.
         *
         * @return object Application with a non-HTML document.
         */
        public static function resolve(): object
        {
            return new class {
                /**
                 * Provide a non-HTML response for isolated field rendering.
                 *
                 * @return object Synthetic document placeholder.
                 */
                public function getDocument(): object
                {
                    return new \stdClass();
                }
            };
        }
    }
}

namespace {
    \defined('_JEXEC') || \define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/admin/src/Field/GooglecolorchooserField.php';

    use Diddipoeler\Component\SportsManagement\Administrator\Field\GooglecolorchooserField;

    foreach ([[], new \stdClass(), null, false, 42, '#12345', 'red', '#123456<script>'] as $invalid) {
        $html = (new GooglecolorchooserField($invalid))->getInput();

        if (!str_contains($html, 'value=""') || str_contains($html, '<script>')) {
            throw new \RuntimeException('Invalid or nested color setting was not rejected.');
        }
    }

    $valid = (new GooglecolorchooserField('#aBcD12'))->getInput();

    if (!str_contains($valid, 'value="#aBcD12"')
        || !str_contains($valid, 'background-color:#aBcD12')
        || !str_contains($valid, 'data-target="theme_color"')) {
        throw new \RuntimeException('Valid Google color selection was not retained.');
    }

    echo "Joomla 5/6 Google color field regressions passed.\n";
}
