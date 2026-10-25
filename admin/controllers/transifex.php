<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Transifex controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\TransifexController;

if (!class_exists(TransifexController::class)) {
    $nativeController = __DIR__ . '/../src/Controller/TransifexController.php';

    if (!is_file($nativeController)) {
        throw new \RuntimeException('SportsManagement administrator controller file is missing: ' . basename($nativeController), 500);
    }

    require_once $nativeController;
}

if (!class_exists(TransifexController::class)) {
    throw new \RuntimeException('SportsManagement native Transifex controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllertransifex', false)) {
    class_alias(TransifexController::class, 'sportsmanagementControllertransifex');
}
