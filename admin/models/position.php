<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Position model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PositionModel;

if (!class_exists(PositionModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PositionModel.php';
}

if (!class_exists(PositionModel::class)) {
    throw new \RuntimeException('SportsManagement native Position model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelposition', false)) {
    class_alias(PositionModel::class, 'sportsmanagementModelposition');
}
