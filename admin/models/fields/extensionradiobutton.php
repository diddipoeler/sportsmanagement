<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ExtensionradiobuttonField;

if (!class_exists(ExtensionradiobuttonField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ExtensionradiobuttonField.php';
}

if (!class_exists('JFormFieldExtensionRadioButton', false)) {
    class_alias(ExtensionradiobuttonField::class, 'JFormFieldExtensionRadioButton');
}
