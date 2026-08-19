<?php
/** Legacy compatibility bridge for the native administrator quote text editor model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\SmquotetxtModel;

if (!class_exists(SmquotetxtModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SmquotetxtModel.php';
}

if (!class_exists('sportsmanagementModelsmquotetxt', false)) {
    class_alias(SmquotetxtModel::class, 'sportsmanagementModelsmquotetxt');
}
