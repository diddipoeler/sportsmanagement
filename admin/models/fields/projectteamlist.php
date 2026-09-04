<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 project team list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\ProjectteamlistField;

if (!class_exists(ProjectteamlistField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/ProjectteamlistField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(ProjectteamlistField::class)) {
    throw new \RuntimeException('SportsManagement native Projectteamlist field could not be loaded.', 500);
}

if (!class_exists('JFormFieldprojectteamlist', false)) {
    class_alias(ProjectteamlistField::class, 'JFormFieldprojectteamlist');
}
