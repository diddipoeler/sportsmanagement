<?php
/**
 * Verify Joomla 5/6 image selectors use the backend app and handle missing documents.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}

namespace Joomla\CMS {
    final class Factory
    {
        public static ?\Joomla\CMS\Application\CMSApplicationInterface $application = null;

        /**
         * Return the active fake Joomla client for each selector test.
         *
         * @return \Joomla\CMS\Application\CMSApplicationInterface The application currently rendering a field.
         */
        public static function getApplication(): \Joomla\CMS\Application\CMSApplicationInterface
        {
            return self::$application ?? throw new \RuntimeException('Missing Joomla application.');
        }
    }
}

namespace Joomla\CMS\Document {
    final class HtmlDocument
    {
        public array $scripts = [];

        /**
         * Return the fake document WebAssetManager for inline field scripts.
         *
         * @return object Recording script registration interface.
         */
        public function getWebAssetManager(): object
        {
            return new class($this) {
                /**
                 * Construct a recording asset manager for a single HTML document.
                 *
                 * @param HtmlDocument $document Destination document.
                 */
                public function __construct(private HtmlDocument $document) {}

                /**
                 * Store the selector's inline script for later assertions.
                 *
                 * @param string $script Generated JavaScript code.
                 * @return void
                 */
                public function addInlineScript(string $script): void
                {
                    $this->document->scripts[] = $script;
                }
            };
        }
    }
}

namespace Joomla\CMS\Component {
    final class ComponentHelper
    {
        /**
         * Return default component parameters for modal and image placeholders.
         *
         * @param string $component Name of the component.
         * @return object Registry-compatible parameters object.
         */
        public static function getParams(string $component): object
        {
            return new class {
                /**
                 * Return an unchanged fallback parameter when no setting exists.
                 *
                 * @param string $name Component setting name.
                 * @param mixed $default Default value.
                 * @return mixed Provided fallback value.
                 */
                public function get(string $name, mixed $default = null): mixed
                {
                    return $default;
                }
            };
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Pass through language keys in this lightweight Joomla test.
         *
         * @param string $key Joomla language constant.
         * @return string Same value, to inspect the generated markup.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\CMS\Uri {
    final class Uri
    {
        public static string $base = 'https://example.test/administrator/';

        /**
         * Return a fixed installation root for URL generation.
         *
         * @param bool $pathOnly Whether to return only the site path.
         * @return string URL root or empty base path.
         */
        public static function root(bool $pathOnly = false): string
        {
            return $pathOnly ? '' : 'https://example.test/';
        }

        /**
         * Return the active client base URL.
         *
         * @return string Administrator or site base address.
         */
        public static function base(): string
        {
            return self::$base;
        }

        /**
         * Convert modal URL parameters into an encoded query string.
         *
         * @param array $query Modal query fields.
         * @return string RFC3986 query for the image handler.
         */
        public static function buildQuery(array $query): string
        {
            return http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }
    }
}

namespace Joomla\CMS\Form {
    abstract class FormField
    {
        public string $name = 'jform[picture]';
        public string $id = 'jform_picture';
        public string $value = 'images/com_sportsmanagement/database/leagues/test.png';
        public array $element = ['targetfolder' => 'leagues'];

        abstract protected function getInput(): string;
    }
}

namespace Joomla\CMS\Log {
    final class Log {}
}

namespace Joomla\Filesystem {
    final class File {}
}

namespace Diddipoeler\Component\SportsManagement\Site\Helper {
    final class ModalImageHelper
    {
        public static array $links = [];

        /**
         * Record the modal link generated by the shared image selector.
         *
         * @param string $id Button modal ID.
         * @param string $icon Button icon path.
         * @param string $title Button tooltip.
         * @param int $size Icon height.
         * @param string $url Dialog URL.
         * @param int $width Modal width.
         * @param int $height Modal height.
         * @param int $mode Modal presentation mode.
         * @param string $schemaAttribute Extra attribute name.
         * @param string $schemaValue Extra attribute value.
         * @return string Minimal test button.
         */
        public static function render(
            string $id, string $icon, string $title, int $size, string $url,
            int $width, int $height, int $mode, string $schemaAttribute,
            string $schemaValue
        ): string {
            self::$links[] = $url;
            return '<button data-dialog="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"></button>';
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Site\Helper\ImageSelectHelper;
    use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
    use Joomla\CMS\Document\HtmlDocument;
    use Joomla\CMS\Factory;
    use Joomla\CMS\Uri\Uri;

    define('_JEXEC', 1);

    final class FakeInput
    {
        /**
         * Supply an expected command selector for the current test form.
         *
         * @param string $key Input command name.
         * @param string $default Default identifier.
         * @return string Input field value.
         */
        public function getCmd(string $key, string $default = ''): string
        {
            return $key === 'view' ? 'league' : $default;
        }
    }

    final class FakeApplication implements \Joomla\CMS\Application\CMSApplicationInterface
    {
        public FakeInput $input;

        /**
         * Set the current client and optional Joomla document for the test.
         *
         * @param string $client Joomla client, site or administrator.
         * @param mixed $document HTML document or null before its creation.
         */
        public function __construct(public string $client, public mixed $document)
        {
            $this->input = new FakeInput();
        }

        /**
         * Check which Joomla client is rendering the image field.
         *
         * @param string $client Target client name.
         * @return bool True for the active client.
         */
        public function isClient(string $client): bool
        {
            return $this->client === $client;
        }

        /**
         * Return Joomla's current fake input object.
         *
         * @return FakeInput Application request inputs.
         */
        public function getInput(): FakeInput
        {
            return $this->input;
        }

        /**
         * Return the current document or null to reproduce the reported error.
         *
         * @return mixed HTML document when prepared; null during early form setup.
         */
        public function getDocument(): mixed
        {
            return $this->document;
        }

        /**
         * Return a harmless user-state default for the modal title.
         *
         * @param string $key Joomla user state identifier.
         * @param mixed $default Fallback value.
         * @return mixed Same fallback if no state was supplied.
         */
        public function getUserState(string $key, mixed $default = null): mixed
        {
            return $default;
        }
    }

    /**
     * Fail with an explanatory message if a selector regression appears.
     *
     * @param bool $condition Condition expected by the regression.
     * @param string $message Failure explanation.
     * @return void
     */
    function assertImageSelector(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    require dirname(__DIR__, 2) . '/site/src/Helper/ImageSelectHelper.php';

    // Explicitly mapped folders retain their historical storage locations.
    foreach ([
        'leagues' => 'leagues',
        'clubs/small' => 'clubs/small',
        'projectteams_trikot_home' => 'projectteams/trikot_home',
        'additional_event' => 'events/additional_event',
        'custom/subtype' => 'events/custom/subtype',
    ] as $type => $expected) {
        assertImageSelector(ImageSelectHelper::getFolder($type) === $expected, 'Image folder mapping changed: ' . $type);
    }

    // Untrusted type fields must not yield filesystem traversal or trigger
    // PHP 8 array-to-string warnings in image upload/delete controllers.
    foreach ([
        '', '..', '../secret', '../../administrator', 'a/../../b',
        'a//b', 'a/./b', 'a\\b', "invalid\nname",
        str_repeat('a', 129), 'a/b/c/d/e', [], new \stdClass(), false,
    ] as $invalidType) {
        try {
            ImageSelectHelper::getFolder($invalidType);
            throw new \RuntimeException('Unsafe image folder was accepted.');
        } catch (\InvalidArgumentException $exception) {
            assertImageSelector($exception->getCode() === 400, 'Unsafe folder was not rejected as invalid input.');
        }
    }

    // Reproduces a backend form before a document is attached: previously
    // method_exists(null, 'getWebAssetManager') threw a PHP 8 TypeError.
    Factory::$application = new FakeApplication('administrator', null);
    $output = ImageSelectHelper::getSelector('jform[picture]', '', 'leagues', 'logo.png', '', '', 'jform_picture');

    assertImageSelector(str_contains($output, 'jsm-image-select'), 'Backend rendering failed with a missing document.');
    assertImageSelector(count(ModalImageHelper::$links) === 2, 'Expected image upload and browser actions.');
    assertImageSelector(
        str_starts_with(ModalImageHelper::$links[0], 'https://example.test/administrator/index.php?'),
        'Backend upload modal does not use the administrator base URL.'
    );
    assertImageSelector(
        str_contains(ModalImageHelper::$links[1], 'view=imagelist'),
        'Backend image browser dialog was lost.'
    );

    $document = new HtmlDocument();
    Factory::$application = new FakeApplication('administrator', $document);
    $field = new ImageSelectHelper();
    assertImageSelector(ImageSelectHelper::$_view === 'league', 'Image selector constructor used a different application.');

    ImageSelectHelper::getSelector('jform[picture]', '', 'leagues', 'logo.png', '', '', 'jform_picture');
    assertImageSelector(count($document->scripts) === 1, 'Backend HTML document did not receive the inline script.');
    assertImageSelector(
        str_contains($document->scripts[0], 'selectImage_leagues'),
        'League callback is missing from the backend document.'
    );

    // Also verify which entry point is loaded first. PHP's class names are
    // case-insensitive, so the bridge must forward to the canonical class.
    $compatibilityField = in_array('--compat-field', $argv, true);
    $fieldClass = $compatibilityField
        ? \Diddipoeler\Component\SportsManagement\Administrator\Field\ImageselectField::class
        : \Diddipoeler\Component\SportsManagement\Administrator\Field\ImageSelectField::class;
    require dirname(__DIR__, 2) . '/admin/src/Field/'
        . ($compatibilityField ? 'ImageselectField.php' : 'ImageSelectField.php');

    // Loading both spellings in the same PHP process must never redeclare
    // the case-insensitive ImageSelectField class.
    require_once dirname(__DIR__, 2) . '/admin/src/Field/ImageSelectField.php';
    require_once dirname(__DIR__, 2) . '/admin/src/Field/ImageselectField.php';
    assertImageSelector(
        class_exists(\Diddipoeler\Component\SportsManagement\Administrator\Field\ImageSelectField::class, false)
        && class_exists(\Diddipoeler\Component\SportsManagement\Administrator\Field\ImageselectField::class, false),
        'Legacy and canonical image select class names must resolve together.'
    );

    $field = new $fieldClass();
    $html = (new \ReflectionMethod($field, 'getInput'))->invoke($field);
    assertImageSelector(str_contains($html, 'jform_picture_preview'), 'Administrator field preview is missing.');
    assertImageSelector(str_contains($html, 'name="jform[picture]"'), 'Administrator image field is not submittable.');
    assertImageSelector(str_contains($html, 'name="original_jform[picture]"'), 'Original image state must be submitted.');
    assertImageSelector(str_contains($html, 'name="copy_jform[picture]"'), 'Copied image state must be submitted.');

    Uri::$base = 'https://example.test/';
    Factory::$application = new FakeApplication('site', new HtmlDocument());
    ModalImageHelper::$links = [];

    ImageSelectHelper::getSelector('jform[picture]', '', 'leagues', '', '', '', 'frontend_picture');
    assertImageSelector(
        str_starts_with(ModalImageHelper::$links[0], 'https://example.test/administrator/index.php?'),
        'Frontend image upload modal no longer points to the administrator image handler.'
    );

    Factory::$application = new FakeApplication('api', null);
    try {
        ImageSelectHelper::getSelector();
        throw new \RuntimeException('API client should not render an HTML image selector.');
    } catch (\RuntimeException $exception) {
        assertImageSelector(
            str_contains($exception->getMessage(), 'requires a Joomla site or administrator'),
            'Unsupported client did not fail safely.'
        );
    }

    echo "Joomla 5/6 administrator image select: null document, active backend and frontend controls OK.\n";
}
