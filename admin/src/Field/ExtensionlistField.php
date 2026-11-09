<?php
/**
 * Native Joomla 5/6 extension-list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\Filesystem\Folder;

final class ExtensionlistField extends ListField
{
    protected $type = 'Extensionlist';

    /**
     * Build the optional extension-folder list for administrator settings.
     *
     * Reads extension directories beneath the component's installed folder,
     * validates both filter and exclusion-pattern syntax, and appends safe
     * folder names
     * to the inherited Joomla field options.
     *
     * @return array<int,object> Installed extension directory choices.
     */
    protected function getOptions(): array
    {
        $path = JPATH_ROOT . '/components/com_sportsmanagement/extensions';

        if (!is_dir($path)) {
            return parent::getOptions();
        }

        $filter = (string) ($this->element['filter'] ?? '');
        $exclude = (string) ($this->element['exclude'] ?? '');

        // Joomla's Folder::folders() interprets its filter as a regular
        // expression. Refuse invalid or excessively long XML configuration.
        if (strlen($filter) > 256 || strlen($exclude) > 256
            || ($filter !== '' && @preg_match(chr(1) . $filter . chr(1), '') === false)) {
            return parent::getOptions();
        }
        // Invalid XML regular expressions must not trigger preg_match errors
        // or break the administrator settings form.
        $excludePattern = $exclude !== '' ? chr(1) . $exclude . chr(1) : null;

        if ($excludePattern !== null && @preg_match($excludePattern, '') === false) {
            $excludePattern = null;
        }

        $folders = Folder::folders($path, $filter) ?: [];
        $options = [];

        foreach ($folders as $folder) {
            if (!is_string($folder) || preg_match('/^[A-Za-z0-9_-]+$/D', $folder) !== 1) {
                continue;
            }

            if ($excludePattern !== null && preg_match($excludePattern, $folder) === 1) {
                continue;
            }

            $options[] = (object) [
                'value' => (string) $folder,
                'text' => (string) $folder,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
