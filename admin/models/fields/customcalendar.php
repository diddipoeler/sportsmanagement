<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 custom calendar field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\CustomcalendarField;

if (!class_exists(CustomcalendarField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/CustomcalendarField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(CustomcalendarField::class)) {
    throw new \RuntimeException('SportsManagement native Customcalendar field could not be loaded.', 500);
}

if (!class_exists('JFormFieldCustomCalendar', false)) {
    class_alias(CustomcalendarField::class, 'JFormFieldCustomCalendar');
}
