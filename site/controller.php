<?php
/**
 * Legacy site controller bridge for Joomla 5/6.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Controller\DisplayController;

if (!class_exists(DisplayController::class)) {
    require_once __DIR__ . '/src/Controller/DisplayController.php';
}

if (!class_exists('sportsmanagementController', false)) {
    class_alias(DisplayController::class, 'sportsmanagementController');
}
