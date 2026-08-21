<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\SerialnumberField;

if (!class_exists(SerialnumberField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SerialnumberField.php';
}

if (!class_exists('JFormFieldserialnumber', false)) {
    class_alias(SerialnumberField::class, 'JFormFieldserialnumber');
}
