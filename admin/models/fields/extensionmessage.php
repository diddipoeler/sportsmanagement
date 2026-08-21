<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ExtensionmessageField;

if (!class_exists(ExtensionmessageField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ExtensionmessageField.php';
}

if (!class_exists('JFormFieldextensionmessage', false)) {
    class_alias(ExtensionmessageField::class, 'JFormFieldextensionmessage');
}
