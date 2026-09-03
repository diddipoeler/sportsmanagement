<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 JoomLeague import controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JoomleagueimportController;

if (!class_exists(JoomleagueimportController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JoomleagueimportController.php';
}

if (!class_exists('sportsmanagementControllerjoomleagueimport', false)) {
    class_alias(JoomleagueimportController::class, 'sportsmanagementControllerjoomleagueimport');
}
