<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ExtensionversionField;

if (!class_exists(ExtensionversionField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ExtensionversionField.php';
}

if (!class_exists('JFormFieldExtensionVersion', false)) {
    class_alias(ExtensionversionField::class, 'JFormFieldExtensionVersion');
}
