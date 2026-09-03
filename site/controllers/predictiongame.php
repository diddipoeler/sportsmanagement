<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 prediction game controller.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Controller\PredictiongameController;

if (!class_exists(PredictiongameController::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Controller/PredictiongameController.php';
}

if (!class_exists('sportsmanagementControllerPredictiongame', false)) {
    class_alias(PredictiongameController::class, 'sportsmanagementControllerPredictiongame');
}
