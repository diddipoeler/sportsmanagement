<?php
/** Compatibility bridge for the Joomla 5/6 user list field. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Field\UserlistField;

if (!class_exists(UserlistField::class)) {
    $fieldFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/UserlistField.php';

    if (is_file($fieldFile)) {
        require_once $fieldFile;
    }
}

if (class_exists(UserlistField::class) && !class_exists('JFormFielduserlist', false)) {
    class_alias(UserlistField::class, 'JFormFielduserlist');
}
