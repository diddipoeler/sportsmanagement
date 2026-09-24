<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Season model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\SeasonModel;

if (!class_exists(SeasonModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SeasonModel.php';
}

if (!class_exists(SeasonModel::class)) {
    throw new \RuntimeException('SportsManagement native Season model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelseason', false)) {
    class_alias(SeasonModel::class, 'sportsmanagementModelseason');
}
