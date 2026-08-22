<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\SportsmanagementField;

if (!class_exists(SportsmanagementField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsmanagementField.php';
}

if (!class_exists('JFormFieldsportsmanagement', false)) {
    class_alias(SportsmanagementField::class, 'JFormFieldsportsmanagement');
}
