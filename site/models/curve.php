<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 frontend Curve model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\CurveModel;

if (!class_exists(CurveModel::class)) {
    foreach ([
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/CurveModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(CurveModel::class)) {
    throw new \RuntimeException('SportsManagement native Curve model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelCurve', false)) {
    class_alias(CurveModel::class, 'sportsmanagementModelCurve');
}
