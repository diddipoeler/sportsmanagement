<?php
/**
 * SportsManagement legacy compatibility bridge for the Joomla 5/6 tree-to-node model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\TreetonodeModel;

if (!class_exists(TreetonodeModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/TreetonodeModel.php';
}

if (!class_exists('sportsmanagementModelTreetonode', false)) {
    class_alias(TreetonodeModel::class, 'sportsmanagementModelTreetonode');
}
