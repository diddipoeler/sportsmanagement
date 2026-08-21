<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\JsmmessageField;

if (!class_exists(JsmmessageField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/JsmmessageField.php';
}

if (!class_exists('JFormFieldJSMMessage', false)) {
    class_alias(JsmmessageField::class, 'JFormFieldJSMMessage');
}
