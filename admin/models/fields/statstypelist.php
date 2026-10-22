<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 statistics type list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\StatstypelistField;

if (!class_exists(StatstypelistField::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsManagementDatabaseTrait.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsManagementListField.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/StatstypelistField.php',
    ] as $nativeField) {
        if (is_file($nativeField)) {
            require_once $nativeField;
        }
    }
}

if (!class_exists(StatstypelistField::class)) {
    throw new \RuntimeException('SportsManagement native Statstypelist field could not be loaded.', 500);
}

if (!class_exists('JFormFieldStatstypelist', false)) {
    class_alias(StatstypelistField::class, 'JFormFieldStatstypelist');
}
