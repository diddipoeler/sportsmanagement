<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 results controller.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Controller\ResultsController;

if (!class_exists(ResultsController::class)) {
    $nativeController = JPATH_SITE . '/components/com_sportsmanagement/src/Controller/ResultsController.php';

    if (is_file($nativeController)) {
        require_once $nativeController;
    }
}

if (!class_exists(ResultsController::class)) {
    throw new \RuntimeException('SportsManagement native Results controller could not be loaded.', 500);
}

if (!class_exists('sportsmanagementControllerResults', false)) {
    class_alias(ResultsController::class, 'sportsmanagementControllerResults');
}
