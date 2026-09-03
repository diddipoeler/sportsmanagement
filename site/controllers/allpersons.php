<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 all persons controller.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Controller\AllpersonsController;

if (!class_exists(AllpersonsController::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Controller/AllpersonsController.php';
}

if (!class_exists('sportsmanagementControllerallpersons', false)) {
    class_alias(AllpersonsController::class, 'sportsmanagementControllerallpersons');
}
