<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Cpanel controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Controller\CpanelController;

if (!class_exists(CpanelController::class)) {
    $nativeController = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/CpanelController.php';

    if (is_file($nativeController)) {
        require_once $nativeController;
    }
}

if (!class_exists(CpanelController::class)) {
    throw new \RuntimeException('SportsManagement native administrator Cpanel controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllercpanel', false)) {
    class_alias(CpanelController::class, 'sportsmanagementControllercpanel');
}
