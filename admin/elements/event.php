<?php
/** Legacy compatibility bridge for the native Joomla 5/6 event field. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\EventField;

if (!class_exists(EventField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsManagementDatabaseTrait.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportsManagementListField.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/EventField.php';
}

if (class_exists(EventField::class) && !class_exists('JFormFieldEvent', false)) {
    class_alias(EventField::class, 'JFormFieldEvent');
}
