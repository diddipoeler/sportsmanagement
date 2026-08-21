<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\MatchdaylistField;

if (!class_exists(MatchdaylistField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/MatchdaylistField.php';
}

if (!class_exists('JFormFieldMatchdaylist', false)) {
    class_alias(MatchdaylistField::class, 'JFormFieldMatchdaylist');
}
