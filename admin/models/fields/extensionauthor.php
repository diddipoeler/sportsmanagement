<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ExtensionauthorField;

if (!class_exists(ExtensionauthorField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ExtensionauthorField.php';
}

if (!class_exists('JFormFieldExtensionAuthor', false)) {
    class_alias(ExtensionauthorField::class, 'JFormFieldExtensionAuthor');
}
