<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\TitleField;

if (!class_exists(TitleField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/TitleField.php';
}

if (!class_exists('JFormFieldTitle', false)) {
    class_alias(TitleField::class, 'JFormFieldTitle');
}
