<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in admin/src/Model/ClubnameModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\ClubnameModel;

if (!class_exists(ClubnameModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ClubnameModel.php';
}

if (!class_exists('sportsmanagementModelclubname', false)) {
    class_alias(ClubnameModel::class, 'sportsmanagementModelclubname');
}
