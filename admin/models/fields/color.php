<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ColorField;

if (!class_exists(ColorField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ColorField.php';
}

if (!class_exists('JFormFieldColor', false)) {
    class_alias(ColorField::class, 'JFormFieldColor');
}
