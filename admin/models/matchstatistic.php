<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Matchstatistic model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\MatchstatisticModel;

if (!class_exists(MatchstatisticModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/MatchstatisticModel.php';
}

if (!class_exists(MatchstatisticModel::class)) {
    throw new \RuntimeException('SportsManagement native Matchstatistic model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelMatchstatistic', false)) {
    class_alias(MatchstatisticModel::class, 'sportsmanagementModelMatchstatistic');
}
