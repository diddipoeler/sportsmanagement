<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ClublistField;

if (!class_exists(ClublistField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ClublistField.php';
}

if (!class_exists('JFormFieldClublist', false)) {
    class_alias(ClublistField::class, 'JFormFieldClublist');
}
