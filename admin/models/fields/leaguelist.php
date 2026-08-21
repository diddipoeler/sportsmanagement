<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\LeaguelistField;

if (!class_exists(LeaguelistField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/LeaguelistField.php';
}

if (!class_exists('JFormFieldleaguelist', false)) {
    class_alias(LeaguelistField::class, 'JFormFieldleaguelist');
}
