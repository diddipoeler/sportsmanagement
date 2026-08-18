<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/AllplaygroundsModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\AllplaygroundsModel;

if (!class_exists(AllplaygroundsModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/AllplaygroundsModel.php';
}

if (!class_exists('sportsmanagementModelallplaygrounds', false)) {
    class_alias(AllplaygroundsModel::class, 'sportsmanagementModelallplaygrounds');
}
