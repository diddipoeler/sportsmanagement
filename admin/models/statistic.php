<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Statistic model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\StatisticModel;

if (!class_exists(StatisticModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/StatisticModel.php';
}

if (!class_exists('sportsmanagementModelstatistic', false)) {
    class_alias(StatisticModel::class, 'sportsmanagementModelstatistic');
}
