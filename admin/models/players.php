<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Players list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PlayersModel;

if (!class_exists(PlayersModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PlayersModel.php';
}

if (!class_exists(PlayersModel::class)) {
    throw new \RuntimeException('SportsManagement native Players model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelplayers', false)) {
    class_alias(PlayersModel::class, 'sportsmanagementModelplayers');
}
