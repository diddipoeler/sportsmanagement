<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Seasons model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\SeasonsModel;

if (!class_exists(SeasonsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SeasonsModel.php';
}

if (!class_exists(SeasonsModel::class)) {
    throw new \RuntimeException('SportsManagement native Seasons model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelSeasons', false)) {
    class_alias(SeasonsModel::class, 'sportsmanagementModelSeasons');
}
