<?php
/**
 * Legacy compatibility bridge for the native prediction members list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictionmembersModel;

if (!class_exists(PredictionmembersModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PredictionmembersModel.php';
}

if (!class_exists('sportsmanagementModelPredictionMembers', false)) {
    class_alias(PredictionmembersModel::class, 'sportsmanagementModelPredictionMembers');
}
