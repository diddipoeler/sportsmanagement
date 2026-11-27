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

    /**
     * Discover available statistics implementations in the installed component.
     * Lists PHP filenames without the extension, excludes the base class
     * and returns inherited options if the statistics directory is absent.
     * @return array Joomla selection options.
     */
    protected function getOptions(): array
    {
        $options = [];
        $directory = JPATH_COMPONENT_ADMINISTRATOR . '/statistics';
        if (!is_dir($directory)) {
            return parent::getOptions();
        }
        $files = Folder::files($directory, '\\.php

        foreach (is_array($files) ? $files : [] as $file) {
            if (!is_string($file)) {
                continue;
            }
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
);

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
