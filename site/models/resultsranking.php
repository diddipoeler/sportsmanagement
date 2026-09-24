<?php
/**
 * SportsManagement legacy compatibility bridge for the native Resultsranking model.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/ResultsrankingModel.php.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\ResultsrankingModel;

if (!class_exists(ResultsrankingModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/ResultsrankingModel.php';
}

if (!class_exists(ResultsrankingModel::class)) {
    throw new \RuntimeException('SportsManagement native Resultsranking model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelResultsranking', false)) {
    class_alias(ResultsrankingModel::class, 'sportsmanagementModelResultsranking');
}
