<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 frontend Staff model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\StaffModel;

if (!class_exists(StaffModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/PersonModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/StaffModel.php';
}

if (!class_exists('sportsmanagementModelStaff', false)) {
    class_alias(StaffModel::class, 'sportsmanagementModelStaff');
}
