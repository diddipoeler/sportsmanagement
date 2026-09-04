<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 team-list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Field\TeamlistField;

if (!class_exists(TeamlistField::class)) {
    $nativeField = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Field/TeamlistField.php';

    if (is_file($nativeField)) {
        require_once $nativeField;
    }
}

if (!class_exists(TeamlistField::class)) {
    throw new \RuntimeException('SportsManagement native Teamlist field could not be loaded.', 500);
}

if (!class_exists('JFormFieldTeamlist', false)) {
    class_alias(TeamlistField::class, 'JFormFieldTeamlist');
}
