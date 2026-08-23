<?php
/** Legacy compatibility bridge for the native Joomla 5/6 events field. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\EventsField;

if (!class_exists(EventsField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsManagementDatabaseTrait.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsManagementListField.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/EventsField.php';
}

if (class_exists(EventsField::class) && !class_exists('JFormFieldEvents', false)) {
    class_alias(EventsField::class, 'JFormFieldEvents');
}
