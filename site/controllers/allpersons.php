<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 all persons controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Controller\AllpersonsController;

if (!class_exists(AllpersonsController::class)) {
    $nativeController = JPATH_SITE . '/components/com_sportsmanagement/src/Controller/AllpersonsController.php';

    if (is_file($nativeController)) {
        require_once $nativeController;
    }
}

if (!class_exists(AllpersonsController::class)) {
    throw new \RuntimeException('SportsManagement native Allpersons controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerallpersons', false)) {
    class_alias(AllpersonsController::class, 'sportsmanagementControllerallpersons');
}
