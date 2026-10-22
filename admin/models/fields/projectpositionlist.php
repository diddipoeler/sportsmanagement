<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 project position list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ProjectpositionlistField;

if (!class_exists(ProjectpositionlistField::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Service/SportsManagementAdministratorApplicationResolver.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsManagementDatabaseTrait.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsManagementListField.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ProjectpositionlistField.php',
    ] as $nativeField) {
        if (is_file($nativeField)) {
            require_once $nativeField;
        }
    }
}

if (!class_exists(ProjectpositionlistField::class)) {
    throw new \RuntimeException('SportsManagement native Projectpositionlist field could not be loaded.', 500);
}

if (!class_exists('JFormFieldprojectpositionlist', false)) {
    class_alias(ProjectpositionlistField::class, 'JFormFieldprojectpositionlist');
}
