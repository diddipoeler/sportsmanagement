<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\CountrylistField;

if (!class_exists(CountrylistField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/CountrylistField.php';
}

if (!class_exists('JFormFieldcountrylist', false)) {
    class_alias(CountrylistField::class, 'JFormFieldcountrylist');
}
