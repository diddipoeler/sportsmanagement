<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator GitHub installer controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\GithubinstallController;

if (!class_exists(GithubinstallController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/GithubinstallController.php';
}

if (!class_exists('sportsmanagementControllergithubinstall', false)) {
    class_alias(GithubinstallController::class, 'sportsmanagementControllergithubinstall');
}
