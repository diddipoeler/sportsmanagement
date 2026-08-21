<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\JsmlinkField;

if (!class_exists(JsmlinkField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/JsmlinkField.php';
}

if (!class_exists('JFormFieldJSMLink', false)) {
    class_alias(JsmlinkField::class, 'JFormFieldJSMLink');
}
