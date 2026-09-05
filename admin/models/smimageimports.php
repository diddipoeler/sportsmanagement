<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Smimageimports model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\SmimageimportsModel;

if (!class_exists(SmimageimportsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SmimageimportsModel.php';
}

if (!class_exists('sportsmanagementModelsmimageimports', false)) {
    class_alias(SmimageimportsModel::class, 'sportsmanagementModelsmimageimports');
}