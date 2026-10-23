<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 tree-to-node controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Controller\TreetonodeController;

if (!class_exists(TreetonodeController::class)) {
    $nativeController = __DIR__ . '/../src/Controller/TreetonodeController.php';

    if (!is_file($nativeController)) {
        throw new \RuntimeException('SportsManagement native Treetonode controller file is missing.', 500);
    }

    require_once $nativeController;
}

if (!class_exists(TreetonodeController::class)) {
    throw new \RuntimeException('SportsManagement native Treetonode controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerTreetonode', false)) {
    class_alias(TreetonodeController::class, 'sportsmanagementControllerTreetonode');
}
