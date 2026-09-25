<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 favorite-team field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\FavteamField;

if (!class_exists(FavteamField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/FavteamField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(FavteamField::class)) {
    throw new \RuntimeException('SportsManagement native Favteam field could not be loaded.', 500);
}

if (!class_exists('JFormFieldFavteam', false)) {
    class_alias(FavteamField::class, 'JFormFieldFavteam');
}
