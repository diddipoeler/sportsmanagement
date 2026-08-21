<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ActeventtypeField;

if (!class_exists(ActeventtypeField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ActeventtypeField.php';
}

if (!class_exists('JFormFieldacteventtype', false)) {
    class_alias(ActeventtypeField::class, 'JFormFieldacteventtype');
}
