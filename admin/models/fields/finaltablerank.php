<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\FinaltablerankField;

if (!class_exists(FinaltablerankField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/FinaltablerankField.php';
}

if (!class_exists('JFormFieldfinaltablerank', false)) {
    class_alias(FinaltablerankField::class, 'JFormFieldfinaltablerank');
}
