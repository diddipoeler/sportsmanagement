<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/EventModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\EventModel;

if (!class_exists(EventModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/EventModel.php';
}

if (!class_exists('sportsmanagementModelEvent', false)) {
    class_alias(EventModel::class, 'sportsmanagementModelEvent');
}
