<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\TeamsField;

if (!class_exists(TeamsField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/TeamsField.php';
}

if (!class_exists('JFormFieldTeams', false)) {
    class_alias(TeamsField::class, 'JFormFieldTeams');
}
