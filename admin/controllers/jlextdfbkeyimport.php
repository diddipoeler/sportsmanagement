<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 DFB-key import controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextdfbkeyimportController;

if (!class_exists(JlextdfbkeyimportController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/JlextdfbkeyimportController.php';
}

if (!class_exists('sportsmanagementControllerjlextdfbkeyimport', false)) {
    class_alias(JlextdfbkeyimportController::class, 'sportsmanagementControllerjlextdfbkeyimport');
}
