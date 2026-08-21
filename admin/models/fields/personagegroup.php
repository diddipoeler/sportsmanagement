<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\PersonagegroupField;

if (!class_exists(PersonagegroupField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/PersonagegroupField.php';
}

if (!class_exists('JFormFieldpersonagegroup', false)) {
    class_alias(PersonagegroupField::class, 'JFormFieldpersonagegroup');
}
