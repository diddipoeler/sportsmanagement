<?php
/**
 * Legacy compatibility bridge for the native tournament-tree nodes list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\TreetonodesModel;

if (!class_exists(TreetonodesModel::class)) {
    foreach ([
        __DIR__ . '/../src/Model/SportsManagementListModel.php',
        __DIR__ . '/../src/Model/TreetonodesModel.php',
    ] as $dependency) {
        if (!is_file($dependency)) {
            throw new \RuntimeException('SportsManagement administrator model dependency is missing: ' . basename($dependency), 500);
        }

        require_once $dependency;
    }
}

if (!class_exists(TreetonodesModel::class)) {
    throw new \RuntimeException('SportsManagement native Treetonodes model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelTreetonodes', false)) {
    class_alias(TreetonodesModel::class, 'sportsmanagementModelTreetonodes');
}
