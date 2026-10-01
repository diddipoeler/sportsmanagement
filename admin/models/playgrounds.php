<?php
/**
 * Legacy compatibility bridge for the native administrator Playgrounds list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PlaygroundsModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementListModel;

$nativeDependencies = [
    SportsManagementListModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
    PlaygroundsModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PlaygroundsModel.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

if (!class_exists(PlaygroundsModel::class)) {
    throw new \RuntimeException('SportsManagement native Playgrounds model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelPlaygrounds', false)) {
    class_alias(PlaygroundsModel::class, 'sportsmanagementModelPlaygrounds');
}
