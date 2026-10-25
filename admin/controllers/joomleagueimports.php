<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 JoomLeague imports controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JoomleagueimportsController;

if (!class_exists(JoomleagueimportsController::class)) {
    $nativeController = __DIR__ . '/../src/Controller/JoomleagueimportsController.php';

    if (!is_file($nativeController)) {
        throw new \RuntimeException('SportsManagement native Joomleagueimports controller file is missing.', 500);
    }

    require_once $nativeController;
}

if (!class_exists(JoomleagueimportsController::class)) {
    throw new \RuntimeException('SportsManagement native Joomleagueimports controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerjoomleagueimports', false)) {
    class_alias(JoomleagueimportsController::class, 'sportsmanagementControllerjoomleagueimports');
}
