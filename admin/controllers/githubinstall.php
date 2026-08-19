<?php
/** Legacy compatibility bridge for the native administrator GitHub installer controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\GithubinstallController;

if (!class_exists(GithubinstallController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/GithubinstallController.php';
}

if (!class_exists('sportsmanagementControllergithubinstall', false)) {
    class_alias(GithubinstallController::class, 'sportsmanagementControllergithubinstall');
}
