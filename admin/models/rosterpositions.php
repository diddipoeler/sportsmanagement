<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Rosterpositions model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\RosterpositionsModel;

if (!class_exists(RosterpositionsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/RosterpositionsModel.php';
}

if (!class_exists(RosterpositionsModel::class)) {
    throw new \RuntimeException('SportsManagement native Rosterpositions model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelrosterpositions', false)) {
    class_alias(RosterpositionsModel::class, 'sportsmanagementModelrosterpositions');
}
