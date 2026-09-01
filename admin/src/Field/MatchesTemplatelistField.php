<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** Joomla 5/6 template selector used by mod_sportsmanagement_matches. */
final class MatchesTemplatelistField extends FormField
{
    protected $type = 'MatchesTemplatelist';

    protected function getInput(): string
    {
        $directory = trim((string) ($this->element['directory'] ?? ''));
        $path = JPATH_ROOT . '/' . ltrim($directory, '/');
        $filter = trim((string) ($this->element['filter'] ?? ''));
        $exclude = trim((string) ($this->element['exclude'] ?? ''));
        $folders = is_dir($path) ? (Folder::folders($path, $filter) ?: []) : [];
        $options = [];

        foreach ($folders as $folder) {
            if ($exclude !== '' && preg_match(chr(1) . $exclude . chr(1), $folder)) {
                continue;
            }

            $options[] = HTMLHelper::_('select.option', $folder, $folder);
        }

        Factory::getApplication()
            ->getLanguage()
            ->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

        if (!(bool) ($this->element['hide_none'] ?? false)) {
            array_unshift(
                $options,
                HTMLHelper::_('select.option', '-1', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_DO_NOT_USE'))
            );
        }

        if (!(bool) ($this->element['hide_default'] ?? false)) {
            array_unshift(
                $options,
                HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_USE_DEFAULT'))
            );
        }

        $root = rtrim((string) Uri::root(true), '/');
        $previewBase = $root . '/modules/mod_sportsmanagement_matches/tmpl/';
        $onchange = "const image=document.getElementById('TemplateImage');"
            . "if(image){image.src=" . json_encode($previewBase) . "+encodeURIComponent(this.value)+'/template.png';}";

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
            $this->value,
            $this->id
        );

        $details = trim((string) ($this->element['details'] ?? ''));
        $detailsHtml = $details !== '' ? '<div class="form-text">' . Text::_($details) . '</div>' : '';
        $preview = $previewBase . rawurlencode((string) $this->value) . '/template.png';

        return '<div class="jsm-template-selector">'
            . $select
            . $detailsHtml
            . '<div class="mt-3 p-2 bg-body-secondary" style="max-width:216px;">'
            . '<img id="TemplateImage" src="' . htmlspecialchars($preview, ENT_QUOTES, 'UTF-8') . '" '
            . 'alt="" width="200" style="max-width:100%;height:auto;">'
            . '</div>'
            . '</div>';
    }
}
