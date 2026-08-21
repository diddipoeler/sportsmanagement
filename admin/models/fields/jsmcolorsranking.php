<?php
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\JsmcolorsrankingField;

if (!class_exists(JsmcolorsrankingField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/JsmcolorsrankingField.php';
}

if (!class_exists('JFormFieldjsmcolorsranking', false)) {
    class_alias(JsmcolorsrankingField::class, 'JFormFieldjsmcolorsranking');
}
