<?php
/**
 * Legacy compatibility bridge for the native tournament-tree matches list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\TreetomatchsModel;

if (!class_exists(TreetomatchsModel::class)) {
    foreach ([
        __DIR__ . '/../src/Model/SportsManagementListModel.php',
        __DIR__ . '/../src/Model/TreetomatchsModel.php',
    ] as $dependency) {
        if (!is_file($dependency)) {
            throw new \RuntimeException('SportsManagement administrator model dependency is missing: ' . basename($dependency), 500);
        }

        require_once $dependency;
    }
}

if (!class_exists(TreetomatchsModel::class)) {
    throw new \RuntimeException('SportsManagement native Treetomatchs model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelTreetomatchs', false)) {
    class_alias(TreetomatchsModel::class, 'sportsmanagementModelTreetomatchs');
}
