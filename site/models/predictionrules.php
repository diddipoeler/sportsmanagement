<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 frontend Predictionrules model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\PredictionrulesModel;

if (!class_exists(PredictionrulesModel::class)) {
    foreach ([
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementPredictionModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/PredictionrulesModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(PredictionrulesModel::class)) {
    throw new \RuntimeException('SportsManagement native Predictionrules model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelPredictionRules', false)) {
    class_alias(PredictionrulesModel::class, 'sportsmanagementModelPredictionRules');
}
