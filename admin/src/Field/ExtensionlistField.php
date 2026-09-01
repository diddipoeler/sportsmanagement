<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\Filesystem\Folder;

final class ExtensionlistField extends ListField
{
    protected $type = 'Extensionlist';

    protected function getOptions(): array
    {
        $path = JPATH_ROOT . '/components/com_sportsmanagement/extensions';

        if (!is_dir($path)) {
            return parent::getOptions();
        }

        $filter = (string) ($this->element['filter'] ?? '');
        $exclude = (string) ($this->element['exclude'] ?? '');
        $folders = Folder::folders($path, $filter) ?: [];
        $options = [];

        foreach ($folders as $folder) {
            if ($exclude !== '' && @preg_match(chr(1) . $exclude . chr(1), $folder) === 1) {
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
