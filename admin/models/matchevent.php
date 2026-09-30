<?php
/**
 * Legacy compatibility bridge for the native match event form model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\MatcheventModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel;

$nativeDependencies = [
    SportsManagementAdminModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php',
    MatcheventModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/MatcheventModel.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

if (!class_exists(MatcheventModel::class)) {
    throw new \RuntimeException('SportsManagement native Matchevent model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelmatchevent', false)) {
    class_alias(MatcheventModel::class, 'sportsmanagementModelmatchevent');
}
