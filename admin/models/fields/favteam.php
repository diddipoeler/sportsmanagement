<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\FavteamField;

if (!class_exists(FavteamField::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/FavteamField.php';
}

if (!class_exists('JFormFieldFavteam', false)) {
    class_alias(FavteamField::class, 'JFormFieldFavteam');
}
