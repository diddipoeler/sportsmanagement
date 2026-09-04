<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 league list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\LeaguelistField;

if (!class_exists(LeaguelistField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/LeaguelistField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(LeaguelistField::class)) {
    throw new \RuntimeException('SportsManagement native Leaguelist field could not be loaded.', 500);
}

if (!class_exists('JFormFieldleaguelist', false)) {
    class_alias(LeaguelistField::class, 'JFormFieldleaguelist');
}
