<?php
/** Legacy compatibility bridge for the native Joomla 5/6 team field. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\TeamField;

if (!class_exists(TeamField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsManagementDatabaseTrait.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsManagementListField.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/TeamField.php';
}

if (class_exists(TeamField::class) && !class_exists('JFormFieldTeam', false)) {
    class_alias(TeamField::class, 'JFormFieldTeam');
}
