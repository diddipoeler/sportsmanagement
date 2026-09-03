<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Jlextindividualsportes controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextindividualsportesController;

if (!class_exists(JlextindividualsportesController::class)) {
    require_once JPATH_COMPONENT_ADMINISTRATOR . '/src/Controller/SportsManagementAdminController.php';
    require_once JPATH_COMPONENT_ADMINISTRATOR . '/src/Controller/JlextindividualsportesController.php';
}

if (!class_exists('sportsmanagementControllerjlextindividualsportes', false)) {
    class_alias(JlextindividualsportesController::class, 'sportsmanagementControllerjlextindividualsportes');
}
