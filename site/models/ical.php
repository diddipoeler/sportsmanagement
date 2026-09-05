<?php
/**
 * SportsManagement legacy compatibility bridge for the native iCal model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\IcalModel;

if (!class_exists(IcalModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/IcalModel.php';
}

if (!class_exists('sportsmanagementModelical', false)) {
    class_alias(IcalModel::class, 'sportsmanagementModelical');
}
