<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 frontend Curve model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\CurveModel;

if (!class_exists(CurveModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/CurveModel.php';
}

if (!class_exists('sportsmanagementModelCurve', false)) {
    class_alias(CurveModel::class, 'sportsmanagementModelCurve');
}
