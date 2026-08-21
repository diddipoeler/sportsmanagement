<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ProjectteamlistField;

if (!class_exists(ProjectteamlistField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ProjectteamlistField.php';
}

if (!class_exists('JFormFieldprojectteamlist', false)) {
    class_alias(ProjectteamlistField::class, 'JFormFieldprojectteamlist');
}
