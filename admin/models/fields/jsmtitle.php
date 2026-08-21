<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\JsmtitleField;

if (!class_exists(JsmtitleField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/JsmtitleField.php';
}

if (!class_exists('JFormFieldJSMTitle', false)) {
    class_alias(JsmtitleField::class, 'JFormFieldJSMTitle');
}
