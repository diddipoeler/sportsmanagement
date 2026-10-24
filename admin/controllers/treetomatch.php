<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Treetomatch controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\TreetomatchController;

if (!class_exists(TreetomatchController::class)) {
    foreach ([
        __DIR__ . '/../src/Controller/SportsManagementFormController.php',
        __DIR__ . '/../src/Controller/TreetomatchController.php',
    ] as $dependency) {
        if (!is_file($dependency)) {
            throw new \RuntimeException('SportsManagement administrator controller dependency is missing: ' . basename($dependency), 500);
        }

        require_once $dependency;
    }
}

if (!class_exists(TreetomatchController::class)) {
    throw new \RuntimeException('SportsManagement native Treetomatch controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerTreetomatch', false)) {
    class_alias(TreetomatchController::class, 'sportsmanagementControllerTreetomatch');
}
