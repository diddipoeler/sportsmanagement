<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\CountryassociationField;

if (!class_exists(CountryassociationField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/CountryassociationField.php';
}

if (!class_exists('JFormFieldcountryassociation', false)) {
    class_alias(CountryassociationField::class, 'JFormFieldcountryassociation');
}
