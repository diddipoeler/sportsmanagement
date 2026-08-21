<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\EventtypelistField;

if (!class_exists(EventtypelistField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/EventtypelistField.php';
}

if (!class_exists('JFormFieldeventtypelist', false)) {
    class_alias(EventtypelistField::class, 'JFormFieldeventtypelist');
}
