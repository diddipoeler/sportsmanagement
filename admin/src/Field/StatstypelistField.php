<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Filesystem\Folder;

final class StatstypelistField extends ListField
{
    protected $type = 'statstypelist';

    protected function getOptions(): array
    {
        $options = [];
        $files = Folder::files(JPATH_COMPONENT_ADMINISTRATOR . '/statistics', 'php$') ?: [];

        foreach ($files as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);

            if ($name === '' || $name === 'base') {
                continue;
            }

            $options[] = HTMLHelper::_('select.option', $name, $name);
        }

        return array_merge(parent::getOptions(), $options);
    }
}
