<?php
/**
 * SportsManagement legacy compatibility bridge.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/PredictionrulesModel.php.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\PredictionrulesModel;

if (!class_exists(PredictionrulesModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementPredictionModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/PredictionrulesModel.php';
}

if (!class_exists('sportsmanagementModelPredictionRules', false)) {
    class_alias(PredictionrulesModel::class, 'sportsmanagementModelPredictionRules');
}
