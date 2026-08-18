<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/AllclubsModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\AllclubsModel;

if (!class_exists(AllclubsModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/AllclubsModel.php';
}

if (!class_exists('sportsmanagementModelallclubs', false)) {
    class_alias(AllclubsModel::class, 'sportsmanagementModelallclubs');
}
