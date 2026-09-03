<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator quote text editor controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\SmquotetxtController;

if (!class_exists(SmquotetxtController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SmquotetxtController.php';
}

if (!class_exists('sportsmanagementControllersmquotetxt', false)) {
    class_alias(SmquotetxtController::class, 'sportsmanagementControllersmquotetxt');
}
