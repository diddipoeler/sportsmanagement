<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/PlaygroundModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\PlaygroundModel;

if (!class_exists(PlaygroundModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/PlaygroundModel.php';
}

if (!class_exists('sportsmanagementModelPlayground', false)) {
    class_alias(PlaygroundModel::class, 'sportsmanagementModelPlayground');
}
