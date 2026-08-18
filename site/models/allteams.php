<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/AllteamsModel.php.
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\AllteamsModel;

if (!class_exists(AllteamsModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/AllteamsModel.php';
}

if (!class_exists('sportsmanagementModelallteams', false)) {
    class_alias(AllteamsModel::class, 'sportsmanagementModelallteams');
}
