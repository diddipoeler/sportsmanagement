<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\UserfieldsField;

if (!class_exists(UserfieldsField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/UserfieldsField.php';
}

if (!class_exists('JFormFielduserfields', false)) {
    class_alias(UserfieldsField::class, 'JFormFielduserfields');
}
