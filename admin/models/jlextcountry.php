<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator country form model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextcountryModel;

if (!class_exists(JlextcountryModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/JlextcountryModel.php';
}

if (!class_exists(JlextcountryModel::class)) {
    throw new \RuntimeException('SportsManagement native Jlextcountry model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModeljlextcountry', false)) {
    class_alias(JlextcountryModel::class, 'sportsmanagementModeljlextcountry');
}
