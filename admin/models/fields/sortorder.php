<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\SortorderField;

if (!class_exists(SortorderField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SortorderField.php';
}

if (!class_exists('JFormFieldsortorder', false)) {
    class_alias(SortorderField::class, 'JFormFieldsortorder');
}
