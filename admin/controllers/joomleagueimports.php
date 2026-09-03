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
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JoomleagueimportsController.php';
}

if (!class_exists('sportsmanagementControllerjoomleagueimports', false)) {
    class_alias(JoomleagueimportsController::class, 'sportsmanagementControllerjoomleagueimports');
}
