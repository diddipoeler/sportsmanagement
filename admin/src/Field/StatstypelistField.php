<?php
/**
 * Joomla 5/6 native statistics type list field.
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

            $options[] = (object) [
                'value' => (string) $name,
                'text' => (string) $name,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
