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
        public static function _(string $key): string
        {
            return in_array($key, [
                'COM_SPORTSMANAGEMENT_EXTENSIONLINK_FORUM_LABEL',
                'COM_SPORTSMANAGEMENT_AUTHOR_LABEL',
                'COM_SPORTSMANAGEMENT_VERSION_LABEL',
                'COM_SPORTSMANAGEMENT_TRANSLATORS_LABEL',
                'LIB_SYW_MESSAGE_EXAMPLE',
            ], true) ? '<script>alert("bad")</script>' : $key;
        }

        /**
         * Replace a translated string placeholder with a synthetic URL.
         *
         * @param string $key Translated message template.
         * @param string $value Replacement link value.
         * @return string Text prepared for HTML escaping.
         */
        public static function sprintf(string $key, string $value): string
        {
            return sprintf($key, $value);
        }
    }
}

namespace Joomla\CMS\Uri {
    /** Joomla URI facade for the extension support icon fixture. */
    final class Uri
    {
        /**
         * Return a static Joomla root URL for image asset assembly.
         *
         * @return string Fake site root address.
         */
        public static function root(): string { return 'https://example.org/'; }
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

                        /**
                         * Report a fixed Joomla language code for translation loading.
                         *
                         * @return string Administrator language tag.
                         */
                        public function getTag(): string { return 'en-GB'; }
                    };
                }
            };
        }
    }
}

namespace {
    \defined('_JEXEC') || \define('_JEXEC', 1);
    \defined('JPATH_SITE') || \define('JPATH_SITE', '/test/site');
    \defined('JPATH_ADMINISTRATOR') || \define('JPATH_ADMINISTRATOR', '/test/admin');

    require_once dirname(__DIR__, 2) . '/admin/src/Field/JsmmessageField.php';
    require_once dirname(__DIR__, 2) . '/admin/src/Field/ExtensionmessageField.php';
    require_once dirname(__DIR__, 2) . '/admin/src/Field/JsmtitleField.php';
    require_once dirname(__DIR__, 2) . '/admin/src/Field/JsmlinkField.php';
    require_once dirname(__DIR__, 2) . '/admin/src/Field/JsmsubtitleField.php';
    require_once dirname(__DIR__, 2) . '/admin/src/Field/ExtensionlinkField.php';
    require_once dirname(__DIR__, 2) . '/admin/src/Field/ExtensionauthorField.php';
    require_once dirname(__DIR__, 2) . '/admin/src/Field/ExtensionversionField.php';
    require_once dirname(__DIR__, 2) . '/admin/src/Field/ExtensiontranslatorsField.php';

    use Diddipoeler\Component\SportsManagement\Administrator\Field\JsmmessageField;
    use Diddipoeler\Component\SportsManagement\Administrator\Field\ExtensionmessageField;
    use Diddipoeler\Component\SportsManagement\Administrator\Field\JsmtitleField;
    use Diddipoeler\Component\SportsManagement\Administrator\Field\JsmlinkField;
    use Diddipoeler\Component\SportsManagement\Administrator\Field\JsmsubtitleField;
    use Diddipoeler\Component\SportsManagement\Administrator\Field\ExtensionlinkField;
    use Diddipoeler\Component\SportsManagement\Administrator\Field\ExtensionauthorField;
    use Diddipoeler\Component\SportsManagement\Administrator\Field\ExtensionversionField;
    use Diddipoeler\Component\SportsManagement\Administrator\Field\ExtensiontranslatorsField;

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

    foreach (['javascript:alert(1)', '//example.org/', '../secret', 'data:text/html,bad',
        '%2e%2e/secret', '%252e%252e/secret', '/images/%2e%2e/secret',
        '%2f%2fevil.example/test', 'images/%5csecret'] as $badLink) {
        $linkField = new JsmlinkField(['title' => 'Support', 'link' => $badLink]);
        $linkHtml = smRenderAdminField($linkField, 'getLabel');

        if (str_contains($linkHtml, '<a ')) {
            throw new \RuntimeException('Unsafe administrator resource link accepted: ' . $badLink);
        }
    }

    $linkField = new JsmlinkField([
        'title' => 'Support',
        'link' => 'https://example.org/help',
        'text' => '<script>bad</script> %s',
    ]);
    $validLink = smRenderAdminField($linkField, 'getLabel');
    $linkText = smRenderAdminField($linkField, 'getInput');

    if (!str_contains($validLink, 'href="https://example.org/help"')
        || str_contains($linkText, '<script>')
        || !str_contains($linkText, '&lt;script&gt;')) {
        throw new \RuntimeException('Resource links or translated descriptions are not escaped correctly.');
    }

    $subtitle = new JsmsubtitleField(['title' => $malicious]);
    $subtitleHtml = smRenderAdminField($subtitle, 'getLabel');

    if (str_contains($subtitleHtml, '<script>') || !str_contains($subtitleHtml, '&lt;script&gt;')) {
        throw new \RuntimeException('Administrator subtitle did not escape translated text.');
    }

    $supportLink = new ExtensionlinkField([
        'linktype' => 'forum',
        'description' => '<script>alert("bad")</script> %s',
        'link' => 'https://example.org/help',
    ]);
    $supportLabel = smRenderAdminField($supportLink, 'getLabel');
    $supportDescription = smRenderAdminField($supportLink, 'getInput');

    if (str_contains($supportLabel, '<script>')
        || !str_contains($supportLabel, '&lt;script&gt;')
        || str_contains($supportDescription, '<script>')
        || !str_contains($supportDescription, '&lt;script&gt;')) {
        throw new \RuntimeException('Extension links rendered unescaped translation markup.');
    }

    foreach (['%2e%2e/secret', '%252e%252e/secret', '/files/%2e%2e/secret',
        '%2f%2fexample.org', 'images/%5csecret'] as $badLink) {
        $blocked = new ExtensionlinkField([
            'linktype' => 'forum',
            'description' => 'Visit %s',
            'link' => $badLink,
        ]);
        $blockedHtml = smRenderAdminField($blocked, 'getInput');

        if (str_contains($blockedHtml, $badLink)) {
            throw new \RuntimeException('Encoded extension traversal reached the administrator output.');
        }
    }

    $unsupportedLink = new ExtensionlinkField([
        'linktype' => 'forum',
        'description' => 'Visit %s',
        'link' => 'javascript:alert(1)',
    ]);
    $unsafeHtml = smRenderAdminField($unsupportedLink, 'getInput');

    if (str_contains($unsafeHtml, 'javascript:') || !str_contains($unsafeHtml, 'Visit')) {
        throw new \RuntimeException('Unsafe extension link reached displayed help text.');
    }

    $example = new ExtensionmessageField();
    (new \ReflectionProperty(ExtensionmessageField::class, 'messageType'))
        ->setValue($example, 'example');
    $exampleLabel = smRenderAdminField($example, 'getLabel');

    if (str_contains($exampleLabel, '<script>')
        || !str_contains($exampleLabel, '&lt;script&gt;')) {
        throw new \RuntimeException('Example field caption was not escaped.');
    }

    // Labels are translated through Joomla and must never become raw HTML.
    foreach ([new ExtensionauthorField(), new ExtensionversionField()] as $field) {
        $label = smRenderAdminField($field, 'getLabel');

        if (str_contains($label, '<script>') || !str_contains($label, '&lt;script&gt;')) {
            throw new \RuntimeException('Extension field caption was not HTML-escaped.');
        }
    }

    $translators = new ExtensiontranslatorsField();
    (new \ReflectionProperty(ExtensiontranslatorsField::class, 'translators'))
        ->setValue($translators, 'Translation credits');
    $translatedLabel = smRenderAdminField($translators, 'getLabel');

    if (str_contains($translatedLabel, '<script>')
        || !str_contains($translatedLabel, '&lt;script&gt;')) {
        throw new \RuntimeException('Extension translator caption was not escaped.');
    }

    echo "Joomla 5/6 administrator message output regression checks passed.\n";
}
