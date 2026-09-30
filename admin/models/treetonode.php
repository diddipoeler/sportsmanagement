<?php
/**
 * Legacy compatibility bridge for the native tournament-tree node model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\TreetonodeModel;

$nativeDependencies = [
    SportsManagementAdminModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php',
    TreetonodeModel::class => JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/TreetonodeModel.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

if (!class_exists(TreetonodeModel::class)) {
    throw new \RuntimeException('SportsManagement native Treetonode model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelTreetonode', false)) {
    class_alias(TreetonodeModel::class, 'sportsmanagementModelTreetonode');
}
