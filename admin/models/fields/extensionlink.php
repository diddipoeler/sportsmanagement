<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ExtensionlinkField;

if (!class_exists(ExtensionlinkField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ExtensionlinkField.php';
}

if (!class_exists('JFormFieldExtensionLink', false)) {
    class_alias(ExtensionlinkField::class, 'JFormFieldExtensionLink');
}
