<?php
/** Legacy compatibility bridge for the native Joomla 5/6 clubs field. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ClubsField;

if (!class_exists(ClubsField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsManagementDatabaseTrait.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ClubsField.php';
}

if (class_exists(ClubsField::class) && !class_exists('JFormFieldClubs', false)) {
    class_alias(ClubsField::class, 'JFormFieldClubs');
}
