<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\GeocompleteField;

if (!class_exists(GeocompleteField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/GeocompleteField.php';
}

if (!class_exists('JFormFieldGeocomplete', false)) {
    class_alias(GeocompleteField::class, 'JFormFieldGeocomplete');
}
