<?php
/**
 * Legacy compatibility bridge for the native site image handler controller.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Controller\ImagehandlerController;

if (!class_exists(ImagehandlerController::class)) {
    $controllerFile = JPATH_SITE . '/components/com_sportsmanagement/src/Controller/ImagehandlerController.php';

    if (is_file($controllerFile)) {
        require_once $controllerFile;
    }
}

if (class_exists(ImagehandlerController::class) && !class_exists('sportsmanagementControllerImagehandler', false)) {
    class_alias(ImagehandlerController::class, 'sportsmanagementControllerImagehandler');
}
