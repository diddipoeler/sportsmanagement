<?php
/** Legacy compatibility bridge for the native Joomla 5/6 teams field. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\TeamsField;

if (!class_exists(TeamsField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsManagementDatabaseTrait.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/TeamsField.php';
}

if (class_exists(TeamsField::class) && !class_exists('JFormFieldTeams', false)) {
    class_alias(TeamsField::class, 'JFormFieldTeams');
}
