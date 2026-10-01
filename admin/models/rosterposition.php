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
use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel;

$nativeDependencies = [
    SportsManagementAdminModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php',
    RosterpositionModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/RosterpositionModel.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

if (!class_exists(RosterpositionModel::class)) {
    throw new \RuntimeException('SportsManagement native Rosterposition model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelrosterposition', false)) {
    class_alias(RosterpositionModel::class, 'sportsmanagementModelrosterposition');
}
