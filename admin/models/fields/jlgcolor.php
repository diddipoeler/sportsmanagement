<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\JlgcolorField;

if (!class_exists(JlgcolorField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/JlgcolorField.php';
}

if (!class_exists('JFormFieldJLGColor', false)) {
    class_alias(JlgcolorField::class, 'JFormFieldJLGColor');
}
