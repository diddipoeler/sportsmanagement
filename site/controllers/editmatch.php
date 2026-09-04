<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 edit-match controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Controller\EditmatchController;

if (!class_exists(EditmatchController::class)) {
    $nativeController = JPATH_SITE . '/components/com_sportsmanagement/src/Controller/EditmatchController.php';

    if (is_file($nativeController)) {
        require_once $nativeController;
    }
}

if (!class_exists(EditmatchController::class)) {
    throw new \RuntimeException('SportsManagement native Editmatch controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerEditmatch', false)) {
    class_alias(EditmatchController::class, 'sportsmanagementControllerEditmatch');
}
