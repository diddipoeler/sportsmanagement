<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Rosterposition model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\RosterpositionModel;

if (!class_exists(RosterpositionModel::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/RosterpositionModel.php',
    ] as $nativeModel) {
        if (is_file($nativeModel)) {
            require_once $nativeModel;
        }
    }
}

if (!class_exists(RosterpositionModel::class)) {
    throw new \RuntimeException('SportsManagement native Rosterposition model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelrosterposition', false)) {
    class_alias(RosterpositionModel::class, 'sportsmanagementModelrosterposition');
}
