<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Matches model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\MatchesModel;

if (!class_exists(MatchesModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/MatchesModel.php';
}

if (!class_exists(MatchesModel::class)) {
    throw new \RuntimeException('SportsManagement native Matches model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelMatches', false)) {
    class_alias(MatchesModel::class, 'sportsmanagementModelMatches');
}
