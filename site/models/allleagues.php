<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/AllleaguesModel.php.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\AllleaguesModel;

if (!class_exists(AllleaguesModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/AllleaguesModel.php';
}

if (!class_exists('sportsmanagementModelallleagues', false)) {
    class_alias(AllleaguesModel::class, 'sportsmanagementModelallleagues');
}
