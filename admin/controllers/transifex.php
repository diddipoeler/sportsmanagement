<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Transifex controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\TransifexController;

if (!class_exists(TransifexController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/TransifexController.php';
}

if (!class_exists('sportsmanagementControllertransifex', false)) {
    class_alias(TransifexController::class, 'sportsmanagementControllertransifex');
}
