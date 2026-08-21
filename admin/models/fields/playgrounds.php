<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\PlaygroundsField;

if (!class_exists(PlaygroundsField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/PlaygroundsField.php';
}

if (!class_exists('JFormFieldPlaygrounds', false)) {
    class_alias(PlaygroundsField::class, 'JFormFieldPlaygrounds');
}
