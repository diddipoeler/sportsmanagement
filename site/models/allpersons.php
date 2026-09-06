<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/AllpersonsModel.php.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\AllpersonsModel;

if (!class_exists(AllpersonsModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/AllpersonsModel.php';
}

if (!class_exists('sportsmanagementModelallpersons', false)) {
    class_alias(AllpersonsModel::class, 'sportsmanagementModelallpersons');
}
