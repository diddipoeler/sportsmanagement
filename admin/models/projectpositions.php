<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Projectpositions model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectpositionsModel;

if (!class_exists(ProjectpositionsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ProjectpositionsModel.php';
}

if (!class_exists(ProjectpositionsModel::class)) {
    throw new \RuntimeException('SportsManagement native Projectpositions model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelProjectpositions', false)) {
    class_alias(ProjectpositionsModel::class, 'sportsmanagementModelProjectpositions');
}
