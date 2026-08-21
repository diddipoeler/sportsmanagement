<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ParentdivisionField;

if (!class_exists(ParentdivisionField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ParentdivisionField.php';
}

if (!class_exists('JFormFieldparentdivision', false)) {
    class_alias(ParentdivisionField::class, 'JFormFieldparentdivision');
}
