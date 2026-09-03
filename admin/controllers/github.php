<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Github controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\GithubController;

if (!class_exists(GithubController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/GithubController.php';
}

if (!class_exists('sportsmanagementControllergithub', false)) {
    class_alias(GithubController::class, 'sportsmanagementControllergithub');
}
