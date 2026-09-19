<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 treeto model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\TreetoModel;

if (!class_exists(TreetoModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/TreetoModel.php';
}

if (!class_exists(TreetoModel::class)) {
    throw new \RuntimeException('SportsManagement native Treeto model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelTreeto', false)) {
    class_alias(TreetoModel::class, 'sportsmanagementModelTreeto');
}
