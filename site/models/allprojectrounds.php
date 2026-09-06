<?php
/**
 * SportsManagement legacy compatibility bridge for the native Allprojectrounds model.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/AllprojectroundsModel.php.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\AllprojectroundsModel;

if (!class_exists(AllprojectroundsModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/AllprojectroundsModel.php';
}

if (!class_exists('sportsmanagementModelallprojectrounds', false)) {
    class_alias(AllprojectroundsModel::class, 'sportsmanagementModelallprojectrounds');
}
