<?php
/**
 * Legacy compatibility bridge for the native Curve model.
 */
defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\CurveModel;

if (!class_exists(CurveModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/ProjectRoundReader.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/CurveModel.php';
}

if (!class_exists('sportsmanagementModelCurve', false)) {
    class_alias(CurveModel::class, 'sportsmanagementModelCurve');
}
