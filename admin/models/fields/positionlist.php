<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\PositionlistField;

if (!class_exists(PositionlistField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/PositionlistField.php';
}

if (!class_exists('JFormFieldpositionlist', false)) {
    class_alias(PositionlistField::class, 'JFormFieldpositionlist');
}
