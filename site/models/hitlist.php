<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/HitlistModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\HitlistModel;

if (!class_exists(HitlistModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/HitlistModel.php';
}

if (!class_exists('sportsmanagementModelhitlist', false)) {
    class_alias(HitlistModel::class, 'sportsmanagementModelhitlist');
}
