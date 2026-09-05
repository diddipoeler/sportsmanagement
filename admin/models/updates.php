<?php
/**
 * Legacy compatibility bridge for the native administrator Updates model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\UpdatesModel;

if (!class_exists(UpdatesModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/UpdatesModel.php';
}

if (!class_exists('sportsmanagementModelUpdates', false)) {
    class_alias(UpdatesModel::class, 'sportsmanagementModelUpdates');
}
