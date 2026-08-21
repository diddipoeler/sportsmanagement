<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\CoefficientyearsField;

if (!class_exists(CoefficientyearsField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/CoefficientyearsField.php';
}

if (!class_exists('JFormFieldcoefficientyears', false)) {
    class_alias(CoefficientyearsField::class, 'JFormFieldcoefficientyears');
}
