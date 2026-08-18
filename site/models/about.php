<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/AboutModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\AboutModel;

if (!class_exists(AboutModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/AboutModel.php';
}

if (!class_exists('sportsmanagementModelAbout', false)) {
    class_alias(AboutModel::class, 'sportsmanagementModelAbout');
}
