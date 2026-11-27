<?php
/**
 * Native Joomla 5/6 matches flag-folder field.
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

/** Joomla 5/6 flag-folder selector used by mod_sportsmanagement_matches. */
final class MatchesFlagsfolderField extends FormField
{
    protected $type = 'MatchesFlagsfolder';

    /**
     * Render the available flag folders beneath Joomla images and media.
     * Converts discovered folders to site-relative paths, sorts them and
     * preserves the translated empty selection for the matches module.
     * @return string Native Joomla folder selection markup.
     */
    protected function getInput(): string
    {
        $folders = [];

        foreach (['images', 'media'] as $rootFolder) {
            $base = JPATH_ROOT . '/' . $rootFolder;

            if (!is_dir($base)) {
                continue;
            }

            $entries = Folder::folders($base, '', true, true, ['system']);
            foreach (is_array($entries) ? $entries : [] as $folder) {
                if (!is_string($folder) || !str_starts_with($folder, $base . DIRECTORY_SEPARATOR)) {
                    continue;
                }
                $relative = ltrim(substr($folder, strlen(JPATH_ROOT)), '/\\');

                if ($relative !== '') {
                    $folders[$relative] = $relative;
                }
            }
        }

        ksort($folders, SORT_NATURAL | SORT_FLAG_CASE);

        $app = SportsManagementAdministratorApplicationResolver::resolve();
        $app->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

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
