<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Githubinstall controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\GithubinstallController;

if (!class_exists(GithubinstallController::class)) {
    $nativeController = __DIR__ . '/../src/Controller/GithubinstallController.php';

    if (!is_file($nativeController)) {
        throw new \RuntimeException('SportsManagement native administrator Githubinstall controller file is missing.', 500);
    }

    require_once $nativeController;
}

if (!class_exists(GithubinstallController::class)) {
    throw new \RuntimeException('SportsManagement native Githubinstall controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllergithubinstall', false)) {
    class_alias(GithubinstallController::class, 'sportsmanagementControllergithubinstall');
}
