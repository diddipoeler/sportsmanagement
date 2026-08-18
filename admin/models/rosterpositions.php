<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/RosterpositionsModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\RosterpositionsModel;

if (!class_exists(RosterpositionsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/RosterpositionsModel.php';
}

if (!class_exists('sportsmanagementModelrosterpositions', false)) {
    class_alias(RosterpositionsModel::class, 'sportsmanagementModelrosterpositions');
}
