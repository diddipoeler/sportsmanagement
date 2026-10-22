<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Google calendar field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\GcalendarField;

if (!class_exists(GcalendarField::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsManagementDatabaseTrait.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsManagementListField.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/GcalendarField.php',
    ] as $nativeField) {
        if (is_file($nativeField)) {
            require_once $nativeField;
        }
    }
}

if (!class_exists(GcalendarField::class)) {
    throw new \RuntimeException('SportsManagement native Gcalendar field could not be loaded.', 500);
}

if (!class_exists('JFormFieldGCalendar', false)) {
    class_alias(GcalendarField::class, 'JFormFieldGCalendar');
}
