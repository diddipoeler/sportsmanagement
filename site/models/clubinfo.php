<?php
/**
 * Joomla 5/6 Clubinfo migration.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\ClubinfoModel;

if (!class_exists(ClubinfoModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/ClubinfoModel.php';
}

if (!class_exists('sportsmanagementModelClubInfo', false)) {
    class_alias(ClubinfoModel::class, 'sportsmanagementModelClubInfo');
}
