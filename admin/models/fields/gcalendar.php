<?php
/** Compatibility bridge for the Joomla 5/6 Google calendar field. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Field\GcalendarField;

if (!class_exists(GcalendarField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/GcalendarField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(GcalendarField::class) && !class_exists('JFormFieldGCalendar', false)) {
    class_alias(GcalendarField::class, 'JFormFieldGCalendar');
}
