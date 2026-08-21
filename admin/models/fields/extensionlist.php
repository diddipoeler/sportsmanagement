<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ExtensionlistField;

if (!class_exists(ExtensionlistField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ExtensionlistField.php';
}

if (!class_exists('JFormFieldExtensionlist', false)) {
    class_alias(ExtensionlistField::class, 'JFormFieldExtensionlist');
}
