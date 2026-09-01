<?php
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
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

    public function __construct()
    {
        $input = Factory::getApplication()->getInput();
        self::$_foldertype = $input->getCmd('type', '');
        self::$_view = $input->getCmd('view', '');
    }

    public static function getSelector(
        $fieldname = '',
        $fieldpreviewName = '',
        $type = '',
        $value = '',
        $default = '',
        $controlName = '',
        $fieldid = ''
    ): string {
        $app = Factory::getApplication();
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

        $document = $app->getDocument();
        if (method_exists($document, 'getWebAssetManager')) {
            $document->getWebAssetManager()->addInlineScript($js);
        } else {
            $document->addScriptDeclaration($js);
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

    public static function getFolder($type): string
    {
        return match ((string) $type) {
            'clubs_small', 'clubssmall', 'clubs/small' => 'clubs/small',
            'clubs_medium', 'clubsmedium', 'clubs/medium' => 'clubs/medium',
            'clubs_large', 'clubslarge', 'clubs/large' => 'clubs/large',
            'clubs_trikot_home', 'clubs_trikot_away' => 'clubs/trikot',
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
            default => 'events/' . trim((string) $type, '/'),
        };
    }

    public static function check($file): bool
    {
        $file = (array) $file;
        $name = (string) ($file['name'] ?? '');
        $tmpName = (string) ($file['tmp_name'] ?? '');
        $size = (int) ($file['size'] ?? 0);
        $params = ComponentHelper::getParams('com_sportsmanagement');
        $sizeLimit = max(1, (int) $params->get('image_max_size', 120)) * 1024;
        $extension = strtolower((string) File::getExt($name));

        if ($name === '' || $tmpName === '' || !in_array($extension, ['gif', 'jpg', 'jpeg', 'png', 'bmp', 'svg'], true)) {
            Log::add(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_ERROR1') . ' '
                    . self::escape($name),
                Log::WARNING,
                'jsmerror'
            );

            return false;
        }

        if ($size <= 0 || $size > $sizeLimit) {
            Log::add(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_ERROR2') . ' '
                    . self::escape($name),
                Log::WARNING,
                'jsmerror'
            );

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

    private static function defaultPlaceholder(string $type): string
    {
        $params = ComponentHelper::getParams('com_sportsmanagement');

        return (string) match ($type) {
            'trikot_home', 'trikot_away', 'clubs_trikot_home', 'clubs_trikot_away'
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

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private static function js(string $value): string
    {
        return (string) json_encode(
            $value,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
        );
    }
}
