<?php
/**
 * Legacy compatibility bridge for the native Leaguechampionoverview model.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\LeaguechampionoverviewModel;

if (!class_exists(LeaguechampionoverviewModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/LeaguechampionoverviewModel.php';
}

if (!class_exists('sportsmanagementModelleaguechampionoverview', false)) {
    class_alias(LeaguechampionoverviewModel::class, 'sportsmanagementModelleaguechampionoverview');
}
