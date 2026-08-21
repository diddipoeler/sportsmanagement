<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ExtensionsubtitleField;

if (!class_exists(ExtensionsubtitleField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ExtensionsubtitleField.php';
}

if (!class_exists('JFormFieldextensionsubtitle', false)) {
    class_alias(ExtensionsubtitleField::class, 'JFormFieldextensionsubtitle');
}
