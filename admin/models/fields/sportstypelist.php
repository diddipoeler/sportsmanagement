<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\SportstypelistField;

if (!class_exists(SportstypelistField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/SportstypelistField.php';
}

if (!class_exists('JFormFieldsportstypelist', false)) {
    class_alias(SportstypelistField::class, 'JFormFieldsportstypelist');
}
