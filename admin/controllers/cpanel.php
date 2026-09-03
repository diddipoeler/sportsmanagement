<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Cpanel controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\CpanelController;

if (!class_exists(CpanelController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/CpanelController.php';
}

if (!class_exists('sportsmanagementControllercpanel', false)) {
    class_alias(CpanelController::class, 'sportsmanagementControllercpanel');
}
