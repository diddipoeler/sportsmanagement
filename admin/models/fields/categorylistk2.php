<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\Categorylistk2Field;

if (!class_exists(Categorylistk2Field::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/Categorylistk2Field.php';
}

if (!class_exists('JFormFieldcategorylistk2', false)) {
    class_alias(Categorylistk2Field::class, 'JFormFieldcategorylistk2');
}
