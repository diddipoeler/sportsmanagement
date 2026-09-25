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
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JoomleagueimportController.php';
}

if (!class_exists(JoomleagueimportController::class)) {
    throw new \RuntimeException('SportsManagement native Joomleagueimport controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerjoomleagueimport', false)) {
    class_alias(JoomleagueimportController::class, 'sportsmanagementControllerjoomleagueimport');
}
