<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Joomleagueimport controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JoomleagueimportController;

if (!class_exists(JoomleagueimportController::class)) {
    $nativeController = __DIR__ . '/../src/Controller/JoomleagueimportController.php';

    if (!is_file($nativeController)) {
        throw new \RuntimeException('SportsManagement native Joomleagueimport controller file is missing.', 500);
    }

    require_once $nativeController;
}

if (!class_exists(JoomleagueimportController::class)) {
    throw new \RuntimeException('SportsManagement native Joomleagueimport controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerjoomleagueimport', false)) {
    class_alias(JoomleagueimportController::class, 'sportsmanagementControllerjoomleagueimport');
}
