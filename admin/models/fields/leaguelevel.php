<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 league-level field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\LeaguelevelField;

if (!class_exists(LeaguelevelField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/LeaguelevelField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(LeaguelevelField::class)) {
    throw new \RuntimeException('SportsManagement native Leaguelevel field could not be loaded.', 500);
}

if (!class_exists('JFormFieldLeagueLevel', false)) {
    class_alias(LeaguelevelField::class, 'JFormFieldLeagueLevel');
}
