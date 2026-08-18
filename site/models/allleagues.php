<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/AllleaguesModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\AllleaguesModel;

if (!class_exists(AllleaguesModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/AllleaguesModel.php';
}

if (!class_exists('sportsmanagementModelallleagues', false)) {
    class_alias(AllleaguesModel::class, 'sportsmanagementModelallleagues');
}
