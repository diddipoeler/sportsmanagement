<?php
/**
 * Native Joomla 5/6 matches template list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** Joomla 5/6 template selector used by mod_sportsmanagement_matches. */
final class MatchesTemplatelistField extends FormField
{
    protected $type = 'MatchesTemplatelist';

    /**
     * Render the matches module template selector and its individual preview.
     * Restricts folder discovery to the Joomla installation, validates XML
     * regular expressions and preserves the historical default and none
     * options. Escapes translated help text and preview attributes.
     * @return string Native Joomla select markup with template preview.
     */
    protected function getInput(): string
    {
        $directory = trim((string) ($this->element['directory'] ?? ''));
        $rootPath = realpath(JPATH_ROOT);
        $path = realpath(JPATH_ROOT . '/' . ltrim($directory, '/'));
        $filter = trim((string) ($this->element['filter'] ?? ''));
        $exclude = trim((string) ($this->element['exclude'] ?? ''));
        $validPath = $rootPath !== false && $path !== false && is_dir($path)
            && ($path === $rootPath || str_starts_with($path, $rootPath . DIRECTORY_SEPARATOR));
        $validFilter = strlen($filter) <= 256
            && ($filter === '' || @preg_match(chr(1) . $filter . chr(1), '') !== false);
        $excludePattern = $exclude !== '' && strlen($exclude) <= 256
            && @preg_match(chr(1) . $exclude . chr(1), '') !== false
            ? chr(1) . $exclude . chr(1) : null;
        $folders = $validPath && $validFilter ? Folder::folders($path, $filter) : [];
        $options = [];

        foreach (is_array($folders) ? $folders : [] as $folder) {
            if (!is_string($folder) || preg_match('/^[A-Za-z0-9_-]+$/D', $folder) !== 1) {
                continue;
            }
            if ($excludePattern !== null && preg_match($excludePattern, $folder) === 1) {
                continue;
            }

            $options[] = HTMLHelper::_('select.option', $folder, $folder);
        }

        $app = SportsManagementAdministratorApplicationResolver::resolve();
        $app->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

        if (!in_array(strtolower((string) ($this->element['hide_none'] ?? '')), ['true', '1'], true)) {
            array_unshift(
                $options,
                HTMLHelper::_('select.option', '-1', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_DO_NOT_USE'))
            );
        }

        if (!in_array(strtolower((string) ($this->element['hide_default'] ?? '')), ['true', '1'], true)) {
            array_unshift(
                $options,
                HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_USE_DEFAULT'))
            );
        }

        $root = rtrim((string) Uri::root(true), '/');
        $previewBase = $root . '/modules/mod_sportsmanagement_matches/tmpl/';
        $previewId = $this->id . '_template_preview';
        $selected = is_string($this->value) ? $this->value : '';
        $onchange = "const image=document.getElementById(" . json_encode($previewId) . ");"
            . "if(image){const valid=/^[A-Za-z0-9_-]+$/.test(this.value)&&this.value!=='-1';"
            . "image.hidden=!valid;if(valid){image.src=" . json_encode($previewBase) . "+encodeURIComponent(this.value)+'/template.png';}else{image.removeAttribute('src');}}";

        $select = HTMLHelper::_(
            'select.genericlist',
            $options,
            $this->name,
            [
                'class' => 'form-select',
                'onchange' => $onchange,
            ],
            'value',
            'text',
            $selected,
            $this->id
        );

        $details = trim((string) ($this->element['details'] ?? ''));
        $detailsHtml = $details !== '' ? '<div class="form-text">' . htmlspecialchars(Text::_($details), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>' : '';
        $preview = preg_match('/^[A-Za-z0-9_-]+$/D', $selected) === 1 && $selected !== '-1'
            ? $previewBase . rawurlencode($selected) . '/template.png' : '';

        return '<div class="jsm-template-selector">'
            . $select
            . $detailsHtml
            . '<div class="mt-3 p-2 bg-body-secondary" style="max-width:216px;">'
            . '<img id="' . htmlspecialchars($previewId, ENT_QUOTES, 'UTF-8') . '" '
            . ($preview !== '' ? 'src="' . htmlspecialchars($preview, ENT_QUOTES, 'UTF-8') . '" ' : 'hidden ')
            . 'alt="" width="200" style="max-width:100%;height:auto;">'
            . '</div>'
            . '</div>';
    }
}
