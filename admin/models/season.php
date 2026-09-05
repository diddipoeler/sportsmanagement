<?php
/**
 * SportsManagement legacy compatibility bridge for the native administrator Season model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\SeasonModel;

if (!class_exists(SeasonModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SeasonModel.php';
}

if (!class_exists('sportsmanagementModelseason', false)) {
    class_alias(SeasonModel::class, 'sportsmanagementModelseason');
}
