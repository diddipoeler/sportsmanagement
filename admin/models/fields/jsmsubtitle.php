<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\JsmsubtitleField;

if (!class_exists(JsmsubtitleField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/JsmsubtitleField.php';
}

if (!class_exists('JFormFieldJSMSubtitle', false)) {
    class_alias(JsmsubtitleField::class, 'JFormFieldJSMSubtitle');
}
