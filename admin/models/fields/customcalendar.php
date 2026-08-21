<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\CustomcalendarField;

if (!class_exists(CustomcalendarField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/CustomcalendarField.php';
}

if (!class_exists('JFormFieldCustomCalendar', false)) {
    class_alias(CustomcalendarField::class, 'JFormFieldCustomCalendar');
}
