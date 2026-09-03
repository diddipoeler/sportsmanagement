<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 SIS import controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextsisimportController;

if (!class_exists(JlextsisimportController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JlextsisimportController.php';
}

if (!class_exists('sportsmanagementControllerjlextsisimport', false)) {
    class_alias(JlextsisimportController::class, 'sportsmanagementControllerjlextsisimport');
}
