<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Projectposition model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectpositionModel;

if (!class_exists(ProjectpositionModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ProjectpositionModel.php';
}

if (!class_exists(ProjectpositionModel::class)) {
    throw new \RuntimeException('SportsManagement native Projectposition model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelProjectposition', false)) {
    class_alias(ProjectpositionModel::class, 'sportsmanagementModelProjectposition');
}
