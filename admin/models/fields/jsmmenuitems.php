<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\JsmmenuitemsField;

if (!class_exists(JsmmenuitemsField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/JsmmenuitemsField.php';
}

if (!class_exists('JFormFieldJSMMenuItems', false)) {
    class_alias(JsmmenuitemsField::class, 'JFormFieldJSMMenuItems');
}
