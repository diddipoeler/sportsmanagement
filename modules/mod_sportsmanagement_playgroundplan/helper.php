<?php
/**
 * Legacy helper bridge for the Joomla 5/6 SportsManagement Playground Plan module.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementPlaygroundPlan\Site\Helper\PlaygroundPlanHelper;

if (!class_exists(PlaygroundPlanHelper::class)) {
    require_once __DIR__ . '/src/Helper/PlaygroundPlanHelper.php';
}

if (!class_exists('modSportsmanagementPlaygroundplanHelper', false)) {
    class_alias(PlaygroundPlanHelper::class, 'modSportsmanagementPlaygroundplanHelper');
}
