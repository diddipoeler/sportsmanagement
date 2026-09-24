<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 match player form model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\MatchplayerModel;

if (!class_exists(MatchplayerModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/MatchplayerModel.php';
}

if (!class_exists(MatchplayerModel::class)) {
    throw new \RuntimeException('SportsManagement native Matchplayer model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelmatchplayer', false)) {
    class_alias(MatchplayerModel::class, 'sportsmanagementModelmatchplayer');
}
