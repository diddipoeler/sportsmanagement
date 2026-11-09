<?php
/**
 * Standalone Joomla 5/6 administrator form output escaping regression tests.
 *
 * Usage: php .github/tests/joomla56-admin-message-escaping.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Form {
    /** Minimal Joomla field facade that holds XML-style attribute data. */
    class FormField
    {
        /** @var array<string,string> Field attributes to be rendered. */
        protected array $element = [];

        /** Whether language substitution is enabled on labels. */
        protected bool $translateLabel = true;

        /**
         * Provide synthetic field attributes without Joomla's XML loader.
         *
         * @param array<string,string> $attributes Field display settings.
         * @return void
         */
        public function __construct(array $attributes = [])
        {
            $this->element = $attributes;
        }
    }
}

namespace Joomla\CMS\Language {
    /** Deterministic translation stub preserving HTML-shaped test strings. */
    final class Text
    {
        /**
         * Return the translation key without filtering markup.
         *
         * @param string $key Test translation value.
         * @return string Unmodified translation result.
         */
        public static function _(string $key): string { return $key; }
    }
}

namespace Joomla\CMS\HTML {
    /** Minimal static helper for optional title icons. */
    final class HTMLHelper
    {
        /**
         * Return a placeholder for a Joomla resource registration.
         *
         * @param mixed ...$arguments Joomla helper arguments.
         * @return string Empty markup.
         */
        public static function _(mixed ...$arguments): string { return ''; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Service {
    /** Application resolver stub for the extension-message field. */
    final class SportsManagementAdministratorApplicationResolver
    {
        /**
         * Return a synthetic admin application for retrieving translations.
         *
         * @return object Application-like object with a language service.
         */
        public static function resolve(): object
        {
            return new class {
                /**
                 * Supply an isolated language service.
                 *
                 * @return object Language stub with a successful load operation.
                 */
                public function getLanguage(): object
                {
                    return new class {
                        /**
                         * Pretend Joomla language files have been loaded.
                         *
                         * @param mixed ...$arguments Language context.
                         * @return bool Success in the test environment.
                         */
                        public function load(mixed ...$arguments): bool { return true; }
                    };
                }
            };
        }
    }
}

namespace {
    \defined('_JEXEC') || \define('_JEXEC', 1);
    \defined('JPATH_SITE') || \define('JPATH_SITE', '/test/site');

    require_once dirname(__DIR__, 2) . '/admin/src/Field/JsmmessageField.php';
    require_once dirname(__DIR__, 2) . '/admin/src/Field/ExtensionmessageField.php';
    require_once dirname(__DIR__, 2) . '/admin/src/Field/JsmtitleField.php';

    use Diddipoeler\Component\SportsManagement\Administrator\Field\JsmmessageField;
    use Diddipoeler\Component\SportsManagement\Administrator\Field\ExtensionmessageField;
    use Diddipoeler\Component\SportsManagement\Administrator\Field\JsmtitleField;

    /**
     * Invoke a protected Joomla field rendering method in isolation.
     *
     * @param object $field Instantiated display-only form field.
     * @param string $method Name of the rendering method.
     * @return string Generated field HTML.
     */
    function smRenderAdminField(object $field, string $method): string
    {
        return (new \ReflectionMethod($field, $method))->invoke($field);
    }

    $malicious = '<script>alert("bad")</script>';
    $plain = new JsmmessageField([
        'style' => 'example',
        'label' => $malicious,
        'text' => $malicious,
    ]);
    $plainHtml = smRenderAdminField($plain, 'getInput');

    if (str_contains($plainHtml, '<script>')
        || substr_count($plainHtml, '&lt;script&gt;') !== 2) {
        throw new \RuntimeException('JSM message field rendered unescaped translations.');
    }

    $extension = new ExtensionmessageField([
        'label' => $malicious,
    ]);
    (new \ReflectionProperty(ExtensionmessageField::class, 'message'))->setValue($extension, $malicious);
    $extensionHtml = smRenderAdminField($extension, 'getInput');

    if (str_contains($extensionHtml, '<script>')
        || substr_count($extensionHtml, '&lt;script&gt;') !== 2) {
        throw new \RuntimeException('Extension message field rendered unescaped text.');
    }

    foreach (['javascript:alert(1)', '//host.test/image.png', '../secrets.png'] as $unsafe) {
        $field = new JsmtitleField(['imagesrc' => $unsafe, 'title' => 'Title']);
        if (str_contains(smRenderAdminField($field, 'getLabel'), '<img')) {
            throw new \RuntimeException('Title field accepted an unsafe image source.');
        }
    }

    $safe = new JsmtitleField(['imagesrc' => 'images/example.png', 'title' => $malicious]);
    $safeHtml = smRenderAdminField($safe, 'getLabel');

    if (!str_contains($safeHtml, 'src="images/example.png"')
        || !str_contains($safeHtml, '&lt;script&gt;')
        || str_contains($safeHtml, '<script>')) {
        throw new \RuntimeException('Title image source or heading escaping broke.');
    }

    echo "Joomla 5/6 administrator message output regression checks passed.\n";
}
