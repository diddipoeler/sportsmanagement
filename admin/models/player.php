<?php
/**
 * Legacy compatibility bridge for the native administrator player model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\PlayerModel;

if (!class_exists(PlayerModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/PlayerModel.php';
}

if (!class_exists('sportsmanagementModelplayer', false)) {
    class_alias(PlayerModel::class, 'sportsmanagementModelplayer');
}
