<?php
/**
 * Legacy compatibility bridge for the native administrator country form model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextcountryModel;

if (!class_exists(JlextcountryModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/JlextcountryModel.php';
}

if (!class_exists('sportsmanagementModeljlextcountry', false)) {
    class_alias(JlextcountryModel::class, 'sportsmanagementModeljlextcountry');
}
