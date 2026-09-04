<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Matches controller AJAX actions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\MatchesController;

if (!class_exists(MatchesController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/MatchesController.php';
}

if (!class_exists('sportsmanagementControllerajaxcalls', false)) {
    class_alias(MatchesController::class, 'sportsmanagementControllerajaxcalls');
}
