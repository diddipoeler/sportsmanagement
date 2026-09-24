<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Positionstatistic model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PositionstatisticModel;

if (!class_exists(PositionstatisticModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PositionstatisticModel.php';
}

if (!class_exists(PositionstatisticModel::class)) {
    throw new \RuntimeException('SportsManagement native Positionstatistic model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelpositionstatistic', false)) {
    class_alias(PositionstatisticModel::class, 'sportsmanagementModelpositionstatistic');
}
