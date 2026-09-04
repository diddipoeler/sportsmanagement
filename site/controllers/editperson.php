<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 edit-person controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Controller\EditpersonController;

if (!class_exists(EditpersonController::class)) {
    $nativeController = JPATH_SITE . '/components/com_sportsmanagement/src/Controller/EditpersonController.php';

    if (is_file($nativeController)) {
        require_once $nativeController;
    }
}

if (!class_exists(EditpersonController::class)) {
    throw new \RuntimeException('SportsManagement native Editperson controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllereditperson', false)) {
    class_alias(EditpersonController::class, 'sportsmanagementControllereditperson');
}
