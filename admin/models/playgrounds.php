<?php
/**
 * Legacy compatibility bridge for the native administrator Playgrounds list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\PlaygroundsModel;

if (!class_exists(PlaygroundsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PlaygroundsModel.php';
}

if (!class_exists('sportsmanagementModelPlaygrounds', false)) {
    class_alias(PlaygroundsModel::class, 'sportsmanagementModelPlaygrounds');
}
