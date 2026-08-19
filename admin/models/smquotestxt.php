<?php
/** Legacy compatibility bridge for the native administrator quote text-files model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\SmquotestxtModel;

if (!class_exists(SmquotestxtModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SmquotestxtModel.php';
}

if (!class_exists('sportsmanagementModelsmquotestxt', false)) {
    class_alias(SmquotestxtModel::class, 'sportsmanagementModelsmquotestxt');
}
