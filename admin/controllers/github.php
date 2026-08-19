<?php
/** Legacy compatibility bridge for the native administrator Github controller. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\GithubController;

if (!class_exists(GithubController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/GithubController.php';
}

if (!class_exists('sportsmanagementControllergithub', false)) {
    class_alias(GithubController::class, 'sportsmanagementControllergithub');
}
