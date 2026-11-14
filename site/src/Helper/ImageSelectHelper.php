<?php
/**
 * Joomla 5/6 SportsManagement site helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\File;

/**
 * Joomla 5/6 image-select runtime used by the legacy-compatible form field.
 *
 * The public methods and static properties intentionally preserve the historic
 * ImageSelectSM API while using only namespaced Joomla/component helpers.
 */
final class ImageSelectHelper
{
    public static string $_foldertype = '';
    public static string $_view = '';

    /**
     * Initialise the legacy image selector state from the active Joomla request.
     *
     * Reads the requested image folder type and view from the actual frontend
     * or administrator application. This prevents backend fields from
     * accidentally selecting a separate frontend application with no document.
     *
     * @return void
     * @throws \RuntimeException If the current Joomla client is unsupported.
     */
    public function __construct()
    {
        $input = self::currentApplication()->getInput();
        self::$_foldertype = $input->getCmd('type', '');
        self::$_view = $input->getCmd('view', '');
    }

    /**
     * Render the complete image-select field and its upload/select modal actions.
     *
     * Resolves component dialog dimensions and placeholders, builds safe field
     * and image URLs, and registers inline JavaScript on the current HTML
     * document. Both backend league forms and frontend forms use the actual
     * running application rather than an unrelated site application. When no
     * HTML document is available, it returns the field without causing a
     * method_exists(null) TypeError.
     *
     * @param mixed $fieldname Submitted image field name.
     * @param mixed $fieldpreviewName Historical preview field identifier.
     * @param mixed $type Image folder selection (such as leagues).
     * @param mixed $value Currently stored image path.
     * @param mixed $default Initial placeholder or default image.
     * @param mixed $controlName Historical Joomla form control name.
     * @param mixed $fieldid HTML input element identifier.
     * @return string Escaped field markup and modal actions.
     * @throws \RuntimeException If no supported Joomla application is active.
     */
    public static function getSelector(
        $fieldname = '',
        $fieldpreviewName = '',
        $type = '',
        $value = '',
        $default = '',
        $controlName = '',
        $fieldid = ''
    ): string {
        $app = self::currentApplication();
        $params = ComponentHelper::getParams('com_sportsmanagement');
        $modalHeight = (int) $params->get('modal_popup_height', 600);
        $modalWidth = (int) $params->get('modal_popup_width', 900);
        $prefix = '';

        if ($app->isClient('site')) {
            $prefix = 'administrator/';
            $modalHeight = 500;
        }

        self::$_foldertype = (string) $type;
        self::$_view = $app->getInput()->getCmd('view', self::$_view);

        $folder = self::getFolder((string) $type);
        $root = rtrim((string) Uri::root(), '/') . '/';
        $fieldName = (string) $fieldname;
        $fieldId = (string) $fieldid;
        $functionSuffix = preg_replace('/[^A-Za-z0-9_]/', '_', $fieldId) ?: 'image';
        $selectFunction = 'selectImage_' . (string) $type;
        $defaultPlaceholder = self::defaultPlaceholder((string) $type);
        $malePlaceholder = self::defaultPlaceholder('mensmall');
        $femalePlaceholder = self::defaultPlaceholder('womansmall');
        $imageBase = 'images/com_sportsmanagement/database/' . trim($folder, '/') . '/';

        $js = self::buildJavascript(
            $selectFunction,
            $functionSuffix,
            $fieldName,
            $fieldId,
            $root,
            $imageBase,
            $defaultPlaceholder,
            $malePlaceholder,
            $femalePlaceholder
        );

        // The form may be initialised before a document exists. Never inspect
        // a null document or use the obsolete global frontend document here.
        $document = $app->getDocument();

        if ($document instanceof HtmlDocument) {
            $document->getWebAssetManager()->addInlineScript($js);
        }

        $layout = (int) $params->get('cfg_draganddrop', 0) ? 'uploaddraganddrop' : 'upload';
        $uploadLink = $prefix . 'index.php?' . Uri::buildQuery([
            'option' => 'com_sportsmanagement',
            'view' => 'imagehandler',
            'layout' => $layout,
            'type' => (string) $type,
            'field' => $fieldName,
            'fieldid' => $fieldId,
            'tmpl' => 'component',
            'pid' => 0,
            'mid' => 0,
            'imagelist' => '',
        ]);
        $selectLink = $prefix . 'index.php?' . Uri::buildQuery([
            'option' => 'com_sportsmanagement',
            'view' => 'imagelist',
            'imagelist' => 1,
            'asset' => 'com_sportsmanagement',
            'folder' => $folder,
            'author' => '',
            'fieldid' => $fieldId,
            'tmpl' => 'component',
            'type' => (string) $type,
            'fieldname' => $fieldName,
        ]);

        $uploadButton = self::modalButton(
            'upload' . $functionSuffix,
            'images/com_sportsmanagement/database/jl_images/up.png',
            Text::_('JLIB_HTML_BEHAVIOR_UPLOADER_CURRENT_TITLE'),
            Uri::base() . $uploadLink,
            $modalWidth,
            $modalHeight
        );
        $selectButton = self::modalButton(
            'select' . $functionSuffix,
            'images/com_sportsmanagement/database/jl_images/zoom.png',
            Text::_('JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE') . ' '
                . $app->getUserState('com_sportsmanagement.itemname', ''),
            Uri::base() . $selectLink,
            $modalWidth,
            $modalHeight
        );

        $escapedFieldId = self::escape($fieldId);
        $escapedFieldName = self::escape($fieldName);
        $escapedValue = self::escape((string) $value);
        $escapedResetTitle = self::escape(Text::_('JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE'));
        $escapedClearTitle = self::escape(Text::_('JCLEAR'));

        return '<div class="jsm-image-select">'
            . '<input class="form-control" type="text" id="' . $escapedFieldId . '" value="'
            . $escapedValue . '" disabled="disabled" />'
            . '<div class="jsm-image-select-actions">'
            . $uploadButton
            . $selectButton
            . '<a class="btn btn-primary" title="' . $escapedResetTitle . '" href="#" '
            . 'onclick="reset_' . self::escape($functionSuffix) . '(); return false;">'
            . self::escape(Text::_('JRESET')) . '</a>'
            . '<a class="btn btn-primary" title="' . $escapedClearTitle . '" href="#" '
            . 'onclick="clear_' . self::escape($functionSuffix) . '(); return false;">'
            . self::escape(Text::_('JCLEAR')) . '</a>'
            . '</div>'
            . '<input type="hidden" id="a_' . $escapedFieldName . '" name="' . $escapedFieldName
            . '" value="' . $escapedValue . '" />'
            . '</div>';
    }

    /**
     * Convert historical image selector types to component image directories.
     *
     * Supports the established league, club, person, team and placeholder
     * categories, plus explicit home/away club-kit editor directories.
     * Preserves the historical shared clubs/trikot aliases and a validated
     * legacy events subdirectory fallback for unmapped safe names. Rejects
     * malformed request values before image upload/delete path handling.
     *
     * @param mixed $type Legacy image folder type.
     * @return string Component-relative image directory.
     * @throws \InvalidArgumentException For invalid or unsafe folder values.
     */
    public static function getFolder($type): string
    {
        // Reject arrays, objects and booleans before casting to a folder name.
        // PHP 8 raises warnings for arrays and rejects many object conversions.
        if (!is_string($type) && !is_int($type)) {
            throw new \InvalidArgumentException('Invalid SportsManagement image folder type.', 400);
        }

        $type = (string) $type;

        return match ($type) {
            'clubs_small', 'clubssmall', 'clubs/small' => 'clubs/small',
            'clubs_medium', 'clubsmedium', 'clubs/medium' => 'clubs/medium',
            'clubs_large', 'clubslarge', 'clubs/large' => 'clubs/large',
            'clubs_trikot_home', 'clubs_trikot_away' => 'clubs/trikot',
            // Distinct club editor image directories. New aliases preserve
            // the existing legacy clubs/trikot mapping for older selectors.
            'clubkits_home' => 'clubs/trikot_home',
            'clubkits_away' => 'clubs/trikot_away',
            'flags' => 'flags',
            'flags_associations' => 'flags_associations',
            'flag_maps' => 'flag_maps',
            'associations' => 'associations',
            'events' => 'events',
            'leagues' => 'leagues',
            'divisions' => 'divisions',
            'persons' => 'persons',
            'projectreferee' => 'projectreferees',
            'playgrounds' => 'playgrounds',
            'positions' => 'positions',
            'projects' => 'projects',
            'projectteams' => 'projectteams',
            'projectteams_trikot_home' => 'projectteams/trikot_home',
            'projectteams_trikot_away' => 'projectteams/trikot_away',
            'seasons' => 'seasons',
            'sport_types' => 'sport_types',
            'statistics' => 'statistics',
            'teamplayers' => 'teamplayers',
            'teams' => 'teams',
            'teamstaffs' => 'teamstaffs',
            'venues' => 'venues',
            'rounds' => 'rounds',
            'rosterground' => 'rosterground',
            'agegroups' => 'agegroups',
            'projectimages' => 'projectimages',
            'matchreport' => 'matchreport',
            default => self::legacyEventFolder($type),
        };
    }

    /**
     * Resolve a historical, non-standard event image subdirectory safely.
     *
     * Legacy image selectors sometimes use event-specific subfolders that are
     * absent from the fixed folder mapping. Preserve these names only when
     * every segment is an ASCII filename component, with no dot segments,
     * backslashes, control bytes, empty segments or excessive nesting.
     * Invalid input must fail closed before upload/delete controllers use it.
     *
     * @param string $type Legacy event image folder name.
     * @return string Component-relative path below the events directory.
     * @throws \InvalidArgumentException When the folder is unsafe or empty.
     */
    private static function legacyEventFolder(string $type): string
    {
        $type = trim($type, '/');

        if (strlen($type) > 128
            || preg_match('~\A[A-Za-z0-9_-]+(?:/[A-Za-z0-9_-]+){0,3}\z~D', $type) !== 1) {
            throw new \InvalidArgumentException('Invalid SportsManagement image folder type.', 400);
        }

        return 'events/' . $type;
    }

    /**
     * Check an uploaded image against component size, type and content rules.
     *
     * Rejects missing files, oversized uploads, unsupported extensions,
     * mismatching image MIME types and embedded active HTML. Reports a
     * translated warning to Joomla's image-handler log on failure.
     *
     * @param mixed $file Uploaded file metadata with name, size and tmp_name.
     * @return bool True only when the uploaded raster image passes validation.
     */
    public static function check($file): bool
    {
        $file = (array) $file;
        $name = (string) ($file['name'] ?? '');
        $tmpName = (string) ($file['tmp_name'] ?? '');
        $size = (int) ($file['size'] ?? 0);
        $params = ComponentHelper::getParams('com_sportsmanagement');
        $sizeLimit = max(1, (int) $params->get('image_max_size', 120)) * 1024;
        $extension = strtolower((string) File::getExt($name));
        // SVG can execute scripts when opened in the browser. Restrict new
        // uploads to raster formats and verify their actual image MIME type.
        $allowedMimes = [
            'gif' => ['image/gif'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'bmp' => ['image/bmp', 'image/x-ms-bmp'],
            'webp' => ['image/webp'],
        ];

        if ($name === '' || $tmpName === '' || !isset($allowedMimes[$extension])) {
            Log::add(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_ERROR1') . ' '
                    . self::escape($name),
                Log::WARNING,
                'jsmerror'
            );

            return false;
        }

        $actualSize = is_file($tmpName) ? filesize($tmpName) : false;

        if ($size <= 0 || $size > $sizeLimit
            || $actualSize === false || $actualSize <= 0 || $actualSize > $sizeLimit) {
            Log::add(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_ERROR2') . ' '
                    . self::escape($name),
                Log::WARNING,
                'jsmerror'
            );

            return false;
        }

        $imageInfo = @getimagesize($tmpName);
        $mime = is_array($imageInfo) ? strtolower((string) ($imageInfo['mime'] ?? '')) : '';

        if (!in_array($mime, $allowedMimes[$extension], true)) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_ERROR1'), Log::WARNING, 'jsmerror');
            return false;
        }

        try {
            $xssCheck = file_get_contents($tmpName, false, null, 0, 4096);

            if ($xssCheck === false) {
                throw new \RuntimeException('Uploaded image could not be read.');
            }
        } catch (\Throwable $e) {
            Log::add($e->getMessage(), Log::WARNING, 'jsmerror');

            return false;
        }

        if (preg_match(
            '#<\s*(?:script|iframe|object|embed|applet|form|input|button|textarea|style|link|meta|frame|frameset|body|html)\b#i',
            $xssCheck
        )) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_IE_WARN'), Log::WARNING, 'jsmerror');

            return false;
        }

        return true;
    }

    /**
     * Create a safe image filename for uploads into the selected folder.
     *
     * Normalises the basename to safe characters, retains the extension, and
     * appends an incrementing timestamp when required to avoid collisions.
     * National flags keep their historical stable filenames.
     *
     * @param mixed $baseDir Destination directory for collision checks.
     * @param mixed $filename Uploaded filename to normalise.
     * @return string Safe destination basename.
     */
    public static function sanitize($baseDir, $filename): string
    {
        $baseDir = rtrim((string) $baseDir, '/\\') . DIRECTORY_SEPARATOR;
        $filename = trim((string) $filename, '.');
        $extension = strtolower((string) pathinfo($filename, PATHINFO_EXTENSION));
        $name = (string) pathinfo($filename, PATHINFO_FILENAME);
        $name = strtolower((string) preg_replace('/[^0-9a-zA-Z()_-]/', '_', $name));
        $name = trim($name, '_');

        if ($name === '') {
            $name = 'image';
        }

        if ($extension === '') {
            return $name;
        }

        if (self::$_foldertype === 'flags') {
            return $name . '.' . $extension;
        }

        $timestamp = time();
        do {
            $candidate = $name . '_' . $timestamp . '.' . $extension;
            $timestamp++;
        } while (File::exists($baseDir . $candidate));

        return $candidate;
    }


    /**
     * Resolve the running Joomla frontend or administrator application.
     *
     * Joomla's application factory returns the actual request context in
     * versions 5 and 6. Unlike the site-only resolver it provides the active
     * backend document for league and other image-select form fields. Other
     * application clients are rejected instead of rendering unsafe dialogs.
     *
     * @return CMSApplicationInterface Active site or administrator application.
     * @throws \RuntimeException When the current client is unsupported.
     */
    private static function currentApplication(): CMSApplicationInterface
    {
        $app = Factory::getApplication();

        if (!$app->isClient('site') && !$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement image selector requires a Joomla site or administrator application.', 500);
        }

        return $app;
    }

    /**
     * Build instance-specific JavaScript for image selection and previews.
     *
     * JSON-encodes all PHP-controlled values before generating callbacks for
     * modal image selection, resetting and clearing, and preview refreshes.
     * Each selector uses its own field identifier for independent operation.
     *
     * @param string $selectFunction Historical modal callback name.
     * @param string $functionSuffix Unique selector callback suffix.
     * @param string $fieldName Submitted Joomla form field name.
     * @param string $fieldId Visible HTML input identifier.
     * @param string $root Absolute site base URL.
     * @param string $imageBase Component image directory relative to the site.
     * @param string $defaultPlaceholder Default image when cleared.
     * @param string $malePlaceholder Placeholder for male person records.
     * @param string $femalePlaceholder Placeholder for female person records.
     * @return string Inline script handling this selector instance.
     */
    private static function buildJavascript(
        string $selectFunction,
        string $functionSuffix,
        string $fieldName,
        string $fieldId,
        string $root,
        string $imageBase,
        string $defaultPlaceholder,
        string $malePlaceholder,
        string $femalePlaceholder
    ): string {
        $selectFunctionJs = self::js($selectFunction);
        $fieldNameJs = self::js($fieldName);
        $fieldIdJs = self::js($fieldId);
        $rootJs = self::js($root);
        $imageBaseJs = self::js($imageBase);
        $defaultPlaceholderJs = self::js($defaultPlaceholder);
        $malePlaceholderJs = self::js($malePlaceholder);
        $femalePlaceholderJs = self::js($femalePlaceholder);

        return <<<JS
(function () {
    const fieldName = {$fieldNameJs};
    const fieldId = {$fieldIdJs};
    const root = {$rootJs};
    const imageBase = {$imageBaseJs};

    const setValue = function (value) {
        const visible = document.getElementById(fieldId);
        const submitted = document.getElementById('a_' + fieldName);
        const copy = document.getElementById('copy_' + fieldId);
        const preview = document.getElementById(fieldId + '_preview');

        if (visible) visible.value = value;
        if (submitted) submitted.value = value;
        if (copy) copy.value = value;
        if (preview) preview.src = root + value.replace(/^\//, '');

        document.getElementsByName(fieldName).forEach(function (element) {
            if (!element.disabled) element.value = value;
        });
    };

    window[{$selectFunctionJs}] = function (image, imageName) {
        setValue(imageBase + (imageName || image));
    };

    window.reset_{$functionSuffix} = function () {
        const original = document.getElementById('original_' + fieldId);
        setValue(original ? original.value : '');
    };

    window.clear_{$functionSuffix} = function () {
        let placeholder = {$defaultPlaceholderJs};
        const checkedGender = document.querySelector('input[name="jform[gender]"]:checked');

        if (checkedGender && checkedGender.value === '1') {
            placeholder = {$malePlaceholderJs};
        } else if (checkedGender && checkedGender.value === '2') {
            placeholder = {$femalePlaceholderJs};
        }

        setValue(placeholder);
    };

    window.jInsertFieldValue = function (value, id) {
        if (id === fieldId) {
            setValue(value);
            return;
        }

        const element = document.getElementById(id);
        const preview = document.getElementById(id + '_preview');
        if (element) element.value = value;
        if (preview) preview.src = root + value.replace(/^\//, '');
    };

    const initialise = function () {
        const submitted = document.getElementById('a_' + fieldName);
        const visible = document.getElementById(fieldId);
        const value = submitted ? submitted.value : (visible ? visible.value : '');
        if (value) setValue(value);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialise, {once: true});
    } else {
        initialise();
    }
}());
JS;
    }

    /**
     * Render an upload or image-browser action as a Bootstrap modal link.
     *
     * Delegates URL validation, markup escaping and dialog dimensions to the
     * shared Joomla 5/6 modal helper for consistent administrator rendering.
     *
     * @param string $id HTML modal identifier.
     * @param string $icon Button image path.
     * @param string $title Translated accessible title.
     * @param string $url Image-handling destination.
     * @param int $width Requested dialog width.
     * @param int $height Requested dialog height.
     * @return string Escaped clickable modal markup.
     */
    private static function modalButton(
        string $id,
        string $icon,
        string $title,
        string $url,
        int $width,
        int $height
    ): string {
        return ModalImageHelper::render(
            $id,
            $icon,
            $title,
            20,
            $url,
            $width,
            $height,
            0,
            'data-jsm-role',
            'modal-action'
        );
    }

    /**
     * Resolve the configured placeholder image for a component image type.
     *
     * Maps leagues, players, clubs, projects, stadiums and other historical
     * category aliases to their corresponding SportsManagement configuration
     * keys, returning an empty string for unspecified categories.
     *
     * @param string $type Selected image or placeholder category.
     * @return string Configured placeholder image path.
     */
    private static function defaultPlaceholder(string $type): string
    {
        $params = ComponentHelper::getParams('com_sportsmanagement');

        return (string) match ($type) {
            'trikot_home', 'trikot_away', 'clubs_trikot_home', 'clubs_trikot_away',
            'clubkits_home', 'clubkits_away'
                => $params->get('ph_trikot', ''),
            'projects' => $params->get('ph_project', ''),
            'projectteams/trikot_home', 'projectteams/trikot_away'
                => $params->get('ph_logo_small', ''),
            'player', 'persons', 'teamplayers' => $params->get('ph_player', ''),
            'stadium', 'playgrounds' => $params->get('ph_stadium', ''),
            'menlarge' => $params->get('ph_player_men_large', ''),
            'mensmall' => $params->get('ph_player_men_small', ''),
            'womanlarge' => $params->get('ph_player_woman_large', ''),
            'womansmall' => $params->get('ph_player_woman_small', ''),
            'clublogobig', 'logo_big', 'clubs_large', 'league', 'leagues'
                => $params->get('ph_logo_big', ''),
            'clublogomedium', 'logo_middle', 'clubs_medium'
                => $params->get('ph_logo_medium', ''),
            'clublogosmall', 'logo_small', 'clubs_small'
                => $params->get('ph_logo_small', ''),
            'icon' => $params->get(
                'ph_icon',
                'images/com_sportsmanagement/database/placeholders/placeholder_21.png'
            ),
            'team', 'team_picture', 'teams', 'projectteams', 'projectteam_picture'
                => $params->get('ph_team', ''),
            default => '',
        };
    }

    /**
     * Escape untrusted image field values for HTML text and attributes.
     *
     * Uses UTF-8 and both quote styles so names, paths and translated labels
     * cannot inject additional markup into image-select controls.
     *
     * @param string $value Unescaped text or attribute value.
     * @return string HTML-escaped value.
     */
    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Encode a PHP string safely as a literal JavaScript string value.
     *
     * Encodes angle brackets, ampersands and both quote types to prevent
     * executable markup from appearing in image selector inline scripts.
     *
     * @param string $value Input value for the generated JavaScript.
     * @return string JSON-formatted JavaScript string literal.
     */
    private static function js(string $value): string
    {
        return (string) json_encode(
            $value,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
        );
    }
}
