<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\AgegrouplistField;

if (!class_exists(AgegrouplistField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/AgegrouplistField.php';
}

if (!class_exists('JFormFieldagegrouplist', false)) {
    class_alias(AgegrouplistField::class, 'JFormFieldagegrouplist');
}
