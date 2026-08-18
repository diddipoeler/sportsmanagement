<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/JsonfeedModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\JsonfeedModel;

if (!class_exists(JsonfeedModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/JsonfeedModel.php';
}

if (!class_exists('sportsmanagementModelJSONFeed', false)) {
    class_alias(JsonfeedModel::class, 'sportsmanagementModelJSONFeed');
}
