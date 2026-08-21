<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\JlmenuitemsField;

if (!class_exists(JlmenuitemsField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/JlmenuitemsField.php';
}

if (!class_exists('JFormFieldJLMenuItems', false)) {
    class_alias(JlmenuitemsField::class, 'JFormFieldJLMenuItems');
}
