<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Github controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\GithubController;

if (!class_exists(GithubController::class)) {
    $nativeController = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/GithubController.php';

    if (is_file($nativeController)) {
        require_once $nativeController;
    }
}

if (!class_exists(GithubController::class)) {
    throw new \RuntimeException('SportsManagement native administrator Github controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllergithub', false)) {
    class_alias(GithubController::class, 'sportsmanagementControllergithub');
}
