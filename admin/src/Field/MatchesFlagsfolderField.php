<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** Joomla 5/6 flag-folder selector used by mod_sportsmanagement_matches. */
final class MatchesFlagsfolderField extends FormField
{
    protected $type = 'MatchesFlagsfolder';

    protected function getInput(): string
    {
        $folders = [];

        foreach (['images', 'media'] as $rootFolder) {
            $base = JPATH_ROOT . '/' . $rootFolder;

            if (!is_dir($base)) {
                continue;
            }

            foreach (Folder::folders($base, '', true, true, ['system']) ?: [] as $folder) {
                $relative = ltrim(str_replace(JPATH_ROOT, '', $folder), '/\\');

                if ($relative !== '') {
                    $folders[$relative] = $relative;
                }
            }
        }

        ksort($folders, SORT_NATURAL | SORT_FLAG_CASE);

        Factory::getApplication()
            ->getLanguage()
            ->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

        $options = [
            HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_DO_NOT_USE')),
        ];

        foreach ($folders as $folder) {
            $options[] = HTMLHelper::_('select.option', $folder, $folder);
        }

        return HTMLHelper::_(
            'select.genericlist',
            $options,
            $this->name,
            ['class' => 'form-select'],
            'value',
            'text',
            $this->value,
            $this->id
        );
    }
}
