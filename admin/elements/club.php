<?php
/** Legacy compatibility bridge for the native Joomla 5/6 club field. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ClubField;

if (!class_exists(ClubField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsManagementDatabaseTrait.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsManagementListField.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ClubField.php';
}

if (class_exists(ClubField::class) && !class_exists('JFormFieldClub', false)) {
    class_alias(ClubField::class, 'JFormFieldClub');
}
