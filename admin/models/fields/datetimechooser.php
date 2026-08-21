<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\DatetimechooserField;

if (!class_exists(DatetimechooserField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/DatetimechooserField.php';
}

if (!class_exists('JFormFieldDatetimechooser', false)) {
    class_alias(DatetimechooserField::class, 'JFormFieldDatetimechooser');
}
